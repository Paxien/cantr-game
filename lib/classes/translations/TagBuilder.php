<?php

class TagBuilder
{
  private $text;
  private $charId;
  private $language;
  private $allowHtml = false;
  private $admin = false;
  private $times = 1;
  private $db = null;

  public static function forText($text)
  {
    $tagBuilder = new self();
    $tagBuilder->text = $text;
    return $tagBuilder;
  }

  public static function forTag($tag)
  {
    return self::forText("<CANTR REPLACE NAME=" . $tag . ">");
  }

  public static function forChar($char)
  {
    if ($char instanceof Character) {
      $char = $char->getId();
    }
    $charId = intval($char);

    $tagBuilder = new self();
    $tagBuilder->text = "<CANTR CHARNAME ID=" . $charId . ">";
    return $tagBuilder;
  }

  public static function forLocation($location)
  {
    if ($location instanceof Location) {
      $location = $location->getId();
    }
    $locId = intval($location);

    $tagBuilder = new self();
    $tagBuilder->text = "<CANTR LOCNAME ID=" . $locId . ">";
    return $tagBuilder;
  }

  public static function forLocDesc($location)
  {
    if ($location instanceof Location) {
      $location = $location->getId();
    }
    $locId = intval($location);

    $tagBuilder = new self();
    $tagBuilder->text = "<CANTR LOCDESC ID=" . $locId . ">";
    return $tagBuilder;
  }

  public static function forObject($object, $detailed = true)
  {
    if ($object instanceof CObject) {
      $object = $object->getId();
    }
    $objectId = intval($object);

    $tagBuilder = new self();
    $tagBuilder->text = "<CANTR OBJNAME ID=" . $objectId . " TYPE=" . ($detailed ? 0 : 1) . ">";
    return $tagBuilder;
  }

  private function __construct()
  {
    // default values
    $this->language = intval($GLOBALS['l']);
    $this->charId = intval($GLOBALS['character']);
  }

  public function observedBy($char)
  {
    if ($char instanceof Character) {
      $this->charId = $char->getId();
    } else {
      $this->charId = intval($char);
    }
    return $this;
  }

  public function language($language)
  {
    $this->language = intval($language);
    return $this;
  }

  public function allowHtml($allowHtml)
  {
    $this->allowHtml = !!$allowHtml;
    return $this;
  }

  public function admin($admin)
  {
    $this->admin = $admin;
    return $this;
  }

  public function db($db)
  {
    $this->db = $db;
    return $this;
  }

  public function times($times)
  {
    $this->times = $times;
    return $this;
  }

  public function twice()
  {
    $this->times(2);
    return $this;
  }

  public function build()
  {
    if ($this->db === null) {
      $this->db = Db::get();
    }
    $tag = new Tag($this->text, $this->allowHtml, $this->charId, $this->language, $this->db);
    $tag->admin = $this->admin;
    $tag->setTimes($this->times);
    return $tag;
  }
}
