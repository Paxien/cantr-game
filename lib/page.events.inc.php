<?php

// SANITIZE INPUT
$all = $_REQUEST['all'];

if (Limitations::getLims($character, Limitations::TYPE_LOCK_CHAR) > 0) {
  CError::throwRedirectTag("player", "error_character_locked");
}

$v_link_used = $player != $char->getPlayer();

if ($v_link_used)  {
  $playerInfo = Request::getInstance()->getPlayer();
  if ($playerInfo->hasAccessTo(AccessConstants::CONTROL_OTHER_CHARACTERS)) {
    $characterOwner = Player::loadById($char->getPlayer());

    $report = $playerInfo->getFullName() . " (id=$player) viewed the events of character " . $char->getName() . " (id=" . $char->getId() .
      ") belonging to player " . $characterOwner->getFullName() . " (id=" . $characterOwner->getId() . ") ";
    Report::saveInDb("eventsearch", $report);
  } else {
    CError::throwRedirectTag("player", "error_you_are_not_allowed_to_view_events_of_other_players");
  }
}

if (!$v_link_used) {
  $char->updateLastDateAndTime(GameDate::NOW());
  $char->saveInDb();
}

// Smarty init
$smarty = new CantrSmarty;

$smarty->assign ("character", $character);

$charInfo = new CharacterInfoView($char);
$charInfo->show();

//////character customizable settings

// maybe for future, now this code has rejected
//if player select new filter with should handle it

$filterAll = new StdClass();
$filterAll->name ="<CANTR REPLACE NAME=event_filter_all>";
$filterAll->data = '"-1"';

$filters = array();
$db = Db::get();
$stm = $db->prepare("SELECT id, data FROM settings_chars WHERE person = :charId AND type = :type");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("type", CharacterSettings::EVENT_FILTER);
$stm->execute();

$selectedExists = false;

$id = 0;
foreach ($stm->fetchAll() as $filter) {

  $filterInfo = explode('|', $filter->data);
  //ignore bad data
  if (count( $filterInfo ) != 2) {
    continue;
  }

  $groupIds = explode( ',' , $filterInfo[1] );

  $filters[$id] = new StdClass();
  $filters[ $id ]->name = $filterInfo[0];

  $filters[ $id ]->data = '"' . implode( '","', $groupIds ) . '"';
  $id++;
}
$filters[$id] = $filterAll;

$smarty->assign( "filters", $filters);

$thisCharFilterCookieName = "event_filter_" . md5( $character );
if( isset( $_COOKIE[$thisCharFilterCookieName] ) ) {
  $smarty->assign( 'selectedfilter', $_COOKIE[$thisCharFilterCookieName] );
}
///////////////////////////////////////////////


$smarty->assign ("too_drunk", $char->hasPassedOut());


if (!isset($_COOKIE['set_box_cookie_'.$character])) {
  $_COOKIE['set_box_cookie_'.$character] = $_POST['large_box'];
}

/* ***************** EVENTS ********************* */

$stm = $db->prepare("SELECT MAX(id) FROM events");
$latestEvent = $stm->executeScalar();
$smarty->assign ("maxid", $latestEvent);



if (isset($none) && $none == 'yes') {
  // nothing
} else {

  if (isset($all) && $all == 'yes') {
    $latest = 0;
  } else {
    $stm = $db->prepare("SELECT event FROM events_view WHERE observer = :charId AND viewed = :viewed LIMIT 1");
    $stm->bindInt("charId", $char->getId());
    $stm->bindInt("viewed", _EVTEXP);
    $latest = $stm->executeScalar();
  }

  if (!$latest) {
    $latest = 0; // No events has been read 5 times - maybe it's new character, so show them all
  }

  $eventList = new EventListView($char, true);
  $events = $eventList->interpret($latest);

  if (!$v_link_used) {
    // Notify newevents table of status of the newest event for this character.
    $stm = $db->prepare("UPDATE newevents SET new = 1 WHERE person = :charId");
    $stm->bindInt("charId", $char->getId());
    $stm->execute();
  }

  $smarty->assign("eventExistingGroups", $eventList->getGroups());
}

$smarty->assign ("events", $events);

$stm = $db->prepare("DELETE FROM events_view WHERE observer = :charId AND viewed >= :viewed");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("viewed", _EVTEXP);
$stm->execute();

if (isset($none) && $none=='yes' && !$v_link_used) {
  $stm = $db->prepare("UPDATE events_view SET viewed = viewed + 1, event = :event WHERE observer = :charId ORDER BY viewed DESC");
  $stm->bindInt("event", $latestEvent);
  $stm->bindInt("charId", $char->getId());
  $stm->execute();
} else {
  $stm = $db->prepare("UPDATE events_view SET viewed = viewed + 1 WHERE observer = :charId ORDER BY viewed DESC");
  $stm->bindInt("charId", $char->getId());
  $stm->execute();
}

$stm = $db->prepare("INSERT IGNORE INTO events_view (observer, viewed, event) VALUES (:charId, 1, :event)");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("event", $latestEvent);
$stm->execute();

if (!(isset($all) && $all=='yes')) {
  $smarty->assign("showall", true);
}

$stm = $db->prepare("SELECT target FROM bookmark_whispering WHERE owner = :charId ORDER BY id");
$stm->bindInt("charId", $char->getId());
$stm->execute();

$charLoc = new char_location($char->getId());
$whisperingBookmarks = array();
foreach ($stm->fetchScalars() as $target) {
  $nameTag = new Tag("<CANTR CHARNAME ID={$target}>", false);
  $nameTag = new Tag($nameTag->interpret(), false);
  $whisperingBookmarks[] = array(
    "id" => $target,
    "name" => $nameTag->interpret(),
    "near" => $charLoc->char_isnear($target),
  );
}

$smarty->assign("whisperingBookmarks", json_encode($whisperingBookmarks));

JsTranslations::getManager()->addTags(["form_confirm", "js_events_inventory", "js_events_clothes", "js_events_tab_loading",
  "char_desc_working_on_project", "js_events_tab_name", "js_events_tab_additional_desc", "js_events_tab_general",
  "js_events_tab_appearance", "js_events_tab_naming", "js_events_tab_at_location", "js_whispering_say_to_all",
  "js_box_close", "char_desc_bar_damage","char_desc_bar_tiredness", "button_talk_to_all", "button_whisper",
  "alt_description_person", "alt_talk_to_person", "alt_point_at_person",
  "alt_join_person_project", "alt_drag_person", "alt_hit_person", "js_box_close"]);

$smarty->assign("charIdMd5", md5($char->getId()));
$smarty->assign("experimentalUiChanges", intval(PlayerSettings::getInstance($player)->get(PlayerSettings::EXPERIMENTAL_UI_CHANGES) == 1));

//language id
$smarty->assign( "l", $l);
$smarty->displayLang ("page.events.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
