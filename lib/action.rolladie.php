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

if (!$object->hasAccessToAction("rolladie") || !$object->getProperty("Die")) {
  CError::throwRedirect("char.inventory", "<CANTR REPLACE NAME=error_not_authorized>, tried to roll a dice");
}

$dieProperty = $object->getProperty("Die");
$numberOfSides = $dieProperty["numberOfSides"];

$dieResult = ceil(random_percent() * $numberOfSides);
Event::create(223, "OBJECT=" . $object->getId() . " ACTOR=" . $char->getId() . " RAND_OUTPUT=$dieResult")->forCharacter($char)->show();

$event = Event::create(222, "OBJECT=" . $object->getId() . " ACTOR=" . $char->getId() . " RAND_OUTPUT=$dieResult");
$event->nearCharacter($char)->andAdjacentLocations()->except($char)->show();

$decay_factor = 1 / 8;
import_lib("func.expireobject.inc.php");
usage_decay_object($object->getId(), $decay_factor);


redirect("char.events");
