<?php

class Location
{

  // registry for cached location objects
  /**
   * @var DbObjectRegistry
   */
  private static $registry;

  // Private members mapping to database 'locations' table.  
  private $id = 0;
  private $name = "";
  private $local_number = 0;
  private $type = 0;
  private $region = 0;
  private $area = 0;
  private $borders_lake = 0;
  private $borders_sea = 0;
  private $map = 0;
  private $x = 0;
  private $y = 0;
  private $island = 0;
  private $deterioration = 0;
  private $expired_date = 0;
  private $digging_slots = 0;

  /** @var Db */
  protected $db;
  /** @var Logger */
  private $logger;

  public static function staticInit()
  {
    self::$registry = new DbObjectRegistry();
  }

  // Constructor
  protected function __construct($mysqlRow, Db $db)
  {
    $this->setId($mysqlRow->id);
    $this->setName($mysqlRow->name);
    $this->setLocalNumber($mysqlRow->local_number);
    $this->setType($mysqlRow->type);
    $this->setRegion($mysqlRow->region);
    $this->setArea($mysqlRow->area);
    $this->setBordersLake($mysqlRow->borders_lake);
    $this->setBordersSea($mysqlRow->borders_sea);
    $this->setX($mysqlRow->x);
    $this->setY($mysqlRow->y);
    $this->setIsland($mysqlRow->island);
    $this->setDeterioration($mysqlRow->deterioration);
    $this->setExpiredDate($mysqlRow->expired_date);
    $this->setDiggingSlots($mysqlRow->digging_slots);

    $this->logger = Logger::getLogger("Location");
    $this->db = $db;
  }


  /**
   * @param int[] $locationIds
   * @return Location[]
   */
  public static function bulkLoadByIds(array $locationIds)
  {
    $db = Db::get();
    if (!Validation::isPositiveIntArray($locationIds)) {
      throw new InvalidArgumentException("some of location ids isn't a positive integer" . var_export($locationIds, true));
    }
    if (empty($locationIds)) {
      return [];
    }

    $stm = $db->prepareWithIntList("SELECT * FROM locations WHERE id IN (:ids)", [
      "ids" => $locationIds,
    ]);
    $stm->execute();
    $unorderedLocations = [];
    foreach ($stm->fetchAll() as $locationRow) {
      if (self::$registry->contains($locationRow->id)) {
        $unorderedLocations[$locationRow->id] = self::$registry->get($locationRow->id);
      } else { // get from cache if already exists
        $loc = self::loadFromFetchObject($locationRow);
        $unorderedLocations[$locationRow->id] = $loc;
        self::$registry->put($locationRow->id, $loc);
      }
    }

    $locations = [];
    foreach ($locationIds as $lId) { // guarantee the same order
      if (array_key_exists($lId, $unorderedLocations)) {
        $locations[] = $unorderedLocations[$lId];
      }
    }

    return $locations;
  }

  // Return a Location object defined by its ID in the database
  public static function loadById($locationId)
  {
    if (self::$registry->contains($locationId)) {
      return self::$registry->get($locationId);
    }
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM locations WHERE id = :locationId LIMIT 1");
    $stm->bindInt("locationId", $locationId);
    $stm->execute();
    if ($locationInfo = $stm->fetchObject()) {
      $obj = self::loadFromFetchObject($locationInfo, $db);
      self::$registry->put($locationId, $obj);
      return $obj;
    }
    throw new InvalidArgumentException("no location with id $locationId");
  }

  public static function loadFromFetchObject(stdClass $locationInfo, Db $db)
  {
    if ($locationInfo->type == LocationConstants::TYPE_BUILDING) {
      return new Building($locationInfo, $db);
    } else {
      return new Location($locationInfo, $db);
    }
  }


  // Getters and setters
  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getLocalNumber()
  {
    return $this->local_number;
  }

  public function setLocalNumber($localNumber)
  {
    $this->local_number = $localNumber;
  }

  public function getType()
  {
    return $this->type;
  }

  public function setType($type)
  {
    $this->type = $type;
  }

  public function getRegion()
  {
    return $this->region;
  }

  public function setRegion($region)
  {
    $this->region = $region;
  }

  public function getArea()
  {
    return $this->area;
  }

  /**
   * @return ObjectType type of buildable object if it's not outside or terrain type otherwise
   */
  public function getObjectType()
  {
    return ObjectType::loadById($this->area);
  }

  public function setArea($area)
  {
    $this->area = $area;
  }

  public function bordersLake()
  {
    return $this->borders_lake;
  }

  public function setBordersLake($borders_lake)
  {
    $this->borders_lake = intval($borders_lake);
  }

  public function bordersSea()
  {
    return $this->borders_sea;
  }

