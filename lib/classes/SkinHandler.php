<?php

class SkinHandler {
  
  private $player;
  
  private $initialized = false;
  private $mainPath;
  private $secondaryPath;
  private $isCustom;

  /** @var Db */
  private $db;
  
  const DEFAULT_CSS = "skins/green-orange.css";
  const DEFAULT_CSS_NAME = "green-orange.css";
  const CLASSIC_CSS = "skins/classic.css";
  const CSS_ROOT = "css/";
  
  const CUSTOM_CSS_MAX_SIZE = 50000;


  public function __construct($player) {
    if (!Validation::isPositiveInt($player)) {
      throw new InvalidArgumentException("player id is not a number");
    }

    $this->player = $player;
    $this->db = Db::get();
  }
  
  public function getAvailableSkins() {
    $files = scandir(self::CSS_ROOT."skins");
    $skins = array();
    if ($files) {
      foreach ($files as $file) {
        if (strrchr($file, ".") == ".css") {
          $skins[] = $file; // skin name
        }
      }
    }
    return $skins;
  }
  
  public function getCustomSkinText() {
    $stm = $this->db->prepare("SELECT custom_name FROM css_skins WHERE player = :playerId");
    $stm->bindInt("playerId", $this->player);
    $custom_name = $stm->executeScalar();
    if (strrchr($custom_name, ".") == ".css" && file_exists(self::CSS_ROOT."custom_skins/".$custom_name)) {
      return file_get_contents(self::CSS_ROOT."custom_skins/".$custom_name);
    }
    return "";
  }
  
  private function getSkinData() {
    $stm = $this->db->prepare("SELECT is_custom, base_name, custom_name FROM css_skins WHERE player = :playerId");
    $stm->bindInt("playerId", $this->player);
    $stm->execute();
    if (list($is_custom, $base_name, $custom_name) = $stm->fetch(PDO::FETCH_NUM)) {
      $this->isCustom = $is_custom;
      if ($is_custom) {
        $this->mainPath = "custom_skins/$custom_name";
        if (file_exists(self::CSS_ROOT.$this->mainPath)) {
          $this->secondaryPath = "skins/$base_name"; // we need base in case somebody uses "extend"
        } else {
          $this->mainPath = self::DEFAULT_CSS;
        }
      } else {
        $this->mainPath = "skins/$base_name";
      }
    } else { // if no row in table `css_skins`
      $stm = $this->db->prepare("INSERT INTO css_skins SET player = :playerId, is_custom = :isCustom, base_name = :baseName, custom_name = :customName");
      $stm->bindInt("playerId", $this->player);
      $stm->bindInt("isCustom", 0);
      $stm->bindStr("baseName", self::DEFAULT_CSS_NAME);
      $stm->bindStr("customName", "");
      $stm->execute();
      $this->mainPath = self::DEFAULT_CSS;
      $this->isCustom = false;
    }
    $this->initialized = true;
  }
  
  public function isCustom() {
    if (!$this->initialized) {
      $this->getSkinData();
    }
    return $this->isCustom;
  }

  public function getMainPath() {
    if (!$this->initialized) {
      $this->getSkinData();
    }
    return $this->mainPath;
  }
  
  public function getSecondaryPath() {
    if (!$this->initialized) {
      $this->getSkinData();
    }
    return $this->secondaryPath;
  }
  
  public function getSelectedSkinName() {
    $stm = $this->db->prepare("SELECT base_name FROM css_skins WHERE player = :playerId");
    $stm->bindInt("playerId", $this->player);
    $base_name = $stm->executeScalar();
    return $base_name;
  }

  public function setSkin($is_custom, $base_css, $custom_css) {
      if (strrchr($base_css, ".") != ".css" || !file_exists(self::CSS_ROOT."skins/".$base_css)) { // check if this css is really available
        $base_css = self::DEFAULT_CSS_NAME;
      }
      // we can assume this row already exists, because it's created when any page is loaded
      $stm = $this->db->prepare("UPDATE css_skins SET is_custom = :isCustom, base_name = :baseName WHERE player = :playerId");
      $stm->bindInt("isCustom", 0);
      $stm->bindStr("baseName", $base_css);
      $stm->bindInt("playerId", $this->player);
      $stm->execute();

      if ($is_custom) { // own custom skin as text
      $stm = $this->db->prepare("SELECT custom_name FROM css_skins WHERE player = :playerId");
      $stm->bindInt("playerId", $this->player);
      $old_custom_name = $stm->executeScalar();
      if (strrchr($old_custom_name, ".") == ".css" && file_exists(self::CSS_ROOT."custom_skins/".$old_custom_name)) {
        $fileName = $old_custom_name; // reuse already existing css file
      } else {
        $fileName = $this->player."_".hash('crc32', $custom_css).".css"; // create name for a new css file
      }
      if (strlen($custom_css) <= self::CUSTOM_CSS_MAX_SIZE && file_put_contents(self::CSS_ROOT."custom_skins/".$fileName, $custom_css)) {
        $stm = $this->db->prepare("UPDATE css_skins SET is_custom = :isCustom, custom_name = :customName WHERE player = :playerId");
        $stm->bindInt("isCustom", 1);
        $stm->bindStr("customName", $fileName);
        $stm->bindInt("playerId", $this->player);
        $stm->execute();
        return true;
      }
    }
    return false;
  }
}
