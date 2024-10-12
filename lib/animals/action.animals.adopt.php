<?php

$object_id = HTTPContext::getInteger('object_id');


$animalObject = DomesticatedAnimalObject::loadFromDb($object_id);

if (!$animalObject || !$char->isInSameLocationAs($animalObject)) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

$isAlreadyBeingAdopted = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_ADOPTING_ANIMAL)->subtype($animalObject->getId())->exists();
if ($isAlreadyBeingAdopted) {
  CError::throwRedirectTag("char.objects", "error_animal_being_adopted");
}

if ($animalObject->isLoyalTo($char)) {
  CError::throwRedirectTag("char.objects", "error_animal_loyal_to_you");
}


$tameRules = $animalObject->getTameRulesString();
$rulesArray = Parser::rulesToArray($tameRules);

$turnsNeeded = (array_key_exists('days', $rulesArray) ? $rulesArray['days'] : 1) * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

$projectName = "<CANTR REPLACE NAME=project_adopting> ". $animalObject->getNameTag();
if ($animalObject->getLoyalTo() > 0) { // project should mention who is the current owner
  $projectName .= " <CANTR REPLACE NAME=project_loyal_to OWNER=". $animalObject->getLoyalTo() .">";
}

$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_ADOPTING_ANIMAL,
  $animalObject->getId(), StateConstants::HUSBANDRY, 0, 2, 0);
$requirementSub = new ProjectRequirement($turnsNeeded, $tameRules);
$outputSub = new ProjectOutput(0, '', 0);

// create object itself
$projectObject = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$projectObject->saveInDb();

$projectName = urlencode($projectName);
Event::create(298, "PROJECT=$projectName")->forCharacter($char)->show();
Event::create(299, "PROJECT=$projectName ACTOR=$character")->nearCharacter($char)->except($char)->show();

redirect("char.events");
