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


$charInfo = new CharacterInfoView($char);
$charInfo->show();

if ($char->getLocation() != 0) {

/* ***************** OBJECTS ********************* */

  try {
    $location = Location::loadById($char->getLocation());
    $objects = ObjectsList::generateObjectsArray($location, $char, $l);
  } catch (InvalidArgumentException $e) {
    $objects = [];
    Logger::getLogger("page.objects.inc.php")->error("character " . $char->getId() . " is in non existent location of id " . $char->getLocation());
  }


  $smarty->assign ("objects", $objects);
  $useJs = (PlayerSettings::getInstance($player)->get(PlayerSettings::JS_OBJ_INV) == 0);
  $smarty->assign ("isJsInterface", $useJs);

  JsTranslations::getManager()->addTags(["js_form_amount", "js_form_drop_amount", "js_form_take_amount", "js_form_store_into",
    "js_form_repair_hours", "js_form_drag_goal", "js_form_give_receiver", "js_form_use_project",
      "js_form_storages_in_inventory", "js_form_storages_on_ground", "js_dragging_failed", "desc_chars_left"]);

}

$smarty->displayLang ("page.objects.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();