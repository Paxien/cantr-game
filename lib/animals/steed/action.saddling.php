<?php

$animalId = HTTPContext::getInteger('animal_id');

$animalObject = DomesticatedAnimalObject::loadById($animalId);
if ($animalObject === null) {
  CError::throwRedirectTag("char.events", "error_no_animal");
}

if ($animalObject->getLocation() != $char->getLocation()) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if (!$animalObject->isLoyalTo($char)) {
  CError::throwRedirectTag("char.events", "error_saddling_no_owner");
}

try {
  $parentLocation = Location::loadById($animalObject->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if ($parentLocation->getType() != LocationConstants::TYPE_OUTSIDE) {
  CError::throwRedirectTag("char.events", "error_saddling_not_outside");

}

import_lib("func.rules.inc.php");
if (!objectHaveAccessToAction($animalId, "animal_saddling")) {
  CError::throwRedirectTag("char.events", "error_cant_be_saddled");
}

$alreadySaddling = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_SADDLING_STEED)->subtype($animalObject->getId())->exists();

if ($alreadySaddling != null) {
  CError::throwRedirectTag("char.events", "error_already_being_saddled");
}

$turnsNeeded = AnimalConstants::STEED_SADDLING_DAYS * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

$projectName = "<CANTR REPLACE NAME=project_saddling> ". $animalObject->getNameTag();

// Project subconstructors
$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_SADDLING_STEED, $animalObject->getId(), 33, 0, 2, 0); // 33 - animal husbandry
$requirementSub = new ProjectRequirement($turnsNeeded, "objects:saddle>1;objectid:$animalId");
$outputSub = new ProjectOutput( 0, '');

// create object itself
$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

Event::createPersonalEvent(346, "OBJECT=$animalId", $char->getId());
Event::createPublicEvent(347, "ACTOR=". $char->getId(). " OBJECT=$animalId",
  $char->getId(), Event::RANGE_SAME_LOCATION, array($char->getId()));


redirect("char.events");
