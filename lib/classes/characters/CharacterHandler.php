<?php

class CharacterHandler
{

  /**
   * Checks if character is in near death state (after getting 100% damage)
   */
  public static function isNearDeath($charId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT char_id FROM char_near_death WHERE char_id = :charId");
    $stm->bindInt("charId", $charId);
    $isNDS = $stm->executeScalar();
    return ($isNDS != null);
  }

  /**
   * Returns near death state (see: class CharacterConstants)
   */
  public static function getNearDeathState($charId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT state FROM char_near_death WHERE char_id = :charId");
    $stm->bindInt("charId", $charId);
    return $stm->executeScalar();
  }
}
