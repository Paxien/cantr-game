<?php

class MessengerBird
{
  const FIRST_MESSENGER_HOME = 1;
  const SECOND_MESSENGER_HOME = 2;

  const MESSENGER_TRAVEL_TURNS_PER_DAY = 96;

  /** @var CObject */
  private $birdObject;

  /** @var Logger */
  private $logger;

  /** @var boolean */
  private $removed;

  /** @var Db */
  private $db;

  /** @var int|null */
  private $firstHome;

  /** @var int|null */
  private $firstHomeRoot;

  /** @var int|null */
  private $secondHome;

  /** @var int|null */
  private $secondHomeRoot;

  /** @var int|null */
  private $goalWhichHome;

  /** @var float|null */
  private $x;

  /** @var float|null */
  private $y;

  public function __construct(CObject $bird, Db $db)
  {
    if (!$bird->hasProperty("MessengerBird")) {
      throw new InvalidArgumentException("Object " . $bird->getId() . " doesn't have a property MessengerBird");
    }

    $this->birdObject = $bird;
    $this->db = $db;
    $this->logger = Logger::getLogger(__CLASS__);

    $stm = $this->db->prepare("SELECT * FROM `messenger_birds` WHERE `object_id` = :id");
    $stm->bindInt("id", $bird->getId());
    $stm->execute();

    if ($messengerData = $stm->fetch()) {
      $this->firstHome = $messengerData->first_home;
      $this->firstHomeRoot = $messengerData->first_home_root;
      $this->secondHome = $messengerData->second_home;
      $this->secondHomeRoot = $messengerData->second_home_root;
      $this->x = $messengerData->x;
      $this->y = $messengerData->y;
      $this->goalWhichHome = $messengerData->goal_which_home;
    }
    // when the bird is just created, then the row in db may be missing
  }

  public function dispatchToHome(Character $sender, $toWhichHome)
  {
    if ($toWhichHome == MessengerBird::FIRST_MESSENGER_HOME) {
      $homeRootId = $this->getFirstHomeRoot();
    } elseif ($toWhichHome == MessengerBird::SECOND_MESSENGER_HOME) {
      $homeRootId = $this->getSecondHomeRoot();
    } else {
      throw new InvalidArgumentException("$toWhichHome is not a valid home identifier");
    }
    if (!$homeRootId) {
      throw new InvalidArgumentException("$toWhichHome home is not set");
    }

    $this->setInitialFlyingPosition($sender);
    $this->setGoalWhichHome($toWhichHome);
    $this->removeBirdFromMap();
    $this->createEvent($sender, $homeRootId);
  }

  private function createEvent(Character $sender, $homeRootId)
  {
    $position = $sender->getPos();

    $homeRoot = Location::loadById($homeRootId);
    $initialX = $position["x"];
    $initialY = $position["y"];
    $homeX = $homeRoot->getX();
    $homeY = $homeRoot->getY();

    $xDiff = $this->getVectorWrappedAroundEdges($initialX, $homeX, MapConstants::MAP_WIDTH);
    $yDiff = $this->getVectorWrappedAroundEdges($initialY, $homeY, MapConstants::MAP_HEIGHT);

    $direction = rad2deg(atan2($yDiff, $xDiff));

    import_lib("func.getdirection.inc.php");
    Event::create(386, "DIRECTION=" . getdirectionrawname($direction) .
      " BIRD_ID=" . $this->birdObject->getId())->forCharacter($sender)->show();
    Event::create(387, "ACTOR=" . $sender->getId() .
      " DIRECTION=" . getdirectionrawname($direction) . " BIRD_ID=" . $this->birdObject->getId())
      ->nearCharacter($sender)->andAdjacentLocations()->except($sender)->show();
  }

  private function getVectorWrappedAroundEdges($from, $to, $size)
  {
    $diff = $to - $from;
    if ($diff > $size / 2) {
      return $diff - $size;
    } elseif ($diff < -$size / 2) {
      return $diff + $size;
    }
    return $diff;
  }

  private function removeBirdFromMap()
  {
    //remove from map
    $this->birdObject->setLocation(0);
    $this->birdObject->setPerson(0);
    $this->birdObject->setAttached(0);
  }

  /**
   * @param Character $sender
   */
  private function setInitialFlyingPosition(Character $sender)
  {
    $position = $sender->getPos();

    $this->setX($position["x"]);
    $this->setY($position["y"]);
  }

