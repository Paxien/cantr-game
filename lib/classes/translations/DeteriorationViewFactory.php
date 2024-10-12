<?php

class DeteriorationViewFactory
{
  const VISIBLE = 1;
  const NOT_VISIBLE = 2;
  const VISIBLE_WHEN_NOT_ZERO = 3;

  private $visibility;
  private $language;
  /** @var bool */
  private $forceReplace;

  public function __construct($visibility = self::VISIBLE, $forceReplace = false)
  {
    $this->visibility = $visibility;
    $this->forceReplace = $forceReplace;
  }

  public function language($language)
  {
    $this->language = $language;
    return $this;
  }

  public function getDeteriorationTag($deterioration)
  {
    if ($deterioration < 2500) {
      return "<CANTR REPLACE NAME=det_brandnew>";
    } elseif ($deterioration < 5000) {
      return "<CANTR REPLACE NAME=det_new>";
    } elseif ($deterioration < 6250) {
      return "<CANTR REPLACE NAME=det_used>";
    } elseif ($deterioration < 7500) {
      return "<CANTR REPLACE NAME=det_often-used>";
    } elseif ($deterioration < 8750) {
      return "<CANTR REPLACE NAME=det_old>";
    }
    return "<CANTR REPLACE NAME=det_crumbling>";
  }

  public function show($deterioration, $gender, $name = "")
  {
    if ($this->visibility == self::VISIBLE) {
      $showDeter = true;
    } elseif ($this->visibility == self::NOT_VISIBLE) {
      $showDeter = false;
    } else {
      $showDeter = ($deterioration > 0);
    }

    if (!$showDeter) {
      return $name;
    }
    $deter = $this->getDeteriorationTag($deterioration);
    $deter = TagBuilder::forText($deter)->allowHtml(true)->build()->interpret();
    
    $language = (isset($this->language) ? $this->language : intval($GLOBALS['l']));
    $grammar = "Nom";
    import_lib("func.grammar.inc.php");
    return adjust_nounphrase($language, "", $deter, $name, $grammar, $gender, $this->forceReplace || empty($name));
  }
}
