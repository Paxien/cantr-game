<?php

class TranslocationMonitor
{
  /** @var Db */
  private $db;

  public function __construct()
  {
    $this->db = Db::get();
  }

  /**
   * @param $from Character|Location|CObject a place from which object is moved
   * @param $to Character|Location|CObject a destination
   * @param $object CObject an object being moved
   */
  public function recordObjectTranslocation($from, $to, $object)
  {
    $fromLocaction = 0;
    $fromCharacter = 0;
    $fromObject = 0;

    $toLocaction = 0;
    $toCharacter = 0;
    $toObject = 0;

    if ($from instanceof Character) {
      $fromCharacter = $from->getId();
    } elseif ($from instanceof Location) {
      $fromLocaction = $from->getId();
    } elseif ($from instanceof CObject) {
      $fromObject = $from->getId();
    } else {
      throw new InvalidArgumentException("from is not a character, an object nor a location. It's "
        . var_export($from, true));
    }

    if ($to instanceof Character) {
      $toCharacter = $to->getId();
    } elseif ($to instanceof Location) {
      $toLocaction = $to->getId();
    } elseif ($to instanceof CObject) {
      $toObject = $to->getId();
    } else {
      throw new InvalidArgumentException("to is not a character, an object nor a location. It's "
        . var_export($to, true));
    }

    $gameDate = GameDate::NOW();
    $stm = $this->db->prepare("INSERT INTO recorded_translocations
      (`from_object`, `from_location`, `from_character`,
      `to_object`, `to_location`, `to_character`,
      `object_id`, `object_type`, `day`, `hour`, `minute`)
      VALUES (:fromObject, :fromLocation, :fromCharacter,
              :toObject, :toLocation, :toCharacter, 
              :objectId, :objectType, :day, :hour, :minute)");
    $stm->bindInt("fromObject", $fromObject);
    $stm->bindInt("fromLocation", $fromLocaction);
    $stm->bindInt("fromCharacter", $fromCharacter);
    $stm->bindInt("toObject", $toObject);
    $stm->bindInt("toLocation", $toLocaction);
    $stm->bindInt("toCharacter", $toCharacter);
    $stm->bindInt("objectId", $object->getId());
    $stm->bindInt("objectType", $object->getType());
    $stm->bindInt("day", $gameDate->getDay());
    $stm->bindInt("hour", $gameDate->getHour());
    $stm->bindInt("minute", $gameDate->getMinute());
    $stm->execute();
  }
}