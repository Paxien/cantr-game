<?php

$objectId = HTTPContext::getInteger('object_id');

$instrumentObject = CObject::loadById($objectId); // TODO

$isBigInstrument = ($instrumentObject->getSetting() == ObjectConstants::SETTING_FIXED && $char->isInSameLocationAs($instrumentObject));
if (!$char->hasInInventory($instrumentObject) && !$isBigInstrument) {
  CError::throwRedirectTag("char.events", "error_instrument_not_held");
}

if ($instrumentObject->getObjectCategory()->getId() != ObjectConstants::OBJCAT_INSTRUMENTS) {
  CError::throwRedirect("char.events", "<CANTR REPLACE NAME=error_not_authorized>, page=$page, " .
    "trying to play on item that isn't instrument.");
}

Event::create(161, "INSTRUMENT=$objectId")->forCharacter($char)->show();
Event::create(162, "INSTRUMENT=$objectId ACTOR=$character")->nearCharacter($char)->andAdjacentLocations()->except($char)->show();

$decayFactor = 1 / 8;

require_once("func.expireobject.inc.php");
usage_decay_object($objectId, $decayFactor);
redirect("char");
