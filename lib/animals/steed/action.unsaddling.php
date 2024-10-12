<?php

$animalId = HTTPContext::getInteger('target');

try {
  $animal = LandVehicle::loadById($animalId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_no_animal");
}

if ($animal->getRegion() != $char->getLocation()) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

$isAlreadyBeingUnsaddled = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_UNSADDLING_STEED)->subtype($animal->getId())->exists();
if ($isAlreadyBeingUnsaddled) {
  CError::throwRedirectTag("char.events", "error_already_being_unsaddled");
}

$db = Db::get();
$stm = $db->prepare("SELECT id FROM animal_domesticated
  WHERE from_animal = 0 AND from_object = 0 AND from_location = :locationId");
$stm->bindInt("locationId", $animalId);
$isAnimal = $stm->executeScalar();
if (!$isAnimal) {
  CError::throwRedirectTag("char.events", "error_cant_be_saddled");
}

if (!$animal->getLocation()->isEmpty()) {
  CError::throwRedirectTag("char.events", "error_steed_not_empty");
}

if (!$animal->isLoyalTo($char)) {
  CError::throwRedirectTag("char.events", "error_only_owner_can_unsaddle");
}

$days = AnimalConstants::STEED_UNSADDLING_DAYS;

$turnsNeeded = $days * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
$projectName = "<CANTR REPLACE NAME=project_unsaddling> <CANTR LOCNAME ID=$animalId>";

// Project subconstructors
$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_UNSADDLING_STEED, $animal->getId(), 33, 0, 0, 0); // 33 - animal husbandry
$requirementSub = new ProjectRequirement($turnsNeeded, "locationid:$animalId");
$outputSub = new ProjectOutput( 0, '');

// create object itself
$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

Event::createPersonalEvent(348, "STEED=$animalId", $char->getId());
Event::createPublicEvent(349, "ACTOR=". $char->getId(). " STEED=$animalId",
  $char->getId(), Event::RANGE_SAME_LOCATION, array($char->getId()));


redirect("char.events");
