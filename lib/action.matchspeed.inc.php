<?php

// SANITIZE INPUT
$to = HTTPContext::getInteger('to');

try {
  $victim = Character::loadById($to);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_person_not_here");
}

if (!$char->isNearTo($victim)) {
  CError::throwRedirectTag("char.events", "error_person_not_here");
}

try {
  $travelActor = Travel::loadByParticipant($char);
  $travelVictim = Travel::loadByParticipant($victim);
} catch (DisallowedActionException $e) {
  CError::throwRedirectTag("char.events", $e->getMessage());
} catch (Exception $e) {
  CError::throwRedirect("char.events", "Error when trying to retrieve travel data: ". $e->getMessage());
}


try {
  if ($travelActor->isVehicle()) {
    $travelActor->getVehicle()->tryDrive($char);
  }
} catch (DisallowedActionException $e) {
  CError::throwRedirectTag("char.people", $e->getMessage());
}

// Check whether they're really on the same road; it was already checked but who cares
if ($travelActor->getConnectionId() != $travelVictim->getConnectionId()) {
    CError::throwRedirectTag("char.events", "error_person_not_here");
}

// Update speed depending on victim's speed
$travelActor->setWantedSpeed($travelVictim->getSpeed());

// turn around when victim is moving in opposite direction
if ($travelActor->getDestination() != $travelVictim->getDestination()) {
  $travelActor->turnAround($character);
}

$travelActor->saveInDb();

redirect("char.events");