  public function setBordersSea($borders_sea)
  {
    $this->borders_sea = intval($borders_sea);
  }

  public function bordersWater()
  {
    return $this->bordersSea() || $this->bordersLake();
  }

  public function getX()
  {
    return $this->x;
  }

  public function setX($x)
  {
    $this->x = $x;
  }

  public function getY()
  {
    return $this->y;
  }

  public function setY($y)
  {
    $this->y = $y;
  }

  public function getIsland()
  {
    return $this->island;
  }

  public function setIsland($island)
  {
    $this->island = $island;
  }

  public function getDeterioration()
  {
    return $this->deterioration;
  }

  public function setDeterioration($deterioration)
  {
    $this->deterioration = $deterioration;
  }

  public function getExpiredDate()
  {
    return $this->expired_date;
  }

  public function setExpiredDate($expired_date)
  {
    $this->expired_date = $expired_date;
  }

  public function getDiggingSlots()
  {
    return $this->digging_slots;
  }

  public function setDiggingSlots($digging_slots)
  {
    $this->digging_slots = $digging_slots;
  }

  public function isOutside()
  {
    return $this->getType() == LocationConstants::TYPE_OUTSIDE;
  }

  public function isBuilding()
  {
    return $this->getType() == LocationConstants::TYPE_BUILDING;
  }

  public function isVehicle()
  {
    return $this->getType() == LocationConstants::TYPE_VEHICLE;
  }

  public function isSubfield()
  {
    return $this->getType() == LocationConstants::TYPE_SUBFIELD;
  }

  public function isSailingShip()
  {
    return $this->getType() == LocationConstants::TYPE_SAILING_SHIP;
  }

  public function getTypeUniqueName()
  {
    return $this->getObjectType()->getUniqueName();
  }

  public function getMaxWeight()
  {
    $maxWeight = $this->getMaxLoadHelper('maxweight');
    import_lib("func.calcweight.inc.php");
    $capAddedByObjects = addcapacity($this->id, $this->db);
    return $maxWeight + $capAddedByObjects;
  }

