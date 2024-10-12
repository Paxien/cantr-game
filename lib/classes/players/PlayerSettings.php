<?php

class PlayerSettings
{
  const PROGRESS_BARS = "progress_bars";
  const HTML_MAIL = "html_mail";
  const BUILD_PICTURES = "build_pictures";
  const COMPASS = "compass";
  const JS_BUILD_MENU = "js_build_menu";
  const CSS_CLASSIC_NOTE = "css_classic_note";
  const CSS_EXTEND_BASE = "css_extend_base";
  const JS_OBJ_INV = "js_obj_inv";
  const JS_LOCATION = "js_location";
  const DID_YOU_KNOW = "did_you_know";
  const CONFIRM_NO_PREVIEW = "confirm_no_preview";
  const RESPONSIVE_LAYOUT = "responsive_layout";
  const EXPERIMENTAL_UI_CHANGES = "experimental_ui_changes";

  const EXIT_PAGE = "exit_page";

  private $options = [];
  private $optionColumns = null;
  private $columnNames = [];

  /** @var PlayerSettings|null */
  private static $instance = null;

  /** @var int */
  private $playerId;
  /** @var Db */
  private $db;

  public static function getInstance($playerId)
  {
    if (self::$instance == null || self::$instance->playerId != $playerId) {
      self::$instance = new PlayerSettings($playerId);
    }
    return self::$instance;
  }

  public function __construct($playerId)
  {
    $this->playerId = $playerId;
    $this->db = Db::get();
    $this->reset();
  }

  public function reset()
  {
    $this->options = null;
    $this->optionColumns = null;
    $this->columnNames = null;

    /*
     * remember that option 0 will always be default
    */
    $this->addOption(self::PROGRESS_BARS, "options", 1, 1, "show progress bars: 0-new;1-old");
    $this->addOption(self::HTML_MAIL, "options", 2, 1, "html mail format in reports: 0-text,1-html");
    $this->addOption(self::BUILD_PICTURES, "options", 3, 1, "build menu pictures: 0-disabled,1-enabled");
    $this->addOption(self::COMPASS, "options", 4, 1, "compass around the map visibility: 0-show,1-hide");
    $this->addOption(self::JS_BUILD_MENU, "options", 5, 1, "JS build menu; 0-enabled,1-disabled");
    $this->addOption(self::CSS_CLASSIC_NOTE, "options", 7, 1, "classic css file when edit/read notes; 0-enabled,1-disabled");
    $this->addOption(self::CSS_EXTEND_BASE, "options", 8, 1, "extend css instead of replacing it; 0-disabled,1-enabled");
    $this->addOption(self::JS_OBJ_INV, "options", 9, 1, "ajax interface for buttons (obj/inv pages); 0-enabled,1-disabled");
    $this->addOption(self::JS_LOCATION, "options", 16, 1, "js location extended box on main pages; 0 - enabled, 1 - disabled");
    $this->addOption(self::DID_YOU_KNOW, "options", 13, 1, "'did you know?' on player page; 0-enabled,1-disabled");
    $this->addOption(self::CONFIRM_NO_PREVIEW, "options", 14, 1, "confirmation when saving w/o preview; 0-enabled,1-disabled");
    $this->addOption(self::RESPONSIVE_LAYOUT, "options", 15, 1, "use (mobile) responsive layout; 0-enabled,1-disabled");
    $this->addOption(self::EXPERIMENTAL_UI_CHANGES, "options", 17, 1, "want to test experimental UI; 0-disable,1-enabled");

    $this->addOption(self::EXIT_PAGE, "exitpage", 0, 2, "exit page;0-webzine;1-wiki;2-forum;3-front page");

    $this->loadFromDb();
  }

  private function addOption($name, $columnName, $pos, $bitCount, $desc, $maxValue = null)
  {
    if ($maxValue == null) {
      $maxValue = pow(2, $bitCount) - 1;
    }

    // adding to the options array
    $this->options[$name]['column'] = $columnName;
    $this->options[$name]['pos'] = $pos;
    $this->options[$name]['bitCount'] = $bitCount;
    $this->options[$name]['maxValue'] = $maxValue;
    $this->options[$name]['desc'] = $desc;
    $this->options[$name]['mask'] = (~((~0) << $bitCount)) << $pos;

    // adding to the columns list
    $this->columnNames[$columnName] = 1;

  }

  private function loadFromDb()
  {
    // get values of all columns
    $colsList = implode(", ", array_keys($this->columnNames));
    $stm = $this->db->prepare("SELECT $colsList FROM players WHERE id = :playerId LIMIT 1");
    $stm->bindInt("playerId", $this->playerId);
    $stm->execute();
    $this->optionColumns = $stm->fetch(PDO::FETCH_ASSOC);
  }

  public function getOptionsList()
  {
    $optionsList = [];
    foreach ($this->options as $name => $entry) {
      $optionsList[$name] = [
        "maxValue" => $entry['maxValue'],
        "selected" => $this->get($name),
      ];
    }

    return $optionsList;
  }

  public function get($name)
  {
    if (!isset($this->options[$name]['value'])) {
      $optionMatchedWithMask = ($this->optionColumns[$this->options[$name]['column']] & $this->options[$name]['mask']);
      $this->options[$name]['value'] = $optionMatchedWithMask >> $this->options[$name]['pos'];
    }
    return $this->options[$name]['value'];
  }

  public function getMaxValue($name)
  {
    return $this->options[$name]['maxValue'];
  }

  public function getDesc($name)
  {
    return $this->options[$name]['desc'];
  }

  public function save()
  {
    // parsing input data
    foreach ($this->options as $optName => $opt) {
      $inputData = HTTPContext::getInteger("settings_interface_$optName", null);
      if ($inputData !== null && $inputData >= 0 && $inputData <= $opt['maxValue']) {
        $currCol = $this->optionColumns[$opt['column']];
        $currCol = $currCol & (~$opt['mask']);
        $currCol += $inputData << $opt['pos'];
        $this->optionColumns[$opt['column']] = $currCol;
      }
    }

    foreach ($this->optionColumns as $colName => $col) {
      $stm = $this->db->prepare("UPDATE players SET `$colName` = :value WHERE id = :playerId LIMIT 1");
      $stm->bindStr("value", $col);
      $stm->bindInt("playerId", $this->playerId);
      $stm->execute();
    }
  }
}
