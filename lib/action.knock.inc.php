<?php

// SANITIZE INPUT
$building = HTTPContext::getInteger('building');
$targetId = HTTPContext::getInteger('target');

if (!$targetId) {
  $targetId = $building;
}

try {
  $target = Location::loadById($targetId);
  $charLocation = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if (!($target->getRegion() == $charLocation->getId()) && !($charLocation->getRegion() == $target->getId())) {
  CError::throwRedirectTag("char.events", "error_knock_not_at_location");
}

$building = $target->getId();

if (Limitations::getLims($char->getId(), Limitations::TYPE_KNOCK, $building) >= _LIMITATION_MAX_KNOCK) {
  CError::throwRedirectTag("char.buildings", "error_knock_limit_exceeded");
}
Limitations::addLim($char->getId(), Limitations::TYPE_KNOCK, Limitations::dhmstoc(0, 1, 0, 0), $building);

$destName = urlencode("<CANTR LOCNAME ID=" . $target->getId() . ">");
$fromName = urlencode("<CANTR LOCNAME ID=" . $char->getLocation() . ">");

if ($target->getType() == LocationConstants::TYPE_VEHICLE) {
  Event::create(13, "PLACE=$destName")->forCharacter($char)->show();
  $eventThisSide = Event::create(11, "ACTOR=$character PLACE=$destName");
  $eventOtherSide = Event::create(12, "PLACE=$fromName");
} else {
  Event::create(16, "PLACE=$destName")->forCharacter($char)->show();
  $eventThisSide = Event::create(14, "ACTOR=$character PLACE=$destName");
  $eventOtherSide = Event::create(15, "PLACE=$fromName");
}

$eventThisSide->inLocation($char->getLocation())->except($char)->show();
$eventOtherSide->inLocation($target->getId())->except($char)->show();

redirect("char");