  public function continueFlyingToHome()
  {
    $x = $this->getX();
    $y = $this->getY();
    $goalWhichHome = $this->getGoalWhichHome();
    $homeRoot = Location::loadById($this->getHomeRootId($goalWhichHome));
    $homeX = $homeRoot->getX();
    $homeY = $homeRoot->getY();

    $xDiff = $this->getVectorWrappedAroundEdges($x, $homeX, MapConstants::MAP_WIDTH);
    $yDiff = $this->getVectorWrappedAroundEdges($y, $homeY, MapConstants::MAP_HEIGHT);

    $maxSpeed = $this->getMaxSpeed();
    $distance = Measure::vectorLength($xDiff, $yDiff);
    $direction = Measure::vectorDirection($xDiff, $yDiff);

    if ($distance <= $maxSpeed) {
      $fromDirection = (rad2deg($direction) + 180) % 360;
      $this->landInHome($goalWhichHome, $homeRoot, $fromDirection);
    }

    $x += $maxSpeed * cos($direction);
    $y += $maxSpeed * sin($direction);

    $this->setX(MapUtil::getNormalizedX($x));
    $this->setY(MapUtil::getNormalizedY($y));
  }

  private function landInHome($goalWhichHome, Location $homeRoot, $fromDirection)
  {
    $homeNestId = $this->getHomeId($goalWhichHome);

    try {
      $homeNest = CObject::loadById($homeNestId);
      $locationIdOfHomeNest = $homeNest->getLocation();
      $locationOfHomeNest = Location::loadById($locationIdOfHomeNest);
      $locationRoot = $locationOfHomeNest->getRoot();
      if ($locationRoot != null && $locationRoot->getId() == $homeRoot->getId()) {
        $this->landInNest($homeNest, $locationOfHomeNest, $homeRoot, $fromDirection);
        return;
      }
    } catch (InvalidArgumentException $e) {
      $this->logger->info("Couldn't find home nest $homeNestId for bird " . $this->birdObject->getId());
    }

    $this->landInCentralArea($homeRoot, $fromDirection);
  }

  private function landInNest(CObject $homeNest, Location $locationOfHomeNest, Location $homeRoot, $fromDirection)
  {
    $this->birdObject->setAttached($homeNest->getId());
    $homeNest->setWeight($homeNest->getWeight() + $this->birdObject->getWeight());
    $homeNest->saveInDb();
    $this->resetFlyingPosition();

    $outermostBuildingId = $this->getOuterMostBuilding($locationOfHomeNest);

    Event::create(388, "DIRECTION=" . MapUtil::getDirectionTagName($fromDirection) .
      " BIRD_ID=" . $this->birdObject->getId() . " HOME_NEST_ID=" . $homeNest->getId())
      ->inLocation($locationOfHomeNest)->show(); // location with nest
    if ($locationOfHomeNest->getId() != $homeRoot->getId()) {
      Event::create(389, "DIRECTION=" . MapUtil::getDirectionTagName($fromDirection) . " BIRD_ID=" . $this->birdObject->getId() . " OUTERMOST_BUILDING_ID=" . $outermostBuildingId)
        ->inLocation($homeRoot)->show(); // outside
    }
  }

  private function landInCentralArea(Location $homeRoot, $fromDirection)
  {
    $this->birdObject->setLocation($homeRoot->getId());
    $this->resetFlyingPosition();

    import_lib("func.getdirection.inc.php");
    Event::create(390, "DIRECTION=" . getdirectionrawname($fromDirection) . " BIRD_ID=" . $this->birdObject->getId()
      . " LOCATION=" . $homeRoot->getId())
      ->inLocation($homeRoot)->show(); // outside
  }

  private function resetFlyingPosition()
  {
    $this->setX(null);
    $this->setY(null);
    $this->setGoalWhichHome(null);
  }

  public function getHomeId($whichHome)
  {
    if ($whichHome == MessengerBird::FIRST_MESSENGER_HOME) {
      return $this->getFirstHome();
    } elseif ($whichHome == MessengerBird::SECOND_MESSENGER_HOME) {
      return $this->getSecondHome();
    }
    throw new InvalidArgumentException("$whichHome is not a valid home id");
  }

  public function getHomeRootId($whichHome)
  {
    if ($whichHome == MessengerBird::FIRST_MESSENGER_HOME) {
      return $this->getFirstHomeRoot();
    } elseif ($whichHome == MessengerBird::SECOND_MESSENGER_HOME) {
      return $this->getSecondHomeRoot();
    }
    throw new InvalidArgumentException("$whichHome is not a valid home id");
  }

  /**
   * @param Location $location the location to start traversing from
   * @return int id of location which is the direct child of a root when traversing to the root location
   */
  private function getOuterMostBuilding(Location $location)
  {
    $outermostBuilding = $location;
    try {
      while (true) {
        $locationParent = Location::loadById($outermostBuilding->getRegion());
        if ($locationParent->getType() == LocationConstants::TYPE_OUTSIDE) {
          break;
        }
        $outermostBuilding = $locationParent;
      }
    } catch (InvalidArgumentException $e) {
      $this->logger->warn("Unable to get the outermost building for a landing of messenger bird " . $this->birdObject->getId()
        . " for location " . $location->getId());
      return 0;
    }
    return $outermostBuilding->getId();
  }

