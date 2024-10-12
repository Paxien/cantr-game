<?php

// SANITIZE INPUT
$character = HTTPContext::getInteger('character');
$multiple = $_REQUEST['multiple'];

$smarty = new CantrSmarty;
$reachedMax = false;

$v_link_used = $player != $char->getPlayer();

if ($v_link_used) {
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

/* ***************** INVENTORY ********************* */

//set cookies for default display

if ($_GET ['show_items']) {
  setcookie('show_items_cookie_' . $character, $_GET['show_items'], time() + 60 * 60 * 24 * 30);
} else {
  if ($_COOKIE['show_items_cookie_' . $character]) {
    $show_items = $_COOKIE['show_items_cookie_' . $character];
  } else {
    $show_items = '2';
  }
}

$smarty->assign('ShowItems', $show_items);
$smarty->assign('multiple', $multiple);

if (!isset($_COOKIE['show_items_cookie_' . $character])) {
  $_COOKIE['show_items_cookie_' . $character] = $_POST['show_items'];
}

$maxObjects = ObjectConstants::MAX_OBJECTS_SHOWN;
if (!$multiple) {
  $objects = ObjectsList::generateObjectsArray($char, $char, $l, $show_items);
} else {
  $maxObjects *= 10;
  $objects = array();

  $notes = CObject::inInventoryOf($char)->types([ObjectConstants::TYPE_NOTE, ObjectConstants::TYPE_ENVELOPE])->findAll();

  foreach ($notes as $note) {
    $objectView = new ObjectView($note, $char);
    $datagot = $objectView->show('transfer_long');

    $obinfo = array();
    $obinfo['id'] = $note->getId();
    $obinfo['name'] = $datagot->transfer_long;

    $objects[] = $obinfo;
    if (count($objects) > $maxObjects){
      $reachedMax = true;
      break;
    }
  }
}

if (count($objects) > $maxObjects){
  $objects = array_slice($objects, 0, $maxObjects);
  $reachedMax = true;
}
if ((!isset($_COOKIE['redirected']) || !$_COOKIE['redirected']) && $reachedMax && !$multiple) {
  // Remember whether we already redirected due to an error and stayed on the same page (don't keep redirecting)
  setcookie('redirected', true);
  CError::throwRedirectTag($page, "error_max_objects_shown");
} else {
  unset($_COOKIE['redirected']);
  setcookie('redirected', '', time() - 3600, '/'); // empty value and old timestamp
}
$smarty->assign("objects", $objects);

$useJs = (PlayerSettings::getInstance($player)->get(PlayerSettings::JS_OBJ_INV) == 0);
$smarty->assign("isJsInterface", (!$multiple) && $useJs);

JsTranslations::getManager()->addTags(["js_form_amount", "js_form_drop_amount", "js_form_take_amount", "js_form_store_into",
  "js_form_repair_hours", "js_form_drag_goal", "js_form_give_receiver", "js_form_use_project",
  "js_form_storages_in_inventory", "js_form_storages_on_ground", "js_dragging_failed", "page_eatraw_amount",
  "state_text", "amount_to_maximize", "change_per_100g",
  "js_form_dispatch_messenger", "js_set_messenger_home_text_1", "js_set_messenger_home_text_2",
  "js_form_set_messenger_home", "js_select_home_to_dispatch_text", "js_turn_back_from_messenger_text"]);

$smarty->assign("multiple", $multiple);

$smarty->displayLang("page.inventory.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();