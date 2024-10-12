<?php
include_once(_LIB_LOC . "/func.expireobject.inc.php");
require_once(_LIB_LOC . "/func.grammar.inc.php");
require_once(_LIB_LOC . "/func.calcweight.inc.php");

class CObject
{
  private $id;
  private $description; // it's object data from db, has nothing to do with text description
  /** @var ObjectType */
  public $objecttype;

  public $wasDescChecked = false; // Used to differ between objects not yet tested and with no description at all. TEXT DESCRIPTION
  public $visibleDescription; // label for key etc.
  public $names;

  private static $objradioTypes; // todo should be removed in the future

  private $logger;
  /** @var Db */
  private $db;

  private function __construct($id)
  {
    $this->logger = Logger::getLogger(__CLASS__);
    $this->db = Db::get();

    $this->id = $id;
  }

  public static function loadById($objectId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM objects WHERE id = :objectId");
    $stm->bindInt("objectId", $objectId);
    $stm->execute();
    if ($fetchObj = $stm->fetchObject()) {
      $objectType = ObjectType::loadById($fetchObj->type);

      return self::loadFromFetchObject($fetchObj, $objectType);
    }
    throw new InvalidArgumentException("no object of id $objectId");
  }

  public static function loadFromFetchObject($fetchObj, ObjectType $objectType)
  {
    $object = new CObject($fetchObj->id);
    $object->description = $fetchObj;
    $object->objecttype = $objectType;
    return $object;
  }

  /**
   * @param array $objectIds
   *
   * @return CObject[] array of instantiated Object's in the same order as ids in $objectIds
   */
  public static function bulkLoadByIds(array $objectIds)
  {
    if (!Validation::isPositiveIntArray($objectIds)) {
      throw new InvalidArgumentException("some of object ids isn't a positive integer" . var_export($objectIds, true));
    }
    if (empty($objectIds)) {
      return [];
    }

    $db = Db::get();

    $stm = $db->prepareWithIntList("SELECT * FROM objects WHERE id IN (:ids)", [
      "ids" => $objectIds,
    ]);
    $stm->execute();
    $objectRows = $stm->fetchAll();
    $objectTypes = [];
    foreach ($objectRows as $row) {
      $objectTypes[$row->type] = true;
    }

    $fetchedTypes = ObjectTypeFinder::any()->ids(array_keys($objectTypes))->findAll();
    $objectTypes = [];
    foreach ($fetchedTypes as $type) {
      $objectTypes[$type->getId()] = $type;
    }

    $unorderedObjects = [];
    foreach ($objectRows as $objectRow) {
      $unorderedObjects[$objectRow->id] = self::loadFromFetchObject($objectRow, $objectTypes[$objectRow->type]);
    }

    $objects = [];
    foreach ($objectIds as $oId) { // guarantee the same order
      if (array_key_exists($oId, $unorderedObjects)) {
        $objects[] = $unorderedObjects[$oId];
      }
    }

    return $objects;
  }

  /**
   * @return string with translation tags for objects inside
   */
  public function printContainerContent()
  {
    $stm = $this->db->prepare("SELECT id FROM objects WHERE attached = :objectId AND type != :type ORDER BY weight DESC LIMIT :limit");
    $stm->bindInt("objectId", $this->id);
    $stm->bindInt("type", ObjectConstants::TYPE_INNER_LOCK);
    $stm->bindInt("limit", ObjectConstants::MAX_OBJECTS_IN_CONTAINER_SHOWN + 1);
    $stm->bindInt("limit", ObjectConstants::MAX_OBJECTS_IN_CONTAINER_SHOWN + 1);
    $stm->execute();
    $objectsInStorage = $stm->fetchScalars();

    $contentListString = Pipe::from(array_slice($objectsInStorage, 0, ObjectConstants::MAX_OBJECTS_IN_CONTAINER_SHOWN))->map(function($contentId) {
      return "<CANTR OBJNAME ID=" . $contentId . " TYPE=1>";
    })->toArray();


    $objNameList = implode(", ", $contentListString);

    if (count($objectsInStorage) > ObjectConstants::MAX_OBJECTS_IN_CONTAINER_SHOWN) {
      $objNameList .= "...";
    }

    return $objNameList;
  }

