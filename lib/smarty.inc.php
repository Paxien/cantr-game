<?php

// load Smarty library

class CantrSmarty extends Smarty
{

  function __construct()
  {
    parent::__construct();

    $this->setTemplateDir(_ROOT_LOC . '/cache/smarty/templates/');
    $this->setCompileDir(_ROOT_LOC . '/cache/smarty/templates_c/');
    $this->setCacheDir(_ROOT_LOC . '/cache/smarty/cache/');
    $this->setConfigDir(_ROOT_LOC . '/cache/smarty/configs/');

    $this->caching = false;

    if (isset($GLOBALS['player'])) {
      $this->assign("player", $GLOBALS ['player']);
    }
    if (isset($GLOBALS ['character'])) {
      $this->assign("character", $GLOBALS ['character']);
    }
    if (isset($GLOBALS ['s'])) {
      $this->assign("SessionID", $GLOBALS ['s']);
    }
    if (isset($GLOBALS ['page'])) {
      $this->assign("page", $GLOBALS ['page']);
    }
    if (isset($GLOBALS ['l'])) {
      $this->assign("language", $GLOBALS ['l']);
    }
  }

  function langTemplateName($template, $lang)
  {
    return substr($template, 0, -4) . "." . $lang . substr($template, -4);
  }

  function displayLang($template, $lang = "en")
  {
    $this->display($this->langTemplateName($template, $lang));
  }

  function fetchLang($template, $lang = "en")
  {
    $this->fetch($this->langTemplateName($template, $lang));
  }
}
