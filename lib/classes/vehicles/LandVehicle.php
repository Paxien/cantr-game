<?php

class LandVehicle extends Vehicle
{

  protected $id;
  protected $location; // composition

  // capacity and travelling
  private $baseSpeed;
  private $weightDelay;
  private $maxPossibleSpeed; // null if there's no limit
  /** @var Db */
  protected $db;

  public static function loadById($vehicleId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM locations WHERE id = :id");
    $stm->bindInt("id", $vehicleId);
    $stm->execute();
    if ($fetchedObject = $stm->fetchObject()) {
      return self::loadFromFetchObject($fetchedObject, $db);
    }
    throw new InvalidArgumentException("$vehicleId isn't a valid vehicle id");
  }

  private static function loadFromFetchObject(stdClass $fetchedObject, Db $db)
  {
    $stm = $db->prepare("SELECT * FROM objecttypes WHERE id = :id");
    $stm->bindInt("id", $fetchedObject->area);
    $stm->execute();
    $fetchedTypeData = $stm->fetchObject();

    if ($fetchedObject == null || $fetchedTypeData == null) {
      throw new InvalidArgumentException("Vehicle with id $fetchedObject->id doesn't exist");
    }
    $stm = $db->prepare("SELECT * FROM animal_domesticated WHERE from_location = :locationId");
    $stm->bindInt("locationId", $fetchedObject->id);
    $stm->execute();
    if ($animalDetails = $stm->fetchObject()) {
      return new Steed($fetchedObject, $fetchedTypeData, $animalDetails, $db);
    }
    if (strpos($fetchedTypeData->rules, "engine") !== false) { // vehicle with engine
      return new FueledVehicle($fetchedObject, $fetchedTypeData, $db);
    } else { // without engine
      return new LandVehicle($fetchedObject, $fetchedTypeData, $db);
    }
  }

  // constructor should be only used in subclasses
  public function __construct(stdClass $fetchedObject, stdClass $fetchedTypeData, Db $db)
  {
    if ($fetchedObject->type != LocationConstants::TYPE_VEHICLE) {
      throw new InvalidArgumentException("location $fetchedObject->id is not a land vehicle");
    }
    $this->id = $fetchedObject->id;

    $vehicleData = Parser::rulesToArray($fetchedTypeData->rules);
    $this->baseSpeed = $vehicleData['speed'];
    $this->weightDelay = $vehicleData['weightdelay'];

    $this->maxPossibleSpeed = null;
    if (array_key_exists("maxspeed", $vehicleData)) {
      $this->maxPossibleSpeed = $vehicleData['maxspeed'];
    }

    $this->db = $db;
    $this->location = Location::loadById($this->getId());
  }

  public function canMoveOnLand()
  {
    return true;
  }

  public function canTurnAround()
  {
    $canTurnAround = $this->getProperty("CanTurnAround");
    if ($canTurnAround === null) {
      $canTurnAround = true;
    }
    return $canTurnAround != false;
  }

  public function canTravelMultipleLocations()
  {
    return $this->getProperty("CanTravelMultipleLocations");
  }

  public function tryDrive(Character $char)
  {
    $controls = CObject::locatedIn($this->getLocation())->hasProperty("ControlsVehicle")->findIds();
    foreach ($controls as $ctrlObjId) {
      $keyLock = KeyLock::loadByObjectId($ctrlObjId);
      if (!$keyLock->canAccess($char->getId())) {
        throw new DisallowedActionException("error_no_access_to_controls");
      }
    }

    if ($this->location->getRegion() > 0) { // not when already travelling
      $objectsPreventingTravel = CObject::locatedIn($this->location->getRegion())
        ->hasProperty("PreventStartingTravel")
        ->findAll();
      $vehicleUniqueName = $this->getLocation()->getTypeUniqueName();
      foreach ($objectsPreventingTravel as $obj) {
        $preventStartingTravelProp = $obj->getProperty("PreventStartingTravel");
        if ($preventStartingTravelProp === null) {
          $preventStartingTravelProp = [];
        }
        if (in_array($vehicleUniqueName, $preventStartingTravelProp)) {
          throw new DisallowedActionException("error_object_preventing_travel OBJECT_ID=" . $obj->getId());
        }
      }
    }

    return true;
  }

  public function getSpeed($speed, $maxSpeed)
  {
    return $speed;
  }

  public function getMaxSpeed(Connection $connection)
  {
    $speed = $this->baseSpeed;

    $roadMultiplier = $connection->getConnectionTypePartFor($this->getVehicleType())->getType()->getSpeedFactor(); // based on road improvement level
    $speedMultipler = $this->getMaxSpeedMultiplier(); // e.g. caused by engine
    $speed *= $roadMultiplier;
    $speed *= $speedMultipler;

    if ($this->maxPossibleSpeed !== null) {
      $speedLimit = $this->maxPossibleSpeed;
      $speed = min($speed, $speedLimit);
    } else {
      $speedLimit = $speed; // no limit
    }

    $controlsBoost = 0;
    if ($speedMultipler > 0) {
      $controlsBoost = ($speedLimit / $this->baseSpeed) * $this->getControlsBoost(); // it looks like a mistake, but it's NOT
    }
    $speed += $controlsBoost;

    $speedAffectedByWeight = max($this->getVehicleMinSpeed(), $speed - $this->getCargoWeight() / $this->weightDelay);
    // cargo weight shouldn't make vehicle slower than min speed, unless it would be because of other factors
    $speed = min($speed, $speedAffectedByWeight);

    return max($speed, 0); // if speed is too small to move at all then return 0
  }

