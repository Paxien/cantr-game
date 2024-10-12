<?php

$startTime = microtime(true);

$ajaxRequest = true; // changes behaviour of cantr_redirect function and Error class

include_once "../lib/stddef.inc.php";

$s = session::getSessionFromCookie();

include_once _LIB_LOC . "/urlencoding.inc.php";
DecodeURIs();

$turn = GameDate::NOW()->getObject();
$turn_info = clone $turn; // backward compatible

$character = HTTPContext::getInteger('character');

if (!$character) { // make sure character is specified, because this file handles ajax actions for characters
  CError::throwRedirect("", "No character specified");
}

$session_handle = new Session($s, $character, false);
$session_info = $session_handle->checklogin();

if (!($session_info instanceof stdClass)) { // when error occurred => session is corrupted 
  CError::throwRedirect("", "Something went wrong");
}

$player = $session_info->player;
$lang_abr = $session_handle->languagestr;

// SANITIZE INPUT
$page = HTTPContext::getString('page', '');

$allowedPages = [
  "talk" => "action.talk.inc.php",
  "map" => "map.php",
  "drop" => "action.drop.php",
  "take" => "action.take.php",
  "give" => "action.give.php",
  "useraw" => "action.useraw.inc.php",
  "eatraw" => "action.eatraw.inc.php",
  "store" => "action.store.php",
  "retrieve" => "action.retrieve.php",
  "drag" => "action.drag.inc.php",
  "repair" => "action.repair.inc.php",
  "name" => "action.name.inc.php",
  "nameloc" => "page.nameloc.inc.php",
  "ingest_all" => "action.ingest_all.php",
  "set_ship_course" => "action.adjustsailing.inc.php",
  "manage_whispering_bookmarks" => "ajax/action.manage_whisper_bookmarks.php",
  "preview_note" => "ajax/preview_note.php",
  "dropdragging" => "action.dropdragging.inc.php",
  "dropproject" => "action.dropproject.inc.php",
  "object_ordering" => "ajax/action.object_ordering.php",
  "pointat" => "action.pointat.inc.php",
  "wear" => "action.wear_clothes.inc.php",
  "copynote" => "action.copynote.inc.php",
  "set_messenger_home" => "animals/messengerBirds/action.messenger_set_home.php",
  "dispatch_messenger_bird" => "animals/messengerBirds/action.dispatch_messenger_bird.php",
  "info.whispering_bookmarks" => "ajax/info.whispering_bookmarks.php",
  "info.object" => "ajax/info.object.php",
  "info.character" => "ajax/info.character.php",
  "info.repair" => "ajax/info.repair.php",
  "info.projects" => "ajax/info.projects.php",
  "info.storages" => "ajax/info.storages.php",
  "info.note_storages" => "ajax/info.note_storages.php",
  "info.locations" => "ajax/info.locations.php",
  "info.location" => "ajax/info.location.php",
  "info.characters.list" => "ajax/info.characters.list.php",
  "info.eatraw" => "ajax/info.eatraw.php",
  "info.ingest_all" => "ajax/info.ingest_all.php",
  "info.new_events" => "ajax/info.new_events.php",
  "info.sailing" => "ajax/info.sailing.php",
  "info.bird_nests" => "ajax/info.bird_nests.php",
  "changeobjdesc" => "page.namekey.inc.php",
];

$accessibleInNDS = [
  "talk", "map", "name", "manage_whispering_bookmarks", "info.whispering_bookmarks", "info.character", "info.new_events",
]; // only pages listed here can be accessed when in NDS or passed out

$playerInfo = Player::loadById($player);

if ($playerInfo->getStatus() == PlayerConstants::LOCKED) {
  CError::throwRedirect("player", "You can't do anything while your account is locked");
}

// character actions
if ($playerInfo->isOnLeave()) {
  CError::throwRedirectTag("player", "error_play_while_on_leave");
}

try {
  $char = Character::loadById($character);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("player", "<CANTR REPLACE NAME=error_page_unavailable PAGE=$page> (no character)");
}

if (!$char->isAlive()) {
  CError::throwRedirectTag("player", "error_play_dead_char");
}

if (Limitations::getLims($character, Limitations::TYPE_LOCK_CHAR) > 0) {
  CError::throwRedirectTag("player", "error_character_locked");
}

if ($s && isset($allowedPages[$page])) {
  include _LIB_LOC . "/" . $allowedPages[$page];
} else {
  CError::throwRedirectTag("player", "error_page_unavailable PAGE=$page");
}

if (!in_array($page, $accessibleInNDS)) {
  if ($char->hasPassedOut()) {
    CError::throwRedirectTag("player", "error_too_drunk");
  } elseif ($char->isNearDeath()) {
    CError::throwRedirectTag("player", "error_near_death_state");
  }
}

$time = microtime(true) - $startTime;

if (mt_rand(0, 1000) < 20) {
  $timing = new Timing(Db::get());
  $timing->store(true);
}
