<?php

class Sailing
{
  private $id;
  private $isRemoved = false;

  /** @var Ship */
  private $ship;
  private $speedPercent = 0;
  private $direction = 0;
  private $resultantDirection = 0;
  private $dockable = []; // array of loc ids
  private $dockingTarget = null;
  private $sailingStopTimestamp = 0;

  private $report = "";

  // float, more precise than Location::x and y
  private $x;
  private $y;

  private $logger;
  /** @var Db */
  private $db;
  /** @var GlobalConfig */
  private $globalConfig;

  public static function loadById($id)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM sailing WHERE id = :id");
    $stm->bindInt("id", $id);
    $stm->execute();
    if ($sailingInfo = $stm->fetchObject()) {
      return self::loadFromFetchObject($sailingInfo, $db);
    }
    throw new InvalidArgumentException("no sailing of id $id");
  }

  public static function loadByVesselId($vesselId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM sailing WHERE vessel = :vesselId");
    $stm->bindInt("vesselId", $vesselId);
    $stm->execute();
    if ($sailingInfo = $stm->fetchObject()) {
      return self::loadFromFetchObject($sailingInfo, $db);
    }
    throw new InvalidArgumentException("no sailing for vessel $vesselId");
  }

  public static function loadFromFetchObject(stdClass $fetchedSailing, Db $db)
  {
    $sailing = new self($db);
    $sailing->id = $fetchedSailing->id;
    $sailing->ship = Ship::loadById($fetchedSailing->vessel);
    $sailing->speedPercent = $fetchedSailing->speed_percent;
    $sailing->direction = $fetchedSailing->direction;
    $sailing->resultantDirection = $fetchedSailing->resultant_direction;

    $sailing->x = floatval($fetchedSailing->x);
    $sailing->y = floatval($fetchedSailing->y);

    $sailing->dockable = !empty($fetchedSailing->dockable) ? explode(",", $fetchedSailing->dockable) : [];
    $sailing->sailingStopTimestamp = $fetchedSailing->sailing_stop_timestamp;
    $sailing->dockingTarget = $fetchedSailing->docking_target;

    return $sailing;
  }

  public static function newInstance(Ship $ship, $excluded)
  {
    $db = Db::get();
    $sailing = new self($db);
    $sailing->id = null;
    $sailing->ship = $ship;
    $sailing->setPos($ship->getLocation()->getX(), $ship->getLocation()->getY());

    $sailing->updateDockable($excluded);

    return $sailing;
  }

  private function __construct(Db $db)
  {
    $this->logger = Logger::getLogger(__CLASS__);
    $this->db = $db;
    $this->globalConfig = new GlobalConfig($this->db);
  }

  /**
   * Starts docking to location and saves the data in database.
   * It's possible to see if ship has docked immediately by using isDocking() function
   * @param Location $goal
   * @param Character $actor
   */
  public function startDockingTo(Location $goal, Character $actor)
  {
    $this->setSpeedPercent(100);
    $this->setDockingTarget($goal->getId());

    $direction = Measure::direction($this->getX(), $this->getY(), $goal->getX(), $goal->getY());
    $this->setDirection($direction);
    $distance = $this->getDistanceToDockingTarget();
    $turns = $distance / $this->getSpeed();

    $TURNS_PER_HOUR = SailingConstants::TURNS_PER_DAY / GameDateConstants::HOURS_PER_DAY;
    if ($turns >= $TURNS_PER_HOUR) { // can dock instantly when it would take less than 1 in-game hour
      $observerLocations = [$goal->getId()];
      if ($goal->getType() == LocationConstants::TYPE_BUILDING && $goal->getRegion() > 0) {
        $observerLocations[] = $goal->getRegion();
      }

      Event::create(184, "SHIPNAME=" . $this->getShip()->getId() .
        " TARGET=" . $goal->getId() . " ACTOR=" . $actor->getId())->
      inLocation($this->getShip()->getLocation())->except($actor)->show();

      foreach ($observerLocations as $locationId) {
        Event::create(182, "SHIPNAME=" . $this->getShip()->getId() .
          " TARGET=" . $goal->getId())->inLocation($locationId)->show();
      }

      Event::create(183, "SHIPNAME=" . $this->getShip()->getId() .
        " TARGET=" . $goal->getId())->forCharacter($actor)->show();
    } else {
      $this->finishDocking($goal);
    }
  }

  public function finishDocking(Location $goal)
  {
    $this->ship->dockTo($goal);
    $this->remove();
  }

  public function makeProgress()
  {
    if ($this->isDocking()) {
      $this->makeDockingProgress();
    } else {
      $this->makeSailingProgress();
    }
    $this->saveInDb();
  }

  private function makeSailingProgress()
  {
    if (!$this->isAnybodyOnDeck()) {
      $this->setSpeedPercent(0);
    }

    $this->report = "Vessel " . $this->getShip()->getId() . " ";

    if ($this->getSpeedPercent() == 0) {
      $this->report .= "floating";
      return;
    }
    // old values to store in logs
    $oldX = $this->getX();
    $oldY = $this->getY();

    // new values normalized to [0, 6000)
    $speed = $this->getSpeed();

    $newX = MapUtil::getNormalizedX($this->getX() + $speed * cos(deg2rad($this->getDirection())));
    $newY = MapUtil::getNormalizedY($this->getY() + $speed * sin(deg2rad($this->getDirection())));

    $pos = Position::getInstance();
    $waterTypeCurrent = $pos->check_areatype(round($oldX), round($oldY));
    $waterTypeDest = $pos->check_areatype(round($newX), round($newY));

    if ($waterTypeDest != $waterTypeCurrent) {
      $this->report .= "stopped, because of different water types ($waterTypeDest, $waterTypeCurrent)";
      $this->setSpeedPercent(0);

      $shipName = urlencode("<CANTR LOCNAME ID=" . $this->getShip()->getId() . ">");
      $this->eventInAllDockedShips(Event::create(97, "SHIPNAME=$shipName"));
      return;
    }

    $this->report .= " sails from (" . round($oldX, 2) . ", " . round($oldY, 2) .
      ") to (" . round($newX, 2) . ", " . round($newY, 2) . "), speed=" . $speed . ", direction=" . $this->getDirection();

    $stm = $this->db->prepare("INSERT INTO sailinglogs (xfrom, yfrom, xto, yto, vessel)
      VALUES (:xFrom, :yFrom, :xTo, :yTo, :vessel)");
    $stm->bindFloat("xFrom", $oldX);
    $stm->bindFloat("yFrom", $oldY);
    $stm->bindFloat("xTo", $newX);
    $stm->bindFloat("yTo", $newY);
    $stm->bindInt("vessel", $this->getShip()->getId());
    $stm->execute();

    // can proceed with changing ship position

    $this->setX($newX);
    $this->setY($newY);

    $dockableEarlier = $this->getDockable();
    $this->updateDockable();
    $this->report .= ", dockable: " . implode(", ", $this->getDockable());

    $newDockableLocations = array_diff($this->getDockable(), $dockableEarlier);
    if (count($newDockableLocations) > 0) {
      $docksStr = implode(", ", array_map(function($dock) {
        return "<CANTR LOCNAME ID=$dock>";
      }, $newDockableLocations));

      Event::create(98, "DOCKS=" . urlencode($docksStr))->inLocation($this->getShip()->getLocation())->show();
    }

    $oneTurnEarlier = GameDateConstants::SECS_PER_DAY / SailingConstants::TURNS_PER_DAY;
    $shouldShipStop = $this->getSailingStopTimestamp() != 0 &&
      GameDate::NOW()->getTimestamp() >= $this->getSailingStopTimestamp() - $oneTurnEarlier;
    if ($shouldShipStop) {
      $this->setSpeedPercent(0);
      $this->setSailingStopTimestamp(0);

      $this->report .= ", stops because it was scheduled to stop";

      $shipName = urlencode("<CANTR LOCNAME ID=" . $this->getShip()->getId() . ">");
      $this->eventInAllDockedShips(Event::create(186, "SHIPNAME=$shipName"));
    }
  }

  private function isAnybodyOnDeck()
  {
    return $this->getShip()->getLocation()->getCharacterCount() > 0;
  }

  private function makeDockingProgress()
  {
    $goalId = $this->getDockingTarget();

    $endDocking = false;
    try {
      $goal = Location::loadById($goalId);

      $distance = Measure::distance($this->getX(), $this->getY(), $goal->getX(), $goal->getY());
      if ($goal->getType() == LocationConstants::TYPE_BUILDING) {
        $allowedDistance = SailingConstants::DOCKING_RANGE_HARBOUR;
      } else {
        $allowedDistance = SailingConstants::DOCKING_RANGE_LAND_OR_SHIP;
      }
      if ($distance > $allowedDistance) {
        $endDocking = true;
      }

      if (!in_array($goal->getType(), [LocationConstants::TYPE_OUTSIDE,
        LocationConstants::TYPE_BUILDING, LocationConstants::TYPE_SAILING_SHIP])
      ) {
        $endDocking = true;
      }

      try { // if docking to a ship which is moving, then cancel
        $otherSailing = Sailing::loadByVesselId($goalId);
        if ($otherSailing->getSpeed() > 0) {
          $endDocking = true;
        }
      } catch (InvalidArgumentException $e) {
      } // it's not a ship
    } catch (InvalidArgumentException $e) {
      $endDocking = true;
    }
    if ($endDocking) { // stop ship and inform about the issue

      $shipName = urlencode("<CANTR LOCNAME ID=" . $this->getShip()->getId() . ">");
      $event = Event::create(99, "SHIPNAME=$shipName");
      $this->eventInAllDockedShips($event);

      $this->setSpeedPercent(0);
      $this->setDockingTarget(null);
      $this->updateDockable();

    } else {

      $this->setDirection(Measure::direction($this->getX(), $this->getY(), $goal->getX(), $goal->getY()));

      $speed = $this->getSpeed();
      $turns = $distance / $speed;
      $TURNS_PER_HOUR = SailingConstants::TURNS_PER_DAY / GameDateConstants::HOURS_PER_DAY;
      if ($turns <= 2 * $TURNS_PER_HOUR) {
        $this->finishDocking($goal);
        return;
      }

      $newX = MapUtil::getNormalizedX($this->getX() + $speed * cos(deg2rad($this->getDirection())));
      $newY = MapUtil::getNormalizedY($this->getY() + $speed * sin(deg2rad($this->getDirection())));

      $this->setX($newX);
      $this->setY($newY);
    }
  }

  private function eventInAllDockedShips(Event $event)
  {
    $loc = $this->getShip()->getLocation();
    $locs = $loc->getSublocationsRecursive();
    // event for all sublocations
    $event->inLocation($loc)->show();
    foreach ($locs as $locId) {
      $event->inLocation(Location::loadById($locId))->show();
    }
  }

  public function getSpeed()
  {
    return $this->getMaxSpeed() * ($this->getSpeedPercent() / 100);
  }

  /**
   * Max speed per turn.
   * @return float max speed.
   */
  public function getMaxSpeed()
  {
    $deckSpeed = $this->ship->getMaxDeckSpeed();
    $sailsSpeed = $this->ship->getMaxSailsSpeed();

    $speed = $deckSpeed + $sailsSpeed;

    return $speed / SailingConstants::TURNS_PER_DAY * $this->globalConfig->getSailingProgressRatio();
  }

  public function getId()
  {
    return $this->id;
  }

  public function hasId()
  {
    return ($this->getId() != null);
  }

  public function remove()
  {
    $this->isRemoved = true;
  }

  public function getDockable()
  {
    return $this->dockable;
  }

  public function setDockable($dockable)
  {
    if ($dockable === null) {
      $dockable = [];
    }
    $this->dockable = $dockable;
  }

  public function getDockingTarget()
  {
    return $this->dockingTarget;
  }

  public function getDistanceToDockingTarget()
  {
    if ($this->isDocking()) {
      try {
        $goal = Location::loadById($this->getDockingTarget());
        return Measure::distance($this->getX(), $this->getY(), $goal->getX(), $goal->getY());
      } catch (InvalidArgumentException $e) {
      } // pass through and goto throw
    }
    throw new IllegalStateException("Ship isn't docking to anything");
  }

  public function setDockingTarget($dockingTarget)
  {
    $this->dockingTarget = $dockingTarget;
  }

  public function getSpeedPercent()
  {
    return $this->speedPercent;
  }

  public function setSpeedPercent($speedPercent)
  {
    $this->speedPercent = Measure::between($speedPercent, [0, 100]);
  }

  public function getDirection()
  {
    return $this->direction;
  }

  /**
   * @param int $direction in which you want to sail. A sharp turn requires a few turns to adjust resultantDir
   */
  public function setDirection($direction)
  {
    $this->direction = abs(intval($direction) % 360);
  }

  public function getResultantDirection()
  {
    return $this->direction;
    // currently there's no difference. Later in case of sharp turn resultant direction
    // will be changed each turn in order to slowly reach value of direction
  }

  public function getTurnsToDock()
  {
    // TODO!!! Why docking is one turn shorter than the distance between ships?
    return ceil($this->getDistanceToDockingTarget() / $this->getSpeed() - 1);
  }

  /**
   * @param int $resultantDirection in which ship is really facing (in degrees [0-360]), internal function
   */
  private function setResultantDirection($resultantDirection)
  {
    $this->resultantDirection = abs(intval($resultantDirection) % 360);
  }

  public function updateDockable($except = [])
  {
    if (!is_array($except)) {
      $except = [$except];
    }

    $pos = Position::getInstance();

    $waterType = $this->getShip()->getAreaType();

    // TODO!!! That is just to reproduce old bug in undocking code, which makes
    // immediate docking to land impossible after undocking from a ship
    if ($except) {
      $waterType = "sea and lake";
    }

    $dockable = $pos->find_dockable($this->getShip()->getLocation(), $this->getX(), $this->getY(), $waterType);
    if (!$dockable) {
      $dockable = [];
    }
    // for example when undocking from ship
    $dockable = array_values(array_diff($dockable, $except));

    $this->setDockable($dockable);
  }

  /**
   * @return Ship
   */
  public function getShip()
  {
    return $this->ship;
  }

  public function getSailingStopTimestamp()
  {
    return $this->sailingStopTimestamp;
  }

  public function setSailingStopTimestamp($sailingStopTimestamp)
  {
    $this->sailingStopTimestamp = $sailingStopTimestamp;
  }

  public function isDocking()
  {
    return $this->dockingTarget != null;
  }

  public function getX()
  {
    return $this->x;
  }

  public function setX($x)
  {
    $x = floatval($x);
    $this->x = MapUtil::getNormalizedX($x);
    $this->getShip()->getLocation()->setX(MapUtil::getNormalizedX(round($this->x)));
  }

  public function getY()
  {
    return $this->y;
  }

  public function setY($y)
  {
    $y = floatval($y);
    $this->y = MapUtil::getNormalizedY($y);
    $this->getShip()->getLocation()->setY(MapUtil::getNormalizedY(round($this->y)));
  }

  public function setPos($x, $y)
  {
    $this->setX($x);
    $this->setY($y);
  }

  public function getReport()
  {
    return $this->report;
  }

  public function canDockTo(Location $location)
  {
    return in_array($location->getId(), $this->dockable);
  }

  public function saveInDb()
  {
    if ($this->hasId()) {
      if ($this->isRemoved) {
        $stm = $this->db->prepare("DELETE FROM sailing WHERE id = :id");
        $stm->bindInt("id", $this->getId());
        $stm->execute();
        if ($stm->rowCount() == 0) {
          $this->logger->warn("Was trying to remove sailing " . $this->getId()
            . " of " . $this->ship->getId() . " but hasn't found any");
        }
        $this->id = null;
      } else {
        $stm = $this->db->prepare("UPDATE sailing SET x = :x, y = :y, speed = :speed, maxspeed = :maxSpeed,
          direction = :direction, resultant_direction = :resultantDirection, dockable = :dockable,
          sailing_stop_timestamp = :sailingStopTimestamp, docking_target = :dockingTarget,
          speed_percent = :speedPercent WHERE id = :id");
        $stm->bindFloat("x", $this->getX());
        $stm->bindFloat("y", $this->getY());
        $stm->bindInt("speed", floor($this->getSpeed() * 8));
        $stm->bindInt("maxSpeed", floor($this->getMaxSpeed() * 8));
        $stm->bindInt("direction", $this->getDirection());
        $stm->bindInt("resultantDirection", $this->getResultantDirection());
        $stm->bindStr("dockable", implode(",", $this->getDockable()));
        $stm->bindInt("sailingStopTimestamp", $this->getSailingStopTimestamp());
        $stm->bindInt("dockingTarget", $this->getDockingTarget(), true);
        $stm->bindInt("speedPercent", $this->getSpeedPercent());
        $stm->bindInt("id", $this->getId());
        $stm->execute();
      }
    } else {
      if ($this->isRemoved) {
        throw new IllegalStateException("Trying to save sailing (" . $this->getId() . ") " .
          "which no longer exists for ship " . $this->ship->getId());
      } else {
        $stm = $this->db->prepare("INSERT INTO sailing (vessel, x, y, speed, maxspeed, direction,
          resultant_direction, dockable, sailing_stop_timestamp, docking_target, speed_percent)
          VALUES (:vessel, :x, :y, :speed, :maxSpeed, :direction,
            :resultantDirection, :dockable, :sailingStopTimestamp, :dockingTarget, :speedPercent)");
        $stm->bindInt("vessel", $this->getShip()->getId());
        $stm->bindFloat("x", $this->getX());
        $stm->bindFloat("y", $this->getY());
        $stm->bindInt("speed", floor($this->getSpeed() * 8));
        $stm->bindInt("maxSpeed", floor($this->getMaxSpeed() * 8));
        $stm->bindInt("direction", $this->getDirection());
        $stm->bindInt("resultantDirection", $this->getResultantDirection());
        $stm->bindStr("dockable", implode(",", $this->getDockable()));
        $stm->bindInt("sailingStopTimestamp", $this->getSailingStopTimestamp());
        $stm->bindInt("dockingTarget", $this->getDockingTarget(), true);
        $stm->bindInt("speedPercent", $this->getSpeedPercent());
        $stm->execute();
        $this->id = $this->db->lastInsertId();
      }
    }
    $this->ship->saveInDb();
    $this->updateSublocationsPosition();
  }

  private function updateSublocationsPosition()
  {
    $shipLoc = $this->getShip()->getLocation();
    $sublocs = $shipLoc->getSublocationsRecursive();
    foreach ($sublocs as $sublocId) {
      $loc = Location::loadById($sublocId);
      $loc->setX($shipLoc->getX());
      $loc->setY($shipLoc->getY());
      $loc->saveInDb();
    }
    if (count($sublocs) > 0) {
      $stm = $this->db->prepareWithIntList("UPDATE radios SET x = :x, y = :y
        WHERE location IN (:ids)", [
        "ids" => $sublocs,
      ]);
      $stm->bindInt("x", $shipLoc->getX());
      $stm->bindInt("y", $shipLoc->getY());
      $stm->execute();
    }
  }
}
