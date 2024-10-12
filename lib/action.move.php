<?php

$target = HTTPContext::getInteger('target');

try {
  $fromLoc = Location::loadById($char->getLocation());
  $toLoc = Location::loadById($target);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

$translocation = new CharacterTranslocation($char, $fromLoc, $toLoc);
$shipTypes = Location::getShipTypeArray();

// outer lock is used only when translocation is between two ships, one docked to another
$checkOuterLock = in_array($fromLoc->getArea(), $shipTypes) && in_array($toLoc->getArea(), $shipTypes);

$translocation->setCheckNearness(true)->setCheckLocks($checkOuterLock, true)->setCheckCapacity(true);

try {
  $translocation->perform();
} catch (TooFarAwayException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
} catch (NoKeyToInnerLockException $e) {
  $keyLock = KeyLock::loadByLocationId($translocation->getInnerLocation()->getId());
  $keyLock->redirectToLockpicking(); // never returns
} catch (NoKeyToOuterLockException $e) {
  $keyLock = KeyLock::loadByLocationId($translocation->getOuterLocation()->getId());
  $keyLock->redirectToLockpicking(); // never returns
} catch (WeightCapacityExceededException $e) {
  CError::throwRedirectTag("char.events", "error_enter_max_weight");
} catch (PeopleCapacityExceededException $e) {
  CError::throwRedirectTag("char.events", "error_enter_max_people");
}

$from = urlencode("<CANTR LOCNAME ID=". $fromLoc->getId() .">");
$dest = urlencode("<CANTR LOCNAME ID=". $toLoc->getId() .">");

$charsInTargetLoc = $toLoc->getCharacterCount() - 1;

$reported = false;
if ($toLoc->getType() == LocationConstants::TYPE_VEHICLE) { // special event for moving from central area
  try {
    $vehicleType = ObjectType::loadById($toLoc->getArea());
    if ((new Category(ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS))->contains($vehicleType)) {
      // notify other people there
      Event::create(372, "ACTOR=$character START=" . $from . " DESTINATION=" . $dest)->inLocation($fromLoc)->except($char)->show();
      Event::create(373, "ACTOR=$character START=" . $from . " DESTINATION=" . $dest)->inLocation($toLoc)->except($char)->show();
      // notify actor
      Event::create(371, "COUNT=$charsInTargetLoc START=$from DESTINATION=$dest")->forCharacter($char)->show();
      $reported = true;
    }
  } catch (InvalidArgumentException $e) {}
} elseif ($fromLoc->getType() == LocationConstants::TYPE_VEHICLE) { // special event for moving from central area
  try {
    $vehicleType = ObjectType::loadById($fromLoc->getArea());
    if ((new Category(ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS))->contains($vehicleType)) {
      // notify other people there
      Event::create(375, "ACTOR=$character START=" . $from . " DESTINATION=" . $dest)->inLocation($fromLoc)->except($char)->show();
      Event::create(376, "ACTOR=$character START=" . $from . " DESTINATION=" . $dest)->inLocation($toLoc)->except($char)->show();
      // notify actor
      Event::create(374, "COUNT=$charsInTargetLoc START=$from DESTINATION=$dest")->forCharacter($char)->show();
      $reported = true;
    }
  } catch (InvalidArgumentException $e) {}
}

if (!$reported) {
  if ($fromLoc->getType() == LocationConstants::TYPE_OUTSIDE) { // special event for moving from central area
    // notify other people there
    Event::create(117, "ACTOR=$character START=" . $from . " DESTINATION=" . $dest)->inLocation($fromLoc)->except($char)->show();
    Event::create(118, "ACTOR=$character START=" . $from . " DESTINATION=" . $dest)->inLocation($toLoc)->except($char)->show();
    // notify actor
    Event::create(116, "COUNT=$charsInTargetLoc START=$from DESTINATION=$dest")->forCharacter($char)->show();
  } else {
    // notify other people there
    Event::create(120, "ACTOR=$character START=" . $from . " DESTINATION=" . $dest)->inLocation($fromLoc)->except($char)->show();
    Event::create(121, "ACTOR=$character START=" . $from . " DESTINATION=" . $dest)->inLocation($toLoc)->except($char)->show();
    // notify actor
    Event::create(119, "COUNT=$charsInTargetLoc START=$from DESTINATION=$dest")->forCharacter($char)->show();
  }
}

redirect("char.events");
