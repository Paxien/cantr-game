<?php

abstract class BaseTag
{
  var $content;
  var $html;
  var $character;
  var $language;

  /** @var Db */
  protected $db;

  abstract protected function interpret($content = null);

  function __construct($content = null, $html = false, $session = null, $char = null, $language = null)
  {
    global $character;
    global $l;
    $char = $char ? $char : $character;
    $language = $language ? $language : $l;

    $this->character = $char;
    $this->language = $language;
    $this->content = $content;
    $this->html = $html;
    $this->db = Db::get();
  }
}