  /**
   * @return bool true if machine is being used (or disassembled or ocuppied in any other way)
   */
  public function isInUse()
  {
    return $this->getProjectWorkedOn() > 0;
  }

  public function getProjectWorkedOn()
  {
    if (is_numeric($this->getSpecifics())) {
      $stm = $this->db->prepare(
        "SELECT
        (SELECT COUNT(*) FROM machines WHERE type = :type1)
        +
        (SELECT COUNT(*) FROM objecttypes WHERE id = :type2 AND rules LIKE '%energy:%')
      AS count
      ");
      $stm->bindInt("type1", $this->getType());
      $stm->bindInt("type2", $this->getType());
      $projTypesCount = $stm->executeScalar();
      // query which gives number of all work and rest projects possible for that type of object - to exclude things like radio receivers
      if ($projTypesCount > 0) { // if any project for that type of object can potentially exist
        $stm = $this->db->prepare("SELECT id FROM projects WHERE id = :id LIMIT 1");
        $stm->bindInt("id", $this->getSpecifics());
        $projectId = $stm->executeScalar();
        if ($projectId > 0) {
          return $projectId;
        }
      }
    } elseif ($this->getType() == ObjectConstants::TYPE_COIN_PRESS) {
      $stm = $this->db->prepare("SELECT id FROM projects
        WHERE type = :type AND subtype = :subtype LIMIT 1");
      $stm->bindInt("type", ProjectConstants::TYPE_PRODUCING_COINS);
      $stm->bindInt("subtype", $this->getId());
      $projectId = $stm->executeScalar();
      if ($projectId > 0) {
        return $projectId;
      }
    }

    // check if it's being disassembled
    $stm = $this->db->prepareWithIntList("SELECT id FROM projects WHERE
       type IN (:types) AND subtype = :subtype LIMIT 1", [
      "types" => [ProjectConstants::TYPE_DISASSEMBLING, ProjectConstants::TYPE_FIXING_DAMAGED],
    ]);
    $stm->bindInt("subtype", $this->getId());
    $disassemblingProjectId = $stm->executeScalar();
    return $disassemblingProjectId;
  }

  public function isReorderable()
  {
    $reorderableTypeNames = ["bookcase", "wood display case", "wheeled display case", "stone pedestal case", "marble pedestal case"];
    return in_array($this->getName(), $reorderableTypeNames) || $this->hasProperty("NoteStorage"); // TODO temporarily just for note related storages to get feedback from players
  }

  public function getDescription($oid)
  {
    $this->loadDescription($oid);
    if (!empty($this->visibleDescription)) {
      return ' - <span class="txt-label">' . $this->visibleDescription . '</span>';
    } else {
      return "";
    }
  }

  private function loadDescription($oid)
  {
    if (!$this->wasDescChecked) {
      $this->wasDescChecked = true;
      $this->visibleDescription = Descriptions::getDescription($oid, Descriptions::TYPE_OBJECT);
    }
  }

  /**
   * @return null|CObject parent object or null if it doesn't have any parent
   * @throws IllegalStateException when parent id is > 0 but parent object doesn't exist in database
   */
  public function getParent()
  {
    if ($this->getAttached() > 0) { // parent exists
      try {
        $parent = CObject::loadById($this->getAttached());
        if ($parent->getExpiredDate() > 0) {
          throw new IllegalStateException("parent " . $this->getAttached() . " of object " . $this->getId() . " has already expired");
        }
        return $parent;
      } catch (InvalidArgumentException $e) {
        throw new IllegalStateException("parent " . $this->getAttached() . " of object " . $this->getId() . " doesn't exist");
      }
    }
    return null; // it's not in storage
  }

  public function getRoot()
  {
    $current = $this;
    while ($current->getAttached() > 0) {
      $current = $current->getParent();
      if ($current->getAttached() == $current->getId()) {
        throw new IllegalStateException("object " . $current->getId() . " is own root");
      }
    }
    return $current;
  }

  /**
   * Returns list of storages in a hierarchy, where first element is root storage (in the inventory or on the ground), second one is its child and so on.
   * @throws IllegalStateException when some object in hierarchy doesn't exist (e.g. has expired)
   *@return CObject[] list of all storages in the hierarchy, starting with root object, empty if no storage
   */
  public function getStorageHierarchy()
  {
    $storageHierarchy = [$this];
    /** @var $storageHierarchy CObject[] */
    while ($storageHierarchy[0]->getAttached() > 0) {
      array_unshift($storageHierarchy, $storageHierarchy[0]->getParent());
    }
    array_pop($storageHierarchy);

    return $storageHierarchy;
  }


  /**
   * @param Character $char character whose ability to access $object is checked
   * @param bool $strict true when it's necessary to be able to retrieve from inventory, not just look inside
   * @param bool $inventoryOnly true when root storage cannot be located on the ground
   * @return bool false if object is not in any storage or this object cannot be manipulated because of inner lock restriction
   * @throws IllegalStateException any storage in the hierarchy doesn't exist
   * @throws NoKeyToInnerLockException any of storages is not accessible for $char because of lock restriction
   * @throws TooFarAwayException $storage is not accessible in inventory/within reach (based on $strict param)
   * @throws ObjectSealedException storage object is sealed and this restriction cannot be ignored
   */
  public function tryAccessibilityInStorage(Character $char, $strict = true, $inventoryOnly = false)
  {
    if ($this->getAttached() <= 0) { // not in storage = we want it to be false
      throw new IllegalStateException("it's not in storage, so it shouldn't ever be called");
    }

    $storage = $this->getParent();
    $storage->tryAccessibilityOfStorageContents($char, $strict, $inventoryOnly);
  }

  /**
   * @param Character $char
   * @param bool $strict
   * @param bool $inventoryOnly
   * @throws IllegalStateException
   * @throws NoKeyToInnerLockException
   * @throws ObjectSealedException
   * @throws TooFarAwayException
   */
  public function tryAccessibilityOfStorageContents(Character $char, $strict = true, $inventoryOnly = false)
  {
    $storage = $this;
    while ($storage != null) {
      if ($strict) {
        $canIgnoreRestrictons = $storage->hasProperty("IgnoreRetrievingRestrictions");
      } else {
        $canIgnoreRestrictons = $storage->hasProperty("IgnoreLookingRestrictions");
      }

      if (!$canIgnoreRestrictons) { // if lock restriction cannot be omitted
        $storageLock = KeyLock::loadByObjectId($storage->getId());
        if (!$storageLock->canAccess($char->getId())) { // no access to storage
          throw new NoKeyToInnerLockException();
        }

        if ($this->hasProperty("Sealable")) {
          $sealsManager = new SealsManager($this);
          if (count($sealsManager->getAll(true)) > 0) { // there are REAL seals
            throw new ObjectSealedException("it's sealed", $storage);
          }
        }
      }
      $parent = $storage->getParent();
      if ($parent == null) {
        break;
      }
      $storage = $parent;
    }
    if ($inventoryOnly) {
      $isRootStorageAccessible = $char->hasInInventory($storage); // root storage in inv
    } else {
      $isRootStorageAccessible = $char->hasWithinReach($storage); // root storage in inv or on the ground
    }

    if (!$isRootStorageAccessible) {
      throw new TooFarAwayException("not accessible for this character");
    }
  }

  public function areStorageContentsAccessible(Character $char, $strict = true, $inventoryOnly = false)
  {
    try {
      $this->tryAccessibilityOfStorageContents($char, $strict, $inventoryOnly);
    } catch (Exception $e) {
      return false;
    }
    return true;
  }

  /**
   * The same as tryAccessibilityInStorage() but returns true/false instead of throwing any exception
   * @param Character $char character whose ability to access $object is checked
   * @param bool $strict true when it's necessary to be able to retrieve from inventory, not just look inside
   * @param bool $inventoryOnly true when root storage cannot be located on the ground
   * @return bool true if it's really accessible, false otherwise
   */
  public function isAccessibleInStorage(Character $char, $strict = true, $inventoryOnly = false)
  {
    try {
      $this->tryAccessibilityInStorage($char, $strict, $inventoryOnly);
    } catch (Exception $e) {
      return false;
    }
    return true;
  }

  public static function InitRadioTypes()
  {

    $types = array('receivers' => '%;radio_receiver%', 'repeaters' => '%;rebroadcast=%', 'transmiters' => '%;broadcast%');
    $db = Db::get();
    $objradioTypes = new StdClass();
    foreach ($types as $type => $rule) {
      $ids = array();
      $stm = $db->query("SELECT id, rules FROM objecttypes WHERE CONCAT(';', rules, ';') LIKE '$rule'");
      $stm->execute();
      while (list ($id, $rules) = $stm->fetch(PDO::FETCH_NUM)) {
        $ids [] = $id;
        $objradioTypes->typeid [$id] = $type;
        if ($type == 'repeaters') {
          $rules = Parser::rulesToArray($rules, ";=");
          $objradioTypes->ranges [$id] = $rules['rebroadcast'];
        }
      }
      $objradioTypes->$type = implode(', ', $ids);
    }
    return $objradioTypes;
  }

  public static function RadioTypeToInt($string)
  {
    if ($string == 'receivers') return 0;
    if ($string == 'repeaters') return 1;
    if ($string == 'transmiters') return 2;
  }

  public static function GetRadioTypes()
  {
    if (self::$objradioTypes === null) {
      self::$objradioTypes = CObject::InitRadioTypes();
    }
    return self::$objradioTypes;
  }

  public static function getRawIdFromName($name)
  {
    return ObjectHandler::getRawIdFromName($name);
  }

  public static function getRawNameFromId($id)
  {
    return ObjectHandler::getRawNameFromId($id);
  }

  public function saveInDb()
  {
    $stm = $this->db->prepare("UPDATE objects SET location = :locationId, person = :charId, attached = :storageId,
    type = :type, typeid = :typeid, weight = :weight, setting = :setting, specifics = :specifics,
    deterioration = :deterioration, expired_date = :expiredDate,
    ordering = :ordering
    WHERE id = :id");
    $stm->bindInt("locationId", $this->getLocation());
    $stm->bindInt("charId", $this->getPerson());
    $stm->bindInt("storageId", $this->getAttached());
    $stm->bindInt("type", $this->getType());
    $stm->bindInt("typeid", $this->getTypeid());
    $stm->bindInt("weight", $this->getWeight());
    $stm->bindInt("setting", $this->getSetting());
    $stm->bindStr("specifics", $this->getSpecifics());
    $stm->bindFloat("deterioration", $this->getDeterioration());
    $stm->bindInt("expiredDate", $this->getExpiredDate());
    $stm->bindInt("ordering", $this->getOrdering());
    $stm->bindInt("id", $this->getId());
    $stm->execute();
  }

  public function getId()
  {
    return $this->id ? $this->id : $this->description->id;
  }

  public function getLocation()
  {
    return $this->description->location;
  }

  public function setLocation($location)
  {
    $this->description->location = intval($location);
  }

  public function getPerson()
  {
    return $this->description->person;
  }

  public function setPerson($person)
  {
    $this->description->person = intval($person);
  }

  public function getAttached()
  {
    return $this->description->attached;
  }

  public function setAttached($attached)
  {
    $this->description->attached = intval($attached);
  }

  public function getName()
  {
    return $this->objecttype->getName();
  }

  public function getUniqueName()
  {
    return $this->objecttype->getUniqueName();
  }

  public function getSubtable()
  {
    return $this->objecttype->getSubtable();
  }

  private function moveContentsOutside()
  {
    $objectsInside = CObject::storedIn($this)->exceptType(ObjectConstants::TYPE_INNER_LOCK)->findAll();

    foreach ($objectsInside as $obj) {
      if ($obj->getType() == ObjectConstants::TYPE_RAW) {
        if ($this->getLocation() > 0) {
          ObjectHandler::rawToLocation($this->getLocation(), $obj->getTypeid(), $obj->getAmount());
        } elseif ($this->getPerson() > 0) {
          ObjectHandler::rawToPerson($this->getPerson(), $obj->getTypeid(), $obj->getAmount());
        } else {
          ObjectHandler::rawToContainer($this->getAttached(), $obj->getTypeid(), $obj->getAmount());
        }
        $obj->remove();
      } elseif ($obj->isQuantity()) { // coin
        if ($this->getLocation() > 0) {
          ObjectHandler::coinsToLocation($this->getLocation(), $obj->getType(), $obj->getSpecifics(), $obj->getAmount());
        } elseif ($this->getPerson() > 0) {
          ObjectHandler::coinsToPerson($this->getPerson(), $obj->getType(), $obj->getSpecifics(), $obj->getAmount());
        } else {
          ObjectHandler::coinsToContainer($this->getAttached(), $obj->getType(), $obj->getSpecifics(), $obj->getAmount());
        }
        $obj->remove();
      } else {
        $obj->setLocation($this->getLocation());
        $obj->setPerson($this->getPerson());
        $obj->setAttached($this->getAttached());
      }
      $obj->saveInDb();
    }
  }

  public function isEmpty()
  {
    $anyObjectInside = CObject::storedIn($this)->exists();
    return !$anyObjectInside;
  }

  public function hasNoFixedContents()
  {
    $anyFixedObjectInside = CObject::storedIn($this)->setting(ObjectConstants::SETTING_FIXED)->exists();
    return !$anyFixedObjectInside;
  }

  /**
   * Removes object and moves all contents outside of container (if it's a container)
   */
  public function remove()
  {
    $this->moveContentsOutside();
    $this->annihilate();
  }

  /**
   * Completely removes object without moving anything outside
   */
  public function annihilate()
  {
    $this->description->location *= (-1);
    $this->description->person *= (-1);
    $this->description->attached *= (-1);
    $radioTypes = self::GetRadioTypes()->typeid;
    if (array_key_exists($this->getType(), $radioTypes)) { // it's a radio, must be removed from `radios`
      $stm = $this->db->prepare("DELETE FROM radios WHERE item = :item");
      $stm->bindInt("item", $this->getId());
      $stm->execute();
    }

    $this->setExpiredDate(GameDate::NOW()->getIntInDbFormat());
  }

  public function getProperty($name)
  {
    if ($name == "Any") {
      return true;
    }

    $stm = $this->db->prepare("SELECT details FROM obj_properties
      WHERE objecttype_id = :objectTypeId AND property_type = :propertyName");
    $stm->bindInt("objectTypeId", $this->getType());
    $stm->bindStr("propertyName", $name);
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
    $stm = $this->db->prepare("SELECT details FROM obj_properties
      WHERE objecttype_id = :objectTypeId AND property_type = :propertyName");
    $stm->bindInt("objectTypeId", $this->getType());
    $stm->bindStr("propertyName", $name);
    $matchingProps = $stm->executeScalar();
    return $matchingProps !== null;
  }

  public function getPropertyNames()
  {
    $stm = $this->db->prepare("SELECT property_type FROM obj_properties WHERE objecttype_id = :objectTypeId");
    $stm->bindInt("objectTypeId", $this->getType());
    $stm->execute();
    $allProperties = $stm->fetchScalars();
    $allProperties[] = "Any";
    return $allProperties;
  }

  public function hasAccessToAction($actionName)
  {
    import_lib("func.rules.inc.php"); // TODO! Buggy, inefficient implementation
    return objectHaveAccessToAction($this->getId(), $actionName);
  }

  public function getType()
  {
    return $this->description->type;
  }

  /**
   * @deprecated it's no longer possible to change object's type
   */
  public function setType($type)
  {
    throw new IllegalStateException("It's no longer possible to change object's type");
  }

  public function getTypeid()
  {
    return $this->description->typeid;
  }

  public function setTypeid($typeid)
  {
    $this->description->typeid = intval($typeid);
  }

  public function getSetting()
  {
    return $this->description->setting;
  }

  public function isQuantity()
  {
    return $this->getSetting() == ObjectConstants::SETTING_QUANTITY;
  }

  public function getSpecifics()
  {
    return $this->description->specifics;
  }

  public function setSpecifics($specifics)
  {
    $this->description->specifics = $specifics;
  }

  public function getRepairRate()
  {
    return $this->objecttype->getRepairRate();
  }

  public function getDeterPerDay()
  {
    return $this->objecttype->getDeterRatePerDay();
  }

  public function getDeterRatePerUse()
  {
    return $this->objecttype->getDeterRatePerUse();
  }

  public function isDeteriorationVisible()
  {
    return $this->objecttype->isDeteriorationVisible();
  }

  public function getWeight()
  {
    return $this->description->weight;
  }

  public function getDeterioration()
  {
    return (float)$this->description->deterioration;
  }

  public function alterDeterioration($byValue)
  {
    $this->description->deterioration += $byValue;
  }

  public function getExpiredDate()
  {
    return $this->description->expired_date;
  }

  public function setExpiredDate($expiredDate)
  {
    $this->description->expired_date = $expiredDate;
  }

  public function getAmount()
  {
    if ($this->getUnitWeight() > 0) {
      return ($this->getWeight() / $this->getUnitWeight());
    }
    return 1;
  }

  public function getUnitWeight()
  {
    if ($this->isQuantity()) {
      if ($this->getType() == ObjectConstants::TYPE_RAW) {
        return 1; // raw
      }
      return ObjectConstants::WEIGHT_COIN; // coin
    }
    return $this->getWeight(); // normal
  }

  public function getRules()
  {
    return $this->objecttype->getRules();
  }

  public function setWeight($weight)
  {
    $this->description->weight = $weight;
  }

  public function getOrdering()
  {
    return $this->description->ordering;
  }

  public function setOrdering($ordering)
  {
    $this->description->ordering = intval($ordering);
  }

  public function getObjectCategory()
  {
    return $this->objecttype->getObjectCategory();
  }

  public function getObjectType()
  {
    return $this->objecttype;
  }

  public function getBuildRequirements()
  {
    return $this->objecttype->getBuildRequirements();
  }

  public function getBuildConditions()
  {
    return $this->objecttype->getBuildConditions();
  }

  public function getTypeWeight()
  {
    return $this->objecttype->getUnitWeight();
  }

  public function getProductionSkill()
  {
    return $this->objecttype->getProductionSkill();
  }

  // ********************
  //    OBJECT SEARCH
  // ********************

  public static function locatedIn($location)
  {
    if ($location instanceof Location) {
      $locId = $location->getId();
    } elseif (Validation::isPositiveInt($location)) {
      $locId = intval($location);
    } else {
      throw new InvalidArgumentException("Invalid location" . var_export($location, true) . " for an object searched for");
    }

    return ObjectFinder::placedIn($locId, 0, 0);
  }

  public static function inInventoryOf($char)
  {
    if ($char instanceof Character) {
      $charId = $char->getId();
    } elseif (Validation::isPositiveInt($char)) {
      $charId = intval($char);
    } else {
      throw new InvalidArgumentException("Invalid char=" . var_export($char, true) . " for an object searched for");
    }

    return ObjectFinder::placedIn(0, $charId, 0);
  }

  public static function storedIn($container)
  {
    if ($container instanceof CObject) {
      $containerId = $container->getId();
    } elseif (Validation::isPositiveInt($container)) {
      $containerId = intval($container);
    } else {
      throw new InvalidArgumentException("Invalid container=" . var_export($container, true) . " for an object searched for");
    }

    return ObjectFinder::placedIn(0, 0, $containerId);
  }

}
