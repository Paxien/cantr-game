<?php

/**
 * Class JSTranslations
 * Responsible for making certain translation tags available client-side
 * to make them accessible for javascript in players browser.
 * Singleton pattern.
 */
class JsTranslations
{
  private static $instance;

  /**
   * @return JsTranslations global instance
   */
  public static function getManager()
  {
    if (self::$instance == null) {
      self::$instance = new self($GLOBALS['character'], $GLOBALS['l']);
    }
    return self::$instance;
  }

  private $character;
  private $language;

  public function __construct($character, $language)
  {
    $this->character = intval($character);
    $this->language = intval($language);
  }

  private $tags = [];
  private $translatedTags = [];

  /**
   * @param string[] $tags list of tags to be made available in javascript
   */
  public function addTags(array $tags)
  {
    foreach ($tags as $tag) {
      $this->tags[] = $tag;
    }
  }

  /**
   * @param array $translations assoc array of pairs: "tag => translated text"
   * to specify special translations which require additional computation before being exposed to js.
   */
  public function addTranslations(array $translations)
  {
    $this->translatedTags = array_merge($this->translatedTags, $translations);
  }

  /**
   * Returns assoc array of pairs: "tag => translated text" based on Tags and already specified Translations.
   * If tag exists in both $tags and $translations, then value from $translations will be used.
   * @return array array of translations to make accessible from javascript
   */
  public function getTranslations()
  {
    $tag = new ReplaceTag();
    $tag->character = $this->character;
    $tag->language = $this->language;

    $interpretQueue = Pipe::from($this->tags)->map(function($tag) {
      return "<CANTR REPLACE NAME=". $tag .">";
    })->toArray();

    $interpretQueue = $tag->interpretQueue($interpretQueue);

    $interpreted = [];
    foreach ($this->tags as $tagName) {
      $interpreted[$tagName] = $interpretQueue["<CANTR REPLACE NAME=" . $tagName . ">"];
    }
    return array_merge($interpreted, $this->translatedTags);
  }
}