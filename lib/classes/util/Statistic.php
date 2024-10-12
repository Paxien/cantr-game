<?php

class Statistic
{
  public function __construct($type, Db $db)
  {
    $this->type = $type;
    $this->db = $db;
  }

  /**
   * Stores a row into ingame_stats table by adding a tuple: (type, subtype, char_id, number) $number to row with already existing type, subtype and char_id.
   * Creates a new row if it doesn't exist and number > 0.
   * It does nothing (no new row is created) when number is zero.
   *
   * Example: $stats = new Statistic("animal_kills", Db::get()); $stats->update("deer", $charId, 1).
   * @param string $subType detailed info about thing that is being tracked
   * @param int|null $charId auxiliary data about the subtype. Usually id of char commiting specified action, but it might be interpereted differently.
   *   If $charId is null then it tries to take charId from the global variable.
   * @param int $number indicating amount of things that are being changed in the statistics
   * @throws Exception when database is working incorrectly
   */
  public function store($subType, $charId = null, $number = 1)
  {
    if ($number == 0) { // nothing to insert
      return;
    }

    $stm = $this->db->prepare("INSERT INTO ingame_stats (`type`, `subtype`, `number`, `day`, `char_id`)
      VALUES (:type, :subtype, :number, :day, :char_id)");
    $stm->bindStr("type", $this->type);
    $stm->bindStr("subtype", $subType);
    $stm->bindInt("number", $number);
    $stm->bindInt("day", GameDate::NOW()->getDay());
    $stm->bindInt("char_id", isset($charId) ? $charId : $GLOBALS['character']);
    $stm->execute();
  }

  /**
   * Updates ingame_stats table by adding value $number to row with already existing type, subtype and char_id.
   * Creates a new row if it doesn't exist and number > 0.
   * It does nothing (no new row is created) when number is zero.
   *
   * Example: $stats = new Statistic("food_eaten", Db::get()); $stats->update("beer", $charId, $numberOfGrams).
   * @param string $subType detailed info about thing that is being tracked
   * @param int|null $charId auxiliary data about the subtype. Usually id of char commiting specified action, but it might be interpereted differently.
   *   If $charId is null then it tries to take charId from the global variable.
   * @param int $number indicating amount of things that are being changed in the statistics
   * @throws Exception when database is working incorrectly
   */
  public function update($subType, $charId = null, $number = 1)
  {
    $stm = $this->db->prepare("UPDATE `ingame_stats` SET `number` = `number` + :number
      WHERE `type` = :type AND `subtype` = :subtype AND `day` = :day AND `char_id` = :char_id");

    $stm->bindStr("type", $this->type);
    $stm->bindStr("subtype", $subType);
    $stm->bindInt("number", $number);
    $stm->bindInt("day", GameDate::NOW()->getDay());
    $stm->bindInt("char_id", isset($charId) ? $charId : ($GLOBALS['character'] ?: 0));
    $stm->execute();
    $isUpdated = $stm->rowCount() > 0;

    if (!$isUpdated) {
      $this->store($subType, $charId, $number);
    }
  }
}