  public function getMaxCharacters()
  {
    $maxCharacters = $this->getMaxLoadHelper('maxpeople');

    $stm = $this->db->prepare("SELECT t.rules AS rules
      FROM objects AS o,objecttypes AS t
      WHERE o.type = t.id AND o.location = :locationId AND t.rules LIKE '%addpeople:%'");
    $stm->bindInt("locationId", $this->getId());
    $stm->execute();
    foreach ($stm->fetchScalars() as $rule) {
      $rulesArray = Parser::rulesToArray($rule);
      if (array_key_exists('addpeople', $rulesArray)) {
        $maxCharacters += $rulesArray['addpeople'];
      }
    }

    return $maxCharacters;
  }

  private function getMaxLoadHelper($ruleName)
  {
    if (in_array($this->type, [LocationConstants::TYPE_OUTSIDE, LocationConstants::TYPE_SUBFIELD])) {
      return PHP_INT_MAX;
    }
    $rules = $this->getObjectType()->getRules();
    $ruleParts = Parser::rulesToArray($rules);
    if (isset($ruleParts[$ruleName])) {
      return $ruleParts[$ruleName];
    }
    return PHP_INT_MAX; // default value
  }

  public function getVisibleObjects($isClose = true)
  {
    $visibleObjects = CObject::locatedIn($this)->hasProperty("VisibleOutside")->findAll();

    return Pipe::from($visibleObjects)
      ->filter(function(CObject $obj) use ($isClose) {
        $visibilityDistance = $obj->getProperty("VisibleOutside")["distance"];
        return ($visibilityDistance == "farAway" || ($visibilityDistance == "close" && $isClose));
      })->map(function(CObject $obj) {
        return $obj->getId();
      })->toArray();
  }

  /**
   * Weight of all objects and people in location, doesn't include stuff (objects and people) in extensions/sublocs
   */
  public function getTotalWeight()
  {
    import_lib("func.calcweight.inc.php");
    return calcweightlocal($this->id);
  }

  /**
   * Weight of all objects and people in location. Includes weight of sublocations, objects and people there
   */
  public function getTotalWeightWithSublocations()
  {
    import_lib("func.calcweight.inc.php");
    return calcweight($this->id);
  }

  public function getCharacterCount()
  {
    $stm = $this->db->prepare("SELECT COUNT(*) FROM chars WHERE location = :locationId AND status = :active");
    $stm->bindInt("locationId", $this->id);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    return $stm->executeScalar();
  }

  public function getCharacterIds()
  {
    $stm = $this->db->prepare("SELECT id FROM chars WHERE location = :locationId AND status = :active");
    $stm->bindInt("locationId", $this->id);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();
    return $stm->fetchScalars();
  }

  /**
   * Checks if location is empty
   * @return boolean true when there are no objects, projects, sublocations and chars in the location
   */
  public function isEmpty()
  {
    $objects = CObject::locatedIn($this->id)->count();
    $projects = Project::locatedIn($this->id)->count();
    $sublocs = count($this->getSublocations());
    $chars = $this->getCharacterCount();

    return ($objects + $projects + $sublocs + $chars) == 0;
  }

  public function isDestroyable()
  {
    return false;
  }

  public function isDisassemblable()
  {
    $rules = Parser::rulesToArray($this->getObjectType()->getRules());
    return ($this->getType() == LocationConstants::TYPE_VEHICLE) && isset($rules['disassemblable']);
  }

  public function getSublocations()
  {
    $stm = $this->db->prepare("SELECT id FROM locations WHERE region = :region AND type != :outside");
    $stm->bindInt("region", $this->id);
    $stm->bindInt("outside", LocationConstants::TYPE_OUTSIDE);
    $stm->execute();
    return $stm->fetchScalars();
  }

  public function getRoot()
  {
    $loc = $this;
    try {
      while (($loc->getType() != LocationConstants::TYPE_OUTSIDE) && ($loc->getRegion() > 0)) {
        $loc = Location::loadById($loc->getRegion());
      }
      return $loc;
    } catch (InvalidArgumentException $e) {
      $this->logger->error($loc->getRegion() . " - root of location " . $this->getId() . " doesn't exist");
      throw $e;
    }
  }

  public function getSublocationsRecursive(Closure $predicate = null)
  {
    $allSublocs = [];
    foreach ($this->getSublocations() as $sublocId) {
      try {
        $subloc = Location::loadById($sublocId);
        if (($predicate === null) || $predicate($subloc)) {
          $allSublocs[] = $subloc->getId();
          $allSublocs = array_merge($allSublocs, $subloc->getSublocationsRecursive($predicate));
        }
      } catch (InvalidArgumentException $e) {
        $this->logger->error("should never happen." .
          "Incorrect structure of locations for getSublocationsRecursive()", $e);
      }
    }
    return array_values(array_unique($allSublocs));
  }

  public function isAdjacentTo(Location $other)
  {
    $isOtherOutside = ($this->getType() != LocationConstants::TYPE_OUTSIDE) && ($this->getRegion() == $other->getId());
    $isOtherInside = ($other->getType() != LocationConstants::TYPE_OUTSIDE) && ($this->getId() == $other->getRegion());
    return ($isOtherOutside || $isOtherInside);
  }

  public function getAllUsedDiggingSlots()
  {
    return self::getUsedDiggingSlots($this->getId());
  }

  /**
   * Can be overridden in the subclasses.
   * @return bool true when the location has a child and everyone can look there
   */
  public function canLookOnParent()
  {
    if ($this->isVehicle()) {
      return true;
    }
    return false;
  }

  /**
   * @return Location parent location if it exists
   * @throws LocationHasNoParentException if this Location has no parent
   */
  public function getParent()
  {
    try {
      if ($this->getType() == LocationConstants::TYPE_OUTSIDE || $this->getRegion() == 0) {
        throw new InvalidArgumentException("Location {$this->getId()} is a root location or its parent ID is 0");
      }
      return self::loadById($this->getRegion());
    } catch (InvalidArgumentException $e) {
      throw new LocationHasNoParentException("Location {$this->getRegion()} has no parent location", 0, $e);
    }
  }

  /**
   * Returns true when the characters in the location are able to see the map of the area.
   * The location and its parent is performed (if exists), because sometimes visibility
   * depends on the parent (e.g. a steel cage in building vs in central area)
   * @return bool true if it's possible to see the map
   */
  public function isMapVisibilityEnabled()
  {
    if (in_array($this->getType(), [
        LocationConstants::TYPE_OUTSIDE,
        LocationConstants::TYPE_SAILING_SHIP,
      ])
      || $this->getRegion() == 0
    ) { // outside or in travel
      return true;
    }

    if ($this->getType() != LocationConstants::TYPE_VEHICLE) {
      return false;
    }

    try {
      $parentLocation = Location::loadById($this->getRegion());
      return $parentLocation->hasProperty("EnableMapVisibility");
    } catch (InvalidArgumentException $e) {
      $this->logger->warn("Parent of " . $this->getId() . " which has ID "
        . $this->getRegion() . " doesn't exist but it should");
      return false;
    }
  }

  public function getProperty($name)
  {
    if ($name == "Any") {
      return true;
    }

    $stm = $this->db->prepare("SELECT details FROM obj_properties WHERE objecttype_id = :objectTypeId
      AND property_type = :propertyType");
    $stm->bindInt("objectTypeId", $this->getArea());
    $stm->bindStr("propertyType", $name);
    $details = $stm->executeScalar();
    if (!empty($details)) {
      return json_decode($details, true);
    }
    return null;
  }

  public function hasProperty($name)
  {
    if ($name == "Any") {
      return true;
    }

    $stm = $this->db->prepare("SELECT COUNT(*) FROM obj_properties WHERE objecttype_id = :objectTypeId
      AND property_type = :propertyType");
    $stm->bindInt("objectTypeId", $this->getArea());
    $stm->bindStr("propertyType", $name);
    $matchingProps = $stm->executeScalar();
    return $matchingProps > 0;
  }

  public function isRepairable()
  {
    return !$this->isOutside() && $this->getObjectType()->getDeterRatePerDay() > 0;
  }

  public function remove()
  {
    $this->setRegion(-1 * $this->getRegion());
    $this->setX(null);
    $this->setY(null);
    $date = GameDate::NOW();
    $this->setExpiredDate($date->getDay() * 10 + $date->getHour());
  }

  /* must take care of position x, y */
  public function revive()
  {
    $this->setRegion(-1 * $this->getRegion());
    $this->setExpiredDate(0);
    $this->setDeterioration(0);
  }

  public function saveInDb()
  {
    $stm = $this->db->prepare("UPDATE locations SET name = :name, local_number = :localNumber, type = :type,
      region = :region, area = :area, borders_lake = :bordersLake, borders_sea = :bordersSea, map = 1, x = :x, y = :y,
      island = :island, deterioration = :deterioration, expired_date = :expiredDate, digging_slots = :diggingSlots
    WHERE id = :id");
    $stm->bindStr("name", $this->name);
    $stm->bindInt("localNumber", $this->local_number);
    $stm->bindInt("type", $this->type);
    $stm->bindInt("region", $this->region);
    $stm->bindInt("area", $this->area);
    $stm->bindInt("bordersLake", $this->borders_lake);
    $stm->bindInt("bordersSea", $this->borders_sea);
    $stm->bindInt("x", $this->x, true);
    $stm->bindInt("y", $this->y, true);
    $stm->bindInt("island", $this->island);
    $stm->bindInt("deterioration", $this->deterioration);
    $stm->bindInt("expiredDate", $this->expired_date);
    $stm->bindInt("diggingSlots", $this->digging_slots);
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }

  public static function getShipTypeArray()
  {
    static $shipTypes = [];
    if (empty($shipTypes)) {
      $db = Db::get();
      $stm = $db->prepare("SELECT vehicles FROM connecttypes WHERE name='sea_waterway'");
      $connect_sea = explode(",", $stm->executeScalar());
      $stm = $db->prepare("SELECT vehicles FROM connecttypes WHERE name='inland_waterway'");
      $connect_lake = explode(",", $stm->executeScalar());
      $shipTypes = array_values(array_unique(array_merge($connect_sea, $connect_lake)));
    }
    return $shipTypes;
  }

  public static function getUsedDiggingSlots($location)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT COUNT(*) FROM chars, projects
      WHERE chars.location = :locationId
        AND chars.project=projects.id
        AND projects.uses_digging_slot = 1");
    $stm->bindInt("locationId", $location);
    $workers = $stm->executeScalar();

    $stm = $db->prepare("SELECT SUM(CEIL(a.number / at.max_in_location))
      FROM animals a 
      INNER JOIN animal_types at ON at.id = a.type AND at.max_in_location != 0 
      INNER JOIN animal_domesticated_types adt ON adt.of_animal_type = at.id 
      WHERE a.location = :locationId");
    $stm->bindInt("locationId", $location);
    $domesticated_animals = $stm->executeScalar();

    $objectsTakingSlots = CObject::locatedIn($location)
      ->hasProperty("TakingUpGatheringSlots")
      ->findAll();

    $groups = [];
    foreach ($objectsTakingSlots as $object) {
      $takingGatheringSlotsProp = $object->getProperty("TakingUpGatheringSlots");
      $groupName = $takingGatheringSlotsProp["groupName"];
      if (!array_key_exists($groupName, $groups)) {
        $groups[$groupName] = [];
      }
      $groups[$groupName][] = (1 + count($groups[$groupName])) * $takingGatheringSlotsProp["slotsTaken"];
    }

    $totalCostPerGroup = Pipe::from($groups)->map(function($groupCosts) {
      return array_sum($groupCosts);
    })->toArray();
    $takenByObjects = array_sum($totalCostPerGroup);

    return $workers + $domesticated_animals + $takenByObjects;
  }

  public static function getMaxDiggingSlots($location)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT digging_slots FROM locations WHERE id = :id");
    $stm->bindInt("id", $location);
    $number = $stm->executeScalar();
    if (!$number) {
      $number = 0;
    }

    return $number;
  }
}

Location::staticInit();
