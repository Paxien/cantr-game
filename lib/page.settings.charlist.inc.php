<?php

class SettingsCharlist
{

  public $template_name = "page.settings.charlist.tpl";

  /** @var Db */
  private $db;

  public function __construct()
  {
    $this->db = Db::get();
  }

  public function getSmarty()
  {
    global $player;

    $languages = array();
    $stm = $this->db->query("SELECT id, original_name FROM languages ORDER BY id");

    foreach ($stm->fetchAll() as $got_lang) {
      $languages[$got_lang->id] = $got_lang->original_name;
    }

    $currentDay = GameDate::NOW()->getDay();
    $stm = $this->db->prepare("SELECT name,register,death_date,language,lastdate,death_cause,status FROM chars WHERE player=:playerId ORDER by register");
    $stm->bindInt("playerId", $player);
    $stm->execute();

    $recent_death = $currentDay - CharacterConstants::DEAD_CLOSE_SLOT_DAYS;
    $recent_death_age = $currentDay - CharacterConstants::DEAD_CLOSE_SLOT_AGE;

    $chars = array();
    foreach ($stm->fetchAll() as $char_info) {

      $death = null;
      if (($char_info->status > _CHAR_ACTIVE) && ($char_info->register >= $recent_death_age) && ($char_info->death_date >= $recent_death)) {
        $blocked_days = min((CharacterConstants::DEAD_CLOSE_SLOT_DAYS - ($currentDay - $char_info->death_date)), (CharacterConstants::DEAD_CLOSE_SLOT_AGE - ($currentDay - $char_info->register)));
      }

      if ($char_info->status > _CHAR_ACTIVE) {

        if ($char_info->death_date) {
          $death = $char_info->death_date;
        } else {
          $death = $char_info->lastdate;
        }
      }

      $char_data = array();
      $char_data['spawn_day'] = $char_info->register;
      $char_data['name'] = $char_info->name;
      $char_data['blocked_days'] = $blocked_days;
      $char_data['death_day'] = $death;
      $char_data['language'] = $languages[$char_info->language];

      $chars[] = $char_data;
    }

    $smarty = new CantrSmarty;
    $smarty->assign("chars", $chars);
    return $smarty;
  }

}
