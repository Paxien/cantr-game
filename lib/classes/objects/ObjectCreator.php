<?php

/**
 * Class ObjectCreator is responsible for creating new instances of objects in the database.
 */
class ObjectCreator
{

  /* mandatory fields */
  private $location;
  private $person;
  private $attached;
  private $type;
  private $setting;
  private $weight;

  /* optional fields */
  private $typeid = 0;
  private $specifics = "";

  /** @var Db */
  private $db;

  public static function inLocation($location, $type, $setting, $weight)
  {
    if ($location instanceof Location) {
      $location = $location->getId();
    }
    if (!Validation::isPositiveInt($location)) {
      throw new InvalidArgumentException("$location is not a valid location");
    }

    return self::newInstance($location, 0, 0, $type, $setting, $weight);
  }

  public static function inInventory($char, $type, $setting, $weight)
  {
    if ($char instanceof Character) {
      $char = $char->getId();
    }
    if (!Validation::isPositiveInt($char)) {
      throw new InvalidArgumentException("$char is not a valid char");
    }

    return self::newInstance(0, $char, 0, $type, $setting, $weight);
  }

  public static function inStorage($storage, $type, $setting, $weight)
  {
    if ($storage instanceof CObject) {
      $storage = $storage->getId();
    }
    if (!Validation::isPositiveInt($storage)) {
      throw new InvalidArgumentException("$storage is not a valid storage");
    }

    return self::newInstance(0, 0, $storage, $type, $setting, $weight);
  }

  private static function newInstance($location, $person, $attached, $type, $setting, $weight)
  {
    if (!Validation::isPositiveInt($type)) {
      throw new InvalidArgumentException("$type is not a valid type");
    }
    if (!Validation::isNonNegativeInt($weight)) {
      throw new InvalidArgumentException("$weight must be non negative int");
    }
    if (!in_array($setting, [ObjectConstants::SETTING_PORTABLE, ObjectConstants::SETTING_QUANTITY,
      ObjectConstants::SETTING_FIXED, ObjectConstants::SETTING_HEAVY])) {
      throw new InvalidArgumentException("$setting is not a valid setting");
    }

    $creator = new self();
    $creator->location = $location;
    $creator->person = $person;
    $creator->attached = $attached;
    $creator->type = $type;
    $creator->setting = $setting;
    $creator->weight = $weight;
    $creator->db = Db::get();

    return $creator;
  }

  public function typeid($typeid)
  {
    if (!Validation::isNonNegativeInt($typeid)) {
      throw new InvalidArgumentException("$typeid must be non negative int");
    }
    $this->typeid = $typeid;
    return $this;
  }

  public function specifics($specifics)
  {
    $this->specifics = $specifics;
    return $this;
  }

  public function create()
  {
    $stm = $this->db->prepare("INSERT INTO `objects` (`location`, `person`, `attached`, `type`, `typeid`, `weight`, `specifics`, `setting`)
      VALUES (:locationId, :charId, :storageId, :type, :typeid, :weight, :specifics, :setting)");
    $stm->bindInt("locationId", $this->location);
    $stm->bindInt("charId", $this->person);
    $stm->bindInt("storageId", $this->attached);
    $stm->bindInt("type", $this->type);
    $stm->bindInt("typeid", $this->typeid);
    $stm->bindInt("weight", $this->weight);
    $stm->bindStr("specifics", $this->specifics);
    $stm->bindInt("setting", $this->setting);
    $stm->execute();

    $objectId = $this->db->lastInsertId();
    return CObject::loadByid($objectId);
  }
}