<?php

class ObjectHandler
{

  /** @var Logger */
  private static $logger;

  public static function staticInit()
  {
    self::$logger = Logger::getLogger(__CLASS__, Logger::NOTICE);
  }

  /*
  * Getting amount of raw pile
  */

  public static function getRawFromLocation($location, $rawtype)
  {
    return self::getRaw($location, 0, 0, $rawtype);
  }

  public static function getRawFromPerson($person, $rawtype)
  {
    return self::getRaw(0, $person, 0, $rawtype);
  }

  public static function getRawFromContainer($attached, $rawtype)
  {
    return self::getRaw(0, 0, $attached, $rawtype);
  }

  private static function getRaw($location, $person, $attached, $rawtype)
  {
    if (!is_numeric($location) || !is_numeric($person) || !is_numeric($attached)) {
      throw new Exception("location, person, attached is not a number");
    }
    if (!is_numeric($rawtype)) {
      throw new Exception("rawtype is not a number");
    }

    $db = Db::get();
    $stm = $db->prepare("SELECT weight FROM objects WHERE location = :locationId AND
      person = :charId AND attached = :storageId AND type = 2 AND typeid = :rawType");
    $stm->bindInt("locationId", $location);
    $stm->bindInt("charId", $person);
    $stm->bindInt("storageId", $attached);
    $stm->bindInt("rawType", $rawtype);
    $amount = $stm->executeScalar();
    if ($amount == null) {
      $amount = 0;
    }
    return $amount;
  }

  /*
  * Altering raw piles
  */

  public static function rawToLocation($location, $rawtype, $amount)
  {
    return self::rawTo($location, 0, 0, $rawtype, $amount);
  }

  public static function rawToPerson($person, $rawtype, $amount)
  {
    return self::rawTo(0, $person, 0, $rawtype, $amount);
  }

  public static function rawToContainer($attached, $rawtype, $amount)
  {
    $isCorrect = self::rawTo(0, 0, $attached, $rawtype, $amount);
    $db = Db::get();
    if ($isCorrect) {
      do {
        $stm = $db->prepare("UPDATE objects SET weight = weight + :weightChange WHERE id = :storageId");
        $stm->bindInt("weightChange", $amount);
        $stm->bindInt("storageId", $attached);
        $stm->execute();
        $stm = $db->prepare("SELECT attached FROM objects WHERE id = :storageId");
        $stm->bindInt("storageId", $attached);
        $attached = $stm->executeScalar();
      } while ($attached > 0);
    }
    return $isCorrect;
  }

  private static function rawTo($location, $person, $attached, $rawtype, $amount)
  {
    if (!is_numeric($location) || !is_numeric($person) || !is_numeric($attached)) {
      throw new InvalidArgumentException("location, person or attached is not a number");
    }
    if (!is_numeric($rawtype)) {
      throw new InvalidArgumentException("rawtype is not a number");
    }
    if (!is_numeric($amount)) {
      throw new InvalidArgumentException("amount is not a number");
    }

    if ($amount == 0) {
      return true;
    }

    $expiredDate = GameDate::NOW()->getIntInDbFormat();
    $db = Db::get();
    $stm = $db->prepare("UPDATE objects SET
        location = IF (weight > -1 * :amount1, location, -location),
        person =   IF (weight > -1 * :amount2,   person,   -person),
        attached = IF (weight > -1 * :amount3, attached, -attached),
        expired_date = IF (weight > -1 * :amount4, 0, :expiredDate),
        weight =  IF (weight > -1 * :amount5,  weight + :amount6, weight)
      WHERE type = 2 AND typeid = :rawType AND (weight >= -1 * :amount7) AND expired_date = 0 AND
        location = :locationId AND person = :charId AND attached = :storageId LIMIT 1");
    $stm->bindInt("amount1", $amount);
    $stm->bindInt("amount2", $amount);
    $stm->bindInt("amount3", $amount);
    $stm->bindInt("amount4", $amount);
    $stm->bindInt("expiredDate", $expiredDate);
    $stm->bindInt("amount5", $amount);
    $stm->bindInt("amount6", $amount);
    $stm->bindInt("amount7", $amount);
    $stm->bindInt("rawType", $rawtype);
    $stm->bindInt("locationId", $location);
    $stm->bindInt("charId", $person);
    $stm->bindInt("storageId", $attached);
    $stm->execute();

    // query that performs pile weight change if pile already exists and
    $reallyChanged = ($stm->rowCount() > 0);
    if ($reallyChanged) {
      return true;
    }

    if ($amount > 0) { // no pile and going to add stuff, so it should be created
      $stm = $db->prepare("INSERT INTO objects (location, person, attached, type, typeid, weight, setting)
        VALUES (:locationId, :charId, :storageId, 2, :rawType, :amount, :setting)");
      $stm->bindInt("locationId", $location);
      $stm->bindInt("charId", $person);
      $stm->bindInt("storageId", $attached);
      $stm->bindInt("rawType", $rawtype);
      $stm->bindInt("amount", $amount);
      $stm->bindInt("setting", ObjectConstants::SETTING_QUANTITY);
      $stm->execute();

      return true;
    }

    self::$logger->error("Removing more than there is. Trying to alter by $amount, there is $amount (charId: " . $GLOBALS['character'] . ", page: " . $GLOBALS['page'] . ")");
    return false;
  }

  public static function coinsToLocation($location, $type, $coinName, $number)
  {
    return self::coinsTo($location, 0, 0, $type, $coinName, $number * ObjectConstants::WEIGHT_COIN);
  }

  public static function coinsToPerson($person, $type, $coinName, $number)
  {
    return self::coinsTo(0, $person, 0, $type, $coinName, $number * ObjectConstants::WEIGHT_COIN);
  }

  public static function coinsToContainer($attached, $type, $coinName, $number)
  {
    $isCorrect = self::coinsTo(0, 0, $attached, $type, $coinName, $number * ObjectConstants::WEIGHT_COIN);
    $db = Db::get();
    if ($isCorrect) {
      $stm = $db->prepare("UPDATE objects SET weight = weight + :weight WHERE id = :storageId");
      $stm->bindInt("weight", $number * ObjectConstants::WEIGHT_COIN);
      $stm->bindInt("storageId", $attached);
      $stm->execute();
    }
    return $isCorrect;
  }

  private static function coinsTo($location, $person, $attached, $type, $coinName, $amount)
  {
    if (!Validation::isInt($location) || !Validation::isInt($person) || !Validation::isInt($attached)) {
      throw new InvalidArgumentException("location, person or attached is not int");
    }
    if (!Validation::isPositiveInt($location) &&
      !Validation::isPositiveInt($person) &&
      !Validation::isPositiveInt($attached)
    ) {
      throw new InvalidArgumentException("inexistent place");
    }
    if (!is_numeric($amount)) {
      throw new InvalidArgumentException("amount is not int");
    }
    if (!in_array($type, ObjectConstants::$TYPES_COINS)) {
      throw new InvalidArgumentException("type is not a coin");
    }
    if (!Validation::isInt($amount / ObjectConstants::WEIGHT_COIN)) {
      throw new InvalidArgumentException("number of coins is not int");
    }

    $db = Db::get();
    $stm = $db->prepare("SELECT IF(type = :coinType1, typeid, id) AS press_id FROM objects
      WHERE type IN (:pressType, :coinType2) AND specifics = :specifics");
    $stm->bindInt("coinType1", $type);
    $stm->bindInt("coinType2", $type);
    $stm->bindInt("pressType", ObjectConstants::TYPE_COIN_PRESS);
    $stm->bindStr("specifics", $coinName);
    $coinPressId = $stm->executeScalar();
    if (!$coinPressId) {
      $coinPressId = 0;
    }
    if ($amount == 0) {
      self::$logger->notice("Suspicious change. Trying to alter $type '$coinName' by $amount (charId: " . $GLOBALS['character'] . ", page: " . $GLOBALS['page'] . ")");
      return true;
    }

    $db = Db::get();
    $expiredDate = GameDate::NOW()->getIntInDbFormat();
    $stm = $db->prepare("UPDATE objects SET
        location = IF (weight > -1 * :amount1, location, -location),
        person =   IF (weight > -1 * :amount2,   person,   -person),
        attached = IF (weight > -1 * :amount3, attached, -attached),
        expired_date = IF (weight > -1 * :amount4, 0, :expiredDate),
        weight =  IF (weight > -1 * :amount5,  weight + :amount6, weight)
      WHERE type = :type AND specifics = :specifics AND
        (weight >= -1 * :amount7) AND expired_date = 0 AND
        location = :locationId AND person = :charId AND attached = :storageId LIMIT 1");
    $stm->bindInt("amount1", $amount);
    $stm->bindInt("amount2", $amount);
    $stm->bindInt("amount3", $amount);
    $stm->bindInt("amount4", $amount);
    $stm->bindInt("expiredDate", $expiredDate);
    $stm->bindInt("amount5", $amount);
    $stm->bindInt("amount6", $amount);
    $stm->bindInt("amount7", $amount);
    $stm->bindInt("type", $type);
    $stm->bindStr("specifics", $coinName);
    $stm->bindInt("locationId", $location);
    $stm->bindInt("charId", $person);
    $stm->bindInt("storageId", $attached);
    $stm->execute();

    // query that performs pile weight change if pile already exists or expires it
    $reallyChanged = ($stm->rowCount() > 0);
    if ($reallyChanged) {
      return true;
    }

    if ($amount > 0) { // no coins stack - create one
      $stm = $db->prepare("INSERT INTO objects (location, person, attached, type, typeid, weight, setting, specifics)
        VALUES (:locationId, :charId, :storageId, :type, :coinPressId, :amount, :setting, :specifics)");
      $stm->bindInt("locationId", $location);
      $stm->bindInt("charId", $person);
      $stm->bindInt("storageId", $attached);
      $stm->bindInt("type", $type);
      $stm->bindInt("coinPressId", $coinPressId);
      $stm->bindInt("amount", $amount);
      $stm->bindInt("setting", ObjectConstants::SETTING_QUANTITY);
      $stm->bindStr("specifics", $coinName);
      $stm->execute();

      return true;
    }
    self::$logger->error("Removing more coins than there are. Trying to alter by $amount (charId: " . $GLOBALS['character'] . ", page: " . $GLOBALS['page'] . ")");
    return false;
  }

  public static function getRawIdFromName($name)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM rawtypes WHERE name = :name");
    $stm->bindStr("name", $name);
    return $stm->executeScalar();
  }

  public static function getRawNameFromId($id)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT name FROM rawtypes WHERE id = :id");
    $stm->bindInt("id", $id);
    return $stm->executeScalar();
  }

  public static function getObjectType($object_id)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT type FROM objects WHERE id = :objectId");
    $stm->bindInt("objectId", $object_id);
    return $stm->executeScalar();
  }

  /**
   * Returns boolean if object is in location or in inventory of sb in location. (containers are ignored)
   */
  public static function isObjectInLocation($objectId, $location)
  {
    $objectsNear = self::getObjectArrayInLocation(array($objectId), $location); // array of one object
    return $objectsNear[$objectId];
  }

  /**
   * @param array $objectIds
   * @param $location
   * @return array list of objects which are either in location or in inventory of character in location
   */
  public static function getObjectArrayInLocation(array $objectIds, $location)
  {
    if (count($objectIds) == 0) {
      return array();
    }
    foreach ($objectIds as $objectId) {
      if (!Validation::isPositiveInt($objectId)) {
        throw new InvalidArgumentException("One of ids: $objectId is not an int");
      }
    }
    if (!Validation::isPositiveInt($location)) {
      throw new InvalidArgumentException("location $location must be > 0");
    }
    $objectsNear = array_flip($objectIds);
    foreach ($objectsNear as &$isNear) { // set all objects as not near
      $isNear = false;
    }
    $db = Db::get();
    $stm = $db->prepareWithList("SELECT id, location, person FROM objects WHERE id IN (:ids)", [
      "ids" => $objectIds,
    ]);
    $stm->execute();
    $checkForPerson = array();
    foreach ($stm->fetchAll() as $obj) {
      if ($obj->location == $location) { // if object is on the ground in specified location
        $objectsNear[$obj->id] = true;
      } elseif ($obj->person > 0) { // if held in inventory then will need additional check
        $checkForPerson[] = $obj->id;
      }
    }
    if (count($checkForPerson) > 0) { // at least one in inventory
      $stm = $db->prepareWithList("SELECT o.id, c.location FROM chars c
        INNER JOIN objects o ON o.person = c.id WHERE o.id IN (:ids)", [
        "ids" => $checkForPerson,
      ]);
      $stm->execute();
      foreach ($stm->fetchAll() as $objHeld) { // if char holding object is in the same location
        $objectsNear[$objHeld->id] = $objHeld->location == $location;
      }
    }
    return $objectsNear;
  }

  public static function getObjectArrayByNameInInventory(array $objectNames, $person)
  {
    $objectsInInventory = array_flip($objectNames);
    foreach ($objectsInInventory as &$isInInv) {
      $isInInv = false;
    }

    $db = Db::get();
    if (!empty($objectNames)) {
      $stm = $db->prepareWithList("SELECT ot.name FROM objecttypes ot
      INNER JOIN objects o ON o.type = ot.id WHERE o.person = :charId AND ot.name IN (:names) GROUP BY ot.name", [
        "names" => $objectNames,
      ]);
      $stm->bindInt("charId", $person);
      $stm->execute();
      foreach ($stm->fetchScalars() as $objectName) {
        $objectsInInventory[$objectName] = true;
      }
    }
    return $objectsInInventory;
  }

  public static function hasObjectByName($objectName, $person)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM objecttypes WHERE name = :name");
    $stm->bindStr("name", $objectName);
    $stm->execute();
    $objectTypes = array();
    foreach ($stm->fetchScalars() as $otName) {
      $objectTypes[] = $otName;
    }
    if (!empty($objectTypes)) {
      $stm = $db->prepareWithList("SELECT id FROM objects WHERE person = :charId AND type IN (:types) LIMIT 1", [
        "types" => $objectTypes,
      ]);
      $stm->bindInt("charId", $person);
      $isObject = $stm->executeScalar();
      return ($isObject != null);
    }
    return false;
  }

