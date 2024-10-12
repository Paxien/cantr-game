<?php

class TagUtil
{

  /**
   * @param $objectUniqueName string unique name of the object
   * @return string _b-type tag (the most generic one, without ) if it exists. Otherwise it falls back to _o-type tag
   */
  public static function getGenericTagForObjectName($objectUniqueName)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT COUNT(*) FROM texts WHERE name = :name");
    $stm->bindStr("name", "item_{$objectUniqueName}_b");
    $buildTagExists = $stm->executeScalar() > 0;
    if ($buildTagExists) {
      return "item_{$objectUniqueName}_b";
    }
    return "item_{$objectUniqueName}_o";
  }

  public static function getBuildingTagByName($name)
  {
    return sprintf("item_%s_b", self::formatName($name));
  }

  public static function getRawTagById($id)
  {
    return self::getRawTagByName(CObject::getRawNameFromId($id));
  }

  public static function getRawTagByName($name)
  {
    return "raw_" . self::formatName($name);
  }

  public static function formatName($name)
  {
    return str_replace(" ", "_", $name);
  }
}