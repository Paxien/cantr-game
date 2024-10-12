<?php

class CharacterSettings
{
  const EVENT_FILTER = 0;
  const ACTIVITY_EVENT_FILTER = 2;
  const OPT_OUT_FROM_SPAWNING_SYSTEM = 3;

  /** @var Character */
  private $char;
  /** @var Db */
  private $db;

  public function __construct(Character $char, Db $db)
  {
    $this->char = $char;
    $this->db = $db;
  }

  public function updateOptOutFromSpawningSystem($optOut)
  {
    $stm = $this->db->prepare("SELECT * FROM settings_chars
      WHERE person = :charId AND type = :type");
    $stm->bindInt("charId", $this->char->getId());
    $stm->bindInt("type", self::OPT_OUT_FROM_SPAWNING_SYSTEM);
    $stm->execute();
    if ($stm->exists()) {
      $stm = $this->db->prepare("UPDATE settings_chars SET data = :value
        WHERE person = :charId AND type = :type");
      $this->bindQueryValues($stm, $optOut);
      $stm->execute();
    } else {
      $stm = $this->db->prepare("INSERT INTO settings_chars (person, type, data)
        VALUES (:charId, :type, :value)");
      $this->bindQueryValues($stm, $optOut);
      $stm->execute();
    }
  }

  private function bindQueryValues(DbStatement $stm, $optOut)
  {
    $stm->bindInt("charId", $this->char->getId());
    $stm->bindInt("type", self::OPT_OUT_FROM_SPAWNING_SYSTEM);
    $stm->bindBool("value", $optOut);
  }

  public function isOptOutFromSpawningSystem()
  {
    $stm = $this->db->prepare("SELECT data FROM settings_chars
      WHERE person = :charId
        AND type = :type");
    $stm->bindInt("charId", $this->char->getId());
    $stm->bindInt("type", self::OPT_OUT_FROM_SPAWNING_SYSTEM);
    $optedOut = $stm->executeScalar();

    return !!$optedOut;
  }
}