  public static function getBuildObjectNameTag($objectType)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT unique_name FROM objecttypes WHERE id = :id");
    $stm->bindInt("id", $objectType);
    $uniqueName = $stm->executeScalar();
    $stm = $db->prepare("SELECT name FROM texts WHERE name = :name LIMIT 1");
    $stm->bindStr("name", "item_{$uniqueName}_b");
    $hasBTag = $stm->executeScalar();
    return "<CANTR REPLACE NAME=item_{$uniqueName}_" . ($hasBTag ? "b" : "o") . ">";
  }

  public static function getObjectTypeTagByName($objTypeName)
  {
    $objectTag = "item_" . str_replace(" ", "_", $objTypeName) . "_o";
    $db = Db::get();
    $stm = $db->prepare("SELECT 1 FROM texts WHERE name = :name LIMIT 1");
    $stm->bindStr("name", $objectTag);
    $exists = $stm->executeScalar();
    return ($exists) ? "<CANTR REPLACE NAME=$objectTag>" : $objTypeName;
  }

  public static function getIdsByUniqueNames(array $uniqueNames)
  {
    $db = Db::get();
    $stm = $db->prepareWithList("SELECT id FROM objecttypes WHERE unique_name IN (:uniqueNames)", [
      "uniqueNames" => $uniqueNames,
    ]);
    $stm->execute();
    return $stm->fetchScalars();
  }
}

ObjectHandler::staticInit();