  public function remove()
  {
    $this->removed = true;
  }

  public function saveInDb()
  {
    $stm = $this->db->prepare("SELECT object_id FROM `messenger_birds` WHERE `object_id` = :objectId");
    $stm->bindInt("objectId", $this->birdObject->getId());
    $exists = $stm->executeScalar();
    if (!$exists && !$this->removed) {
      $stm = $this->db->prepare("INSERT INTO `messenger_birds` (`object_id`, `first_home`, `first_home_root`,
        `second_home`, `second_home_root`, `x`, `y`, `goal_which_home`)
          VALUES (:objectId, :firstHome, :firstHomeRoot, :secondHome, :secondHomeRoot, :x, :y, :goalWhichHome)");

      $this->bindValuesToStatement($stm);
      $stm->execute();
    } elseif ($exists && !$this->removed) {
      $stm = $this->db->prepare("UPDATE `messenger_birds` SET `first_home` = :firstHome, `first_home_root` = :firstHomeRoot,
        `second_home` = :secondHome, `second_home_root` = :secondHomeRoot,
        `x` = :x, `y` = :y, `goal_which_home` = :goalWhichHome WHERE `object_id` = :objectId");

      $this->bindValuesToStatement($stm);
      $stm->execute();
    } elseif ($exists && $this->removed) {
      $stm = $this->db->prepare("DELETE FROM `messenger_birds` WHERE `object_id` = :objectId");
      $stm->bindInt("objectId", $this->birdObject->getId());
      $stm->execute();
    } else {
      throw new IllegalStateException("Trying to remove inexistent MessengerBird");
    }
    $this->birdObject->saveInDb();
  }

  private function bindValuesToStatement(DbStatement $stm)
  {
    $stm->bindInt("objectId", $this->birdObject->getId());
    $stm->bindInt("firstHome", $this->firstHome, true);
    $stm->bindInt("firstHomeRoot", $this->firstHomeRoot, true);
    $stm->bindInt("secondHome", $this->secondHome, true);
    $stm->bindInt("secondHomeRoot", $this->secondHomeRoot, true);
    $stm->bindFloat("x", $this->x, true);
    $stm->bindFloat("y", $this->y, true);
    $stm->bindInt("goalWhichHome", $this->goalWhichHome, true);
  }

  /**
   * @return int|null
   */
  public function getFirstHome()
  {
    return $this->firstHome;
  }

  public function setFirstHome(CObject $firstHome)
  {
    $this->firstHome = $firstHome->getId();
  }

  /**
   * @return int|null
   */
  public function getSecondHome()
  {
    return $this->secondHome;
  }

  public function setSecondHome(CObject $secondHome)
  {
    $this->secondHome = $secondHome->getId();
  }

  /**
   * @return int|null
   */
  public function getFirstHomeRoot()
  {
    return $this->firstHomeRoot;
  }

  /**
   * @param Location $firstHomeRoot location being centeral area
   * @throws InvalidAmountException when location is not a central area
   */
  public function setFirstHomeRoot(Location $firstHomeRoot)
  {
    if ($firstHomeRoot->getType() != LocationConstants::TYPE_OUTSIDE) {
      throw new InvalidAmountException($firstHomeRoot->getId() . " is not a central area");
    }
    $this->firstHomeRoot = $firstHomeRoot->getId();
  }

  /**
   * @return int|null
   */
  public function getSecondHomeRoot()
  {
    return $this->secondHomeRoot;
  }

  /**
   * @param Location $secondHomeRoot location being centeral area
   * @throws InvalidAmountException when location is not a central area
   */
  public function setSecondHomeRoot(Location $secondHomeRoot)
  {
    if ($secondHomeRoot->getType() != LocationConstants::TYPE_OUTSIDE) {
      throw new InvalidAmountException($secondHomeRoot->getId() . " is not a central area");
    }
    $this->secondHomeRoot = $secondHomeRoot->getId();
  }

  /**
   * @return int|null
   */
  public function getGoalWhichHome()
  {
    return $this->goalWhichHome;
  }

  /**
   * @param int|null $goalWhichHome
   */
  public function setGoalWhichHome($goalWhichHome)
  {
    $this->goalWhichHome = $goalWhichHome;
  }

  /**
   * @return float|null
   */
  public function getX()
  {
    return $this->x;
  }

  /**
   * @param float|null $x
   */
  public function setX($x)
  {
    $this->x = $x;
  }

  /**
   * @return float|null
   */
  public function getY()
  {
    return $this->y;
  }

  /**
   * @param float|null $y
   */
  public function setY($y)
  {
    $this->y = $y;
  }

  /**
   * @return float
   */
  public function getMaxSpeed()
  {
    $messengerBirdProp = $this->birdObject->getProperty("MessengerBird");
    return $messengerBirdProp["speed"] / self::MESSENGER_TRAVEL_TURNS_PER_DAY;
  }
}
