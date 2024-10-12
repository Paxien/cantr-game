<?php

// SANITIZE INPUT
$newspeed = HTTPContext::getInteger('newspeed');

if ($newspeed > 100) {
  CError::throwRedirectTag("char.description", "error_invalid_speed_too_fast");
}

if ($newspeed < 0) {
  CError::throwRedirectTag("char.description", "error_invalid_speed_too_slow");
}

try {
  $travel = Travel::loadByParticipant($char);
  if ($travel->isVehicle()) {
    $travel->getVehicle()->tryDrive($char);
  }
} catch (DisallowedActionException $e) {
  CError::throwRedirectTag("char.description", $e->getMessage());
} catch (Exception $e) {
  CError::throwRedirect("char.description", "Invalid action: ". $e->getMessage());
}

$travel->setWantedSpeed($travel->getMaxSpeed() * $newspeed/100);
$travel->saveInDb();

redirect("char.description");

