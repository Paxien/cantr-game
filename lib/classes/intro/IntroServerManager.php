<?php

class IntroServerManager
{

  private $db;

  public static function newInstance(Db $introDb)
  {
    return new self($introDb);
  }

  private function __construct(Db $introDb)
  {
    $this->db = $introDb;
  }

  /**
   * Informs if it's possible to spawn a newbie character of specified language.
   * @param  int  $language language to be checked
   * @return boolean true if it's allowed to spawn a character.
   */
  public function isIntroOpenFor($language)
  {
    $stm = $this->db->prepare("SELECT spawning_allowed FROM languages WHERE id = :language");
    $stm->bindInt("language", $language);
    return !!$stm->executeScalar();
  }

} 
