<?php

if (!$char->isTravelling()) {
  CError::throwRedirectTag("char.events", "error_turn_not_travelling");
}

try {
  $travel = Travel::loadByParticipant($char);
  if ($travel->isVehicle()) {
    $travel->getVehicle()->tryDrive($char); // throws DisallowedActionException if cant

    if (!$travel->getVehicle()->canTurnAround()) {
      throw new DisallowedActionException("error_cannot_turn_around");
    }
  }
} catch (DisallowedActionException $e) {
  CError::throwRedirectTag("char.events", $e->getMessage());
} catch (Exception $e) {
  Logger::getLogger("")->error("Illegal exception when turning around by $character: ". $e->getMessage());
  CError::throwRedirect("char.events", "Illegal input for turning around");
}

if ($travel->getWantedSpeed() > 0) {
	$travel->turnAround($character);
  $travel->saveInDb();
} else {
  CError::throwRedirectTag("char.events", "error_turn_without_speed");
}

redirect("char");
