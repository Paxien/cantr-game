<?php

class Ship
{
  private $id;
  private $location;
  private $rules; // array
  private $shipType;
  private $logger;
  /** @var Db */
  private $db;

  public static function loadById($id)
  {
    return new self($id);
  }

  private function __construct($id)
  {
    $this->db = Db::get();
    $this->id = intval($id);
    $this->location = Location::loadById($this->getId());
    $this->shipType = $this->location->getArea();
    $rules = $this->location->getObjectType()->getRules();
    $this->rules = Parser::rulesToArray($rules);
    $this->logger = Logger::getLogger(__CLASS__);
  }

  public function canMoveOn($connectionType)
  {
    $stm = $this->db->prepare("SELECT vehicles FROM connecttypes WHERE id = :id");
    $stm->bindInt("id", $connectionType);
    $vehicles = $stm->executeScalar();
    return in_array($this->shipType, explode(",", $vehicles));
  }

  /**
   * Directions for undocking
   * @return array keys=directions (degree) where ship can potentially be placed; values=array(x,y) after undocking
   */
  public function getUndockingDirections()
  {
    $position = Position::getInstance();
    $x = $this->location->getX();
    $y = $this->location->getY();

    $canMoveOnSea = $this->canMoveOn(ConnectionConstants::TYPE_SEA);
    $canMoveOnLake = $this->canMoveOn(ConnectionConstants::TYPE_LAKE);

    $availableDirections = [];
    foreach (range(0, 359, 45) as $deg) {
      $near = ["x" => $x + round(5 * cos(deg2rad($deg))), "y" => $y + round(5 * sin(deg2rad($deg)))];
      $far = ["x" => $x + round(10 * cos(deg2rad($deg))), "y" => $y + round(10 * sin(deg2rad($deg)))];

      $areaTypeNear = $position->check_areatype($near["x"], $near["y"]);
      $areaTypeFar = $position->check_areatype($far["x"], $far["y"]);

      if (
        (($areaTypeNear == "lake") && $canMoveOnLake) ||
        (($areaTypeNear == "sea") && $canMoveOnSea)
      ) {
        $availableDirections[$deg] = $near;
      } elseif (
        (($areaTypeFar == "lake") && $canMoveOnLake) ||
        (($areaTypeFar == "sea") && $canMoveOnSea)
      ) {
        $availableDirections[$deg] = $far;
      }
    }
    return $availableDirections;
  }

  public function dockTo(Location $goal)
  {
    $shipName = urlencode("<CANTR LOCNAME ID=" . $this->getId() . ">");
    $goalName = urlencode("<CANTR LOCNAME ID=" . $goal->getId() . ">");

    $locs = $this->location->getSublocationsRecursive();
    $locs[] = $this->location->getId();
    $locs = array_map(function($lId) {
      return Location::loadById($lId);
    }, $locs);
    foreach ($locs as $loc) {
      $loc->setX($goal->getX());
      $loc->setY($goal->getY());

      Event::create(96, "SHIPNAME=$shipName DOCK=$goalName")->inLocation($loc)->show();
      $loc->saveInDb();
    }

    // docked to location or harbour
    if (in_array($goal->getType(), [LocationConstants::TYPE_OUTSIDE, LocationConstants::TYPE_BUILDING])) {
      $this->reportDockingToLand($goal, $shipName, $goalName, $locs);
    } else {
      $this->reportDockingToShip($goal, $shipName, $goalName);
    }

    // all reports are done, so finish relocation
    $this->location->setType(LocationConstants::TYPE_VEHICLE);
    $this->location->setRegion($goal->getId());
    $this->saveInDb();
  }


  /**
   * Informs if ship can undock in situ (to exactly the same position as parent) or has to select direction
   * @return bool true if can undock to the same position, false if has to select direction
   * @throws IllegalStateException when ship is not docked to anything
   */
  public function canUndockInSitu()
  {
    if ($this->location->getType() == LocationConstants::TYPE_SAILING_SHIP) {
      throw new IllegalStateException("Can't check undocking when ship " . $this->getId() . " isn't docked to anything.");
    }
    $parent = $this->location->getRoot();
    if ($parent->getType() == LocationConstants::TYPE_SAILING_SHIP) {
      return true;
    } elseif (in_array($parent->getType(), [LocationConstants::TYPE_OUTSIDE])) {
      return false;
    }
    $this->logger->error("Ship " . $this->getId() . " sailing or docked to inexistent loc (" . $parent->getId() . ")");
    throw new IllegalStateException("illegal parent id: " . $parent->getId());
  }