  private function getVehicleMinSpeed()
  {
    import_lib("stddef.inc.php");
    return _WALKING_SPEED / 2;
  }

  private function getControlsBoost()
  {
    $allBoosters = CObject::locatedIn($this->getLocation())->hasProperty("BoostTraveling")->findAll();

    $boosters = [];
    foreach ($allBoosters as $booster) {
      $boosters[$booster->getId()] = $booster->getProperty("BoostTraveling");
    }

    $multiplier = 0;
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


  public function getMaxSpeedMultiplier()
  {
    return 1;
  }

  public function reduceFuel($toGo, $maxSpeed)
  {
    // do nothing, doesn't require fuel
  }

  public function saveInDb()
  {
    $this->location->saveInDb();
    if ($this->location->getExpiredDate() == 0) {
      $stm = $this->db->prepare("UPDATE radios SET x = :x, y = :y
        WHERE location = :locationId");
      $stm->bindInt("locationId", $this->getId());
      $stm->bindInt("x", $this->location->getX());
      $stm->bindInt("y", $this->location->getY());
      $stm->execute();
    }
  }

  public function getId()
  {
    return $this->id;
  }

  public function hasId()
  {
    return ($this->getId() != null);
  }

  public function getRegion()
  {
    return $this->location->getRegion();
  }

  public function setRegion($region)
  {
    $this->location->setRegion($region);
  }

  public function getVehicleType()
  {
    return $this->location->getArea();
  }

  public function getLocation()
  {
    return $this->location;
  }

  public function setCoordinates($posX, $posY)
  {
    $this->location->setX($posX);
    $this->location->setY($posY);
  }

  public function remove()
  {
    $this->location->remove();
  }

  public function getCargoWeight()
  {
    return $this->location->getTotalWeightWithSublocations();
  }

  public function getProperty($name)
  {
    return $this->getLocation()->getProperty($name);
  }

  public function hasProperty($name)
  {
    return $this->getLocation()->hasProperty($name);
  }

  /**
   * @param Connection $prevConnection
   * @param Travel $prevTravel
   * @return Connection|null to ride next or null if vehicle should stop
   */
  public function travelContinuation(Connection $prevConnection, Travel $prevTravel)
  {
    if ($this->shouldStop($prevTravel->getDestination())) { // there's something forcing vehicle to stop
      return null;
    }

    $stm = $this->db->prepare("SELECT id FROM connections
      WHERE :destination IN (start, end) AND id != :previousConnection");
    $stm->bindInt("destination", $prevTravel->getDestination());
    $stm->bindInt("previousConnection", $prevConnection->getId());
    $stm->execute();

    $intermediateDestination = Location::loadById($prevTravel->getDestination());
    $accessibleRoadTypes = $intermediateDestination->getProperty("AccessibleRoadTypes");

    $connections = Pipe::from($stm->fetchScalars())
      ->map(function($connId) {
        return Connection::loadById($connId);
      })->filter(function(Connection $conn) use ($accessibleRoadTypes) {
        return $conn->canBeMovedOn($this->getVehicleType(), $accessibleRoadTypes);
      })->toArray();

    if (count($connections) == 0) {
      return null;
    }

    return $connections[array_rand($connections)];
  }

  /**
   * @param $destination int root location to which the vehicle arrives
   * @return int the location in which vehicle should be placed. It should return $destination or one of its sublocations
   */
  public function getPreferredArrivalSublocationOverride($destination)
  {
    $preferArrivalSublocationProp = $this->getProperty("PreferArrivalSublocation");
    if (!empty($preferArrivalSublocationProp)) {
      $locationSubtypes = ObjectHandler::getIdsByUniqueNames($preferArrivalSublocationProp);
      $foundLocaton = LocationFinder::any()
        ->subtypes($locationSubtypes)
        ->region($destination)
        ->find();
      if ($foundLocaton) {
        return $foundLocaton->getId();
      }
    }

    return $destination;
  }

  /**
   * @param location int root location id
   * @return boolean if there is an object or location that forces vehicle that can travel multiple locations to stop
   */
  public function shouldStop($location)
  {
    $canTravelMultipleLocationsProp = $this->location->getProperty("CanTravelMultipleLocations");
    return $this->shouldStopBecauseOfLocation($location, $canTravelMultipleLocationsProp["locationsToStop"])
      || $this->shouldStopBecauseOfObject($location, $canTravelMultipleLocationsProp["objectsToStop"]);
  }

  /**
   * @param $location int location id which is parent of the locations of specified subtypes
   * @param $locationSubtypesUniqueNames
   * @return
   */
  public function shouldStopBecauseOfLocation($location, $locationSubtypesUniqueNames)
  {
    if (!empty($locationSubtypesUniqueNames)) {
      $locationSubtypes = ObjectHandler::getIdsByUniqueNames($locationSubtypesUniqueNames);
      return LocationFinder::any()
        ->subtypes($locationSubtypes)
        ->region($location)
        ->exists();
    }
    return false;
  }

  /**
   * @param $location int location id in which to look for object of specified types
   * @param $objectTypeUniqueNames
   * @return
   */
  public function shouldStopBecauseOfObject($location, $objectTypeUniqueNames)
  {
    if (!empty($objectTypeUniqueNames)) {
      $objectTypes = ObjectHandler::getIdsByUniqueNames($objectTypeUniqueNames);
      return CObject::locatedIn($location)
        ->types($objectTypes)
        ->exists();
    }
    return false;
  }
}
