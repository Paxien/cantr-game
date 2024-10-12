<?php

$text_to_broadcast = HTTPContext::getRawString('text_to_broadcast');
$broadcast_language = HTTPContext::getInteger('broadcast_language');
$character_id = HTTPContext::getInteger('character_id');
$location_id = HTTPContext::getInteger('location_id');
$broadcast_language_loc = HTTPContext::getInteger('broadcast_language_loc');
$broadcast_target = HTTPContext::getRawString('broadcast_target');
$recursively = HTTPContext::getRawString('recursively');

$plr = Request::getInstance()->getPlayer();
if ($plr->hasAccessTo(AccessConstants::MANUAL_EVENT_CREATOR_TOOL) && !empty($text_to_broadcast)) {

  //to be sure, 3 minutes, I hope this script will work not longer that 10 seconds.
  set_time_limit(3 * 60);

  /**
   * @author Wiktor Obrębski
   */

  $broadcastOptions = array(
    'mode' => $broadcast_target,
    'targetLanguage' => $broadcast_target == EventsBroadcaster::MODE_LANGUAGE_GROUP ?
      $broadcast_language : $broadcast_language_loc,
    'targetCharacter' => $character_id,
    'targetLocation' => $location_id,
    'recursively' => !empty($recursively),
  );

  $broadcaster = new EventsBroadcaster($plr, $broadcastOptions);

  $broadcaster->broadcast($text_to_broadcast);

  if ($broadcaster->affectedChars() == 0) {
    $message = "Can't find any valid target for this event.";
  } else {
    $message = $broadcaster->affectedChars() . ' chars affected by this event.';
  }
}

if (empty($text_to_broadcast)) {
  $message = "Text can't be empty";
}

redirect("manual_events", ["message" => urlencode($message)]);