  /**
   * @param int $direction direction in which ship should undock, no arg = undock from ship to same (x,y)
   * @return Sailing object describing current sailing
   * @throws IllegalStateException when the ship is not docked to anything
   * @throws NotOnWaterException when trying to undock from other ship and not on water (would result in ship being blocked)
   */
  public function undock($direction = null)
  {
    $inSitu = $direction === null;

    try {
      $parent = Location::loadById($this->location->getRegion());
    } catch (InvalidArgumentException $e) {
      $this->logger->error("ship " . $this->getId() . " trying to undock " .
        "from inexistent loc (" . $this->location->getRegion() . ")");
      throw new IllegalStateException("can't undock at all");
    }

    $excluded = [];

    if ($inSitu) {

      if (!$this->onWater()) {
        throw new NotOnWaterException("");
      }
      $targetPosition = ["x" => $parent->getX(), "y" => $parent->getY()];

      $excluded[] = $parent->getId();
    } else {

      $allowedDirections = $this->getUndockingDirections();
      if (!array_key_exists($direction, $allowedDirections)) {
        throw new InvalidArgumentException("$direction is not a valid undocking direction");
      }
      $targetPosition = $allowedDirections[$direction];
    }

    $this->location->setType(LocationConstants::TYPE_SAILING_SHIP);
    $this->location->setRegion(0);
    $this->location->setX($targetPosition["x"]);
    $this->location->setY($targetPosition["y"]);

    $undockingFrom = urlencode("<CANTR LOCNAME ID=" . $parent->getId() . ">");
    $shipName = urlencode("<CANTR LOCNAME ID=" . $this->getId() . ">");

    $undockEvent = Event::create(26, "SHIP=$shipName PLACE=$undockingFrom");

    $locs = $this->getLocation()->getSublocationsRecursive();
    $locs[] = $this->getId();

    $parentRoot = $parent->getRoot();
    foreach ($locs as $locId) {
      $loc = Location::loadById($locId);
      if (!$inSitu) { // real undocking
        $this->reportTravelHistory($loc, $parentRoot, false);
      }
      if ($loc->getType() != LocationConstants::TYPE_BUILDING) {
        $undockEvent->inLocation($loc)->show();
      }
    }
    $undockEvent->inLocation($parent)->show();

    // remove old sailing logs
    $stm = $this->db->prepare("DELETE FROM sailinglogs WHERE vessel = :vesselId");
    $stm->bindInt("vesselId", $this->getId());
    $stm->execute();

    $this->cleanupProjects($parent);

    return Sailing::newInstance($this, $excluded);
  }

  private function reportDockingToLand($goal, $shipName, $goalName, $locs)
  {
    $goals = [$goal];
    if ($goal->getType() == LocationConstants::TYPE_BUILDING) { // harbour
      try {
        $goals[] = Location::loadById($goal->getRegion()); // central area where harbour is located
      } catch (InvalidArgumentException $e) {
        $this->logger->error("unable to instantiate location " . $goal->getRegion() .
          " where harbor " . $goal->getId() . " is located");
      }
    }
    // notify people in central area and, optionally, harbour
    foreach ($goals as $loc) {
      Event::create(96, "SHIPNAME=$shipName DOCK=$goalName")->inLocation($loc)->show();
    }

    // add travel history for everyone on docking ship
    $goalRoot = $goal->getRoot();
    foreach ($locs as $loc) {
      $this->reportTravelHistory($loc, $goalRoot, true);
    }
  }

  private function reportDockingToShip($goal, $shipName, $goalName)
  {
    $goalSub = $goal->getSublocationsRecursive();
    $goalSub[] = $goal->getId();
    foreach ($goalSub as $locId) {
      $loc = Location::loadById($locId);
      Event::create(96, "SHIPNAME=$shipName DOCK=$goalName")->inLocation($loc)->show();
    }
  }

