<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasInInventory($object)) {
  CError::throwRedirectTag("char.inventory", "error_instrument_not_held");
}


if (!$object->hasAccessToAction("tossacoin")) {
  CError::throwRedirect("char.inventory", "<CANTR REPLACE NAME=error_not_authorized>, tried to toss a coin");
}

$rand = random_percent();
if ($rand < 0.5) {
  $ownEventId = 220;
  $eventId = 218;
} else {
  $ownEventId = 221;
  $eventId = 219;
}

Event::create($ownEventId, "OBJECT=" . $object->getId() . " ACTOR=" . $char->getId())->forCharacter($char)->show();

$event = Event::create($eventId, "OBJECT=" . $object->getId() . " ACTOR=" . $char->getId());
$event->nearCharacter($char)->andAdjacentLocations()->except($char)->show();

$decay_factor = 1 / 8;
import_lib("func.expireobject.inc.php");
usage_decay_object($object->getId(), $decay_factor);


redirect("char.events");
