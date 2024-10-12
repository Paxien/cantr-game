<?php

// SANITIZE INPUT
$to = HTTPContext::getInteger('to'); // character in NDS to heal

// ERRORS CHECK
try {
  $healedChar = Character::loadById($to);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

// same location
if (!$char->isInSameLocationAs($healedChar)) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if ($char->isBusy()) {
  CError::throwRedirectTag("char.events", "error_cannot_heal_already_working");
}

// healing project already there
$healingProjectExists = Project::locatedIn($char->getLocation())->type(ProjectConstants::TYPE_HEAL_NEAR_DEATH)->subtype($to)->exists();
if ($healingProjectExists) {
  CError::throwRedirectTag("char.events", "error_target_already_healed_there");
}

if ($healedChar->getNearDeathState() != CharacterConstants::NEAR_DEATH_NOT_HEALED) {
  CError::throwRedirectTag("char.events", "error_target_not_near_death");
}

if (!$char->isAlive()) {
  CError::throwRedirectTag("char.events", "error_target_dead_char");
}

// ACCEPTED

$turnsNeeded = floor(ProjectConstants::NEAR_DEATH_HEAL_PROJECT_DAYS * ProjectConstants::DEFAULT_PROGRESS_PER_DAY);
$projectName = "<CANTR REPLACE NAME=project_healing_near_death VICTIM=$to>"; // translate todo
$reqNeeded = "days:". ProjectConstants::NEAR_DEATH_HEAL_PROJECT_DAYS;

$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_HEAL_NEAR_DEATH, $to, 0, 0, 4, 0);
$requirementSub = new ProjectRequirement($turnsNeeded, $reqNeeded);
$outputSub = new ProjectOutput(0, "");

$healingProject = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$healingProject->saveInDb();

$char->setProject($healingProject->getId());
$char->saveInDb();

// start healing event for medic
Event::createPersonalEvent(304, "VICTIM=$to", $char->getId());
// start healing event for victim
Event::createPersonalEvent(305, "ACTOR=$character", $to);
// start healing event for observers
Event::createPublicEvent(306, "ACTOR=$character VICTIM=$to", $character, Event::RANGE_NEAR_LOCATIONS, array($character, $to));

redirect("char.events");

