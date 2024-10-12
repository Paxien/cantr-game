<?php

class GlobalConfig
{
  /** @var Db */
  private $db;

  public function __construct(Db $db)
  {
    $this->db = $db;
  }

  public function isUniversalTaintEnabled()
  {
    return $this->getConfigValue("universal_taint_enabled");
  }

  public function setUniversalTaintEnabled($enabled)
  {
    $this->updateValue("universal_taint_enabled", $enabled);
  }

  public function getIntroProtectionLevel()
  {
    return $this->getConfigValue("intro_protection_level");
  }

  public function getProjectProgressRatio()
  {
    return $this->getConfigValue("project_progress_ratio");
  }

  public function getTravelProgressRatio()
  {
    return $this->getConfigValue("travel_progress_ratio");
  }

  public function getSailingProgressRatio()
  {
    return $this->getConfigValue("sailing_progress_ratio");
  }

  public function getDeteriorationRatio()
  {
    return $this->getConfigValue("deterioration_ratio");
  }

  public function setIntroProtectionLevel($secLevel)
  {
    $this->updateValue("intro_protection_level", $secLevel);
  }

  public function getMaxCharactersPerPlayer()
  {
    return $this->getConfigValue("max_characters_per_player");
  }

  private function getConfigValue($name)
  {
    $stm = $this->db->prepare("SELECT `value` FROM global_config WHERE `key` = :name");
    $stm->bindStr("name", $name);
    return $stm->executeScalar();
  }

  private function updateValue($name, $value)
  {
    $stm = $this->db->prepare("REPLACE INTO global_config (`key`, `value`) VALUES (:name, :value)");
    $stm->bindStr("name", $name);
    $stm->bindInt("value", $value);
    $stm->execute();
  }
}