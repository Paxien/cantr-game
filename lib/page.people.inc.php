<?php

$smarty = new CantrSmarty;

$v_link_used = $player != $char->getPlayer();

if ($v_link_used)  {
  $playerInfo = Request::getInstance()->getPlayer();
  if (!$playerInfo->hasAccessTo(AccessConstants::CONTROL_OTHER_CHARACTERS)) {
    CError::throwRedirectTag("player", "error_you_are_not_allowed_to_view_events_of_other_players");
  }
}

if ($player == $char->getPlayer()) {
  $char->updateLastDateAndTime(GameDate::NOW());
  $char->saveInDb();
}

$char_loc = new char_location($char->getId());

$charInfo = new CharacterInfoView($char);
$charInfo->show();

/* ***************** PEOPLE ********************* */

$chars = $char_loc->chars_near(_PEOPLE_NEAR);

$db = Db::get();
$stm = $db->prepareWithIntList("SELECT ch.*, chn.description,
    health.value AS health, hunger.value AS hunger, drunkenness.value AS drunkenness, nds.state AS near_death,
    projects.name AS project_name, locations.type AS loc_type, locations.area AS loc_area
  FROM chars AS ch
    LEFT JOIN charnaming AS chn ON chn.observed = ch.id AND chn.observer = :observer AND chn.type = 1
    LEFT JOIN states AS health ON health.person=ch.id AND health.type = :health
    LEFT JOIN states AS hunger ON hunger.person=ch.id AND hunger.type = :hunger
    LEFT JOIN states AS drunkenness ON drunkenness.person=ch.id AND drunkenness.type = :drunkenness
    LEFT JOIN projects ON projects.id=ch.project
    LEFT JOIN locations ON locations.id = ch.location
    LEFT JOIN char_near_death nds ON nds.char_id=ch.id
  WHERE ch.id IN (:charIds) ORDER BY ch.id", [
    "charIds" => $chars,
]);
$stm->bindInt("observer", $char->getId());
$stm->bindInt("health", StateConstants::HEALTH);
$stm->bindInt("hunger", StateConstants::HUNGER);
$stm->bindInt("drunkenness", StateConstants::DRUNKENNESS);
$stm->execute();
foreach ($stm->fetchAll() as $X) {
  $chars_info [$X->id] = $X;
}

foreach ($chars as $charid) {

  $otherchar_info = $chars_info [$charid];
  
  $smartychar = new StdClass();

  $smartychar->id = $otherchar_info->id;
  $smartychar->description = $otherchar_info->description;
  $smartychar->sex = $otherchar_info->sex;
  $smartychar->project = $otherchar_info->project;
  $smartychar->hunger = $otherchar_info->hunger;
  $smartychar->health = $otherchar_info->health;
  $smartychar->drunkenness = $otherchar_info->drunkenness;
  $smartychar->additional = "";
  $smartychar->near_death = $otherchar_info->near_death;
  if ($smartychar->near_death == CharacterConstants::NEAR_DEATH_NOT_HEALED) {
    $smartychar->additional .= '<span class="people-label"><CANTR REPLACE NAME=char_near_death_state></span>';
  } elseif ($smartychar->near_death == CharacterConstants::NEAR_DEATH_HEALED) {
    $smartychar->additional .= '<span class="people-label"><CANTR REPLACE NAME=char_near_death_state_cured></span>';
  }
  
  $tag = new tag;
  $tag->content = $otherchar_info->project_name;
  $tag->html = false;
  $smartychar->project_name = $tag->interpret();
  $smartychar->is_travelling = $char_loc->istravelling;
  
  if ($char->getLocation() != 0){
    $smartychar->workingonproject = $otherchar_info->project != 0;
    if (!$char->isBusy()) {
      $smartychar->canjoinitsproject = $otherchar_info->project != 0 && $otherchar_info->location == $char->getLocation();
    }
  }
    
  if (($otherchar_info->location != $char->getLocation())) {
  
    if ($otherchar_info->location != 0) {
      if ($otherchar_info->loc_type == 3)
        $smartychar->additional .= " (<CANTR LOCNAME ID=$otherchar_info->location> [<CANTR LOCDESC ID={$otherchar_info->location}>])";
      else
        $smartychar->additional .= " (<CANTR LOCNAME ID=$otherchar_info->location>)";
    } else {
      $smartychar->additional .= " (<CANTR REPLACE NAME=char_marching>)";
    }
    
  }
  
  $tag = new tag;
  $tag->content = "<CANTR CHARNAME ID=$otherchar_info->id>";
  $tag->html = false;
  $smartychar->name = $tag->interpret ();

  $characters [] = $smartychar;

}

$smarty->assign ("chars", $characters);
$smarty->assign("charlocation", $char->getLocation());
$smarty->assign("charBusy", $char->isBusy());

$smarty->assign("CONSTANTS", ["DRUNK_STATE_MIN" => [CharacterConstants::DESC_DRUNK_1_MIN, CharacterConstants::DESC_DRUNK_2_MIN,
  CharacterConstants::DESC_DRUNK_3_MIN, CharacterConstants::DESC_DRUNK_4_MIN, CharacterConstants::DESC_DRUNK_5_MIN], "PASSED_OUT_MIN" => CharacterConstants::PASSOUT_LIMIT]);

$smarty->displayLang ("page.people.tpl", $lang_abr); 

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();