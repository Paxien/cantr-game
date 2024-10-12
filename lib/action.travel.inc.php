<?php

$connectionId = HTTPContext::getInteger('connection');

try {
  $initialLocation = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.description", "error_too_far_away");
}

if ($initialLocation->isVehicle()) { // vehicle
  try {
    $subject = LandVehicle::loadById($initialLocation->getId());
  } catch (DisallowedActionException $e) {
    CError::throwRedirectTag("char.description", $e->getMessage());
  } catch (InvalidArgumentException $e) {
    CError::throwRedirect("char.description", "You have tried to commit illegal action");
  }
} else {
  $subject = $char;
}

$db = Db::get();

if ($subject instanceof LandVehicle) {
  $stm = $db->prepare("SELECT id FROM travels WHERE type > 0 AND person = :subject");
  $stm->bindInt("subject", $subject->getId());
  $alreadyTravelling = $stm->executeScalar();
} else {
  $stm = $db->prepare("SELECT id FROM travels WHERE type = 0 AND person = :subject");
  $stm->bindInt("subject", $char->getId());
  $alreadyTravelling = $stm->executeScalar();
}

if ($alreadyTravelling) {
  CError::throwRedirect("char.events", "You are already travelling");
}

try {
  $connection = Connection::loadById($connectionId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.description", "error_too_far_away");
}

try {
  $travel = Travel::newInstance($subject, $connection, $char, $db);

  if ($travel->isVehicle()) {
    $travel->getVehicle()->tryDrive($char);
  }
} catch (DisallowedActionException $e) {
  CError::throwRedirectTag("char.description", $e->getMessage());
}

$chars = array();
if ($subject instanceof LandVehicle) {
  $stm = $db->prepare("SELECT id FROM chars WHERE location = :subject AND status = :active");
  $stm->bindInt("subject", $subject->getId());
  $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
  $stm->execute();
  $chars = $stm->fetchScalars();
} else {
  $chars[] = $char->getId();
}

if ($subject instanceof Character) {
  $stm = $db->prepare("UPDATE chars SET project = 0 WHERE id = :charId");
  $stm->bindInt("charId", $char->getId());
  $stm->execute();
}

// remove dragging of chars inside and participation in dragging
foreach ($chars as $charId) {
  try {
    $dragging = Dragging::loadByVictim(DraggingConstants::TYPE_HUMAN, $charId);
    $dragging->removeDragger($charId);
    $dragging->saveInDb();
  } catch (InvalidArgumentException $e) {
  }
}

// remove sign change projects
if ($travel->isVehicle()) {
  $vehicleId = $subject->getId();
  $signChangeProject = Project::locatedIn($travel->getStart())->type(ProjectConstants::TYPE_ALTERING_SIGN)->subtype($vehicleId)->find();
  if ($signChangeProject !== null) {
    $stm = $db->prepare("UPDATE chars SET project = 0 WHERE project = :projectId");
    $stm->bindInt("projectId", $signChangeProject->getId());
    $stm->execute();
    $signChangeProject->deleteFromDb();
  }
}

redirect("char.events");