  private function reportTravelHistory($loc, $parent, $isArrival)
  {
    $arrival = $isArrival ? 1 : 0;
    $gameDate = GameDate::NOW();
    $chars = $loc->getCharacterIds();
    $stm = $this->db->prepare("INSERT INTO travelhistory (person, location, arrival, day, hour, vehicle)
        VALUES (:charId, :locationId, :arrival, :day, :hour, :vehicle)");
    foreach ($chars as $charId) {
      $stm->bindInt("charId", $charId);
      $stm->bindInt("locationId", $parent->getId());
      $stm->bindInt("arrival", $arrival);
      $stm->bindInt("day", $gameDate->getDay());
      $stm->bindInt("hour", $gameDate->getHour());
      $stm->bindInt("vehicle", $this->getId());
      $stm->execute();
    }
  }

  private function cleanupProjects($from)
  {
    $undockChars = array_merge($from->getCharacterIds(), $this->getLocation()->getCharacterIds());
    $draggingManager = new DraggingManager($undockChars);
    $draggingManager->tryFinishingAll();

    $signChange = Project::locatedIn($from)->type(ProjectConstants::TYPE_ALTERING_SIGN)->subtype($this->getId())->find();
    if ($signChange !== null) {
      $stm = $this->db->prepare("UPDATE chars SET project = 0 WHERE project = :projectId");
      $stm->bindInt("projectId", $signChange->getId());
      $stm->execute();
      $stm = $this->db->prepare("DELETE FROM projects WHERE id = :projectId");
      $stm->bindInt("projectId", $signChange->getId());
      $stm->execute();
    }
  }


  public function getAreaType()
  {
    $pos = Position::getInstance();
    return $pos->check_areatype(round($this->location->getX()), round($this->location->getY()));
  }


  public function onWater()
  {
    return in_array($this->getAreaType(), ["sea", "lake"]);
  }

  public function getLocation()
  {
    return $this->location;
  }

  public function isSailing()
  {
    return $this->location->getType() == LocationConstants::TYPE_SAILING_SHIP;
  }

  private function getMaxBasicSpeed()
  {
    $totalWeight = $this->location->getTotalWeightWithSublocations();
    $speed = $this->rules["speed"] - ($totalWeight / $this->rules["weightdelay"]);
    return $speed;
  }

  public function getMaxDeckSpeed()
  {
    $basicSpeed = $this->getMaxBasicSpeed();
    return $basicSpeed * $this->getControlsMultiplier("deck");
  }

  public function getMaxSailsSpeed()
  {
    $basicSpeed = $this->getMaxBasicSpeed();
    $speed = $basicSpeed * ($this->getControlsMultiplier("sails") - 1);
    return $speed;
  }

  private function getControlsMultiplier($property)
  {
    $allBoosters = CObject::locatedIn($this->getLocation())
      ->hasProperty("BoostSailing")->findAll();

    $boosters = [];
    foreach ($allBoosters as $booster) {
      $boostSailingProp = $booster->getProperty("BoostSailing");
      if ($boostSailingProp[$property]) {
        $boosters[$booster->getId()] = $boostSailingProp[$property];
      }
    }
    $multiplier = 1;
    foreach ($boosters as $boostObjId => $boostProp) {
      if (array_key_exists("passive", $boostProp)) {
        $multiplier += $boostProp["passive"];
      }
      if (array_key_exists("active", $boostProp)) {
        $boostProject = Project::locatedIn($this->getLocation())
          ->type(ProjectConstants::TYPE_BOOSTING_VEHICLE)->subtype($boostObjId)->find();
        if ($boostProject != null) {
          $workers = $boostProject->getResult();
          $workers = (!empty($workers) ? $workers : 0);
          $multiplier += $workers * $boostProp["active"];
        }
      }
    }

    return $multiplier;
  }

  public function getSpeedLostByWeight()
  {
    $weight = $this->getLocation()->getTotalWeightWithSublocations();
    $weightDelay = $this->rules['weightdelay'];
    return $weight / $weightDelay;
  }

  public function getId()
  {
    return $this->id;
  }

  public function saveInDb()
  {
    $this->location->saveInDb();
  }
}
