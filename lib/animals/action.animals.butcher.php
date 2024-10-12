<?php

$cleaverId = $_POST['object_id'];
$animalId = $_POST['animal_id'];

try {
  $cleaver = CObject::loadById($cleaverId);
  $animalObject = DomesticatedAnimalObject::loadFromDb($animalId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$cleaver->hasAccessToAction("animal_butcher")) {
  CError::throwRedirectTag("char.inventory", "error_action_butcher_not_possible");
}

if (!$animalObject) {
  CError::throwRedirect("char.inventory", "wrong pack id (not an animal)");
}

if (!$char->isInSameLocationAs($animalObject)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

$alreadyBeingButchered = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_BUTCHERING_ANIMAL)->subtype($animalObject->getId())->exists();

if ($alreadyBeingButchered) {
  CError::throwRedirectTag("char.inventory", "error_animal_already_butchered");
}

$butcherRaws = $animalObject->getRawPoolArray("butchering_raws");

if (empty($butcherRaws)) {
  CError::throwRedirectTag("char.inventory", "error_animal_cant_butcher");
}

$maxDays = 0;
foreach ($butcherRaws as $rawName => $raw) {
  $maxDays = max($maxDays, $raw['amount'] / $raw['dailyHarvest']);
}
$turnsNeeded = $maxDays * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

$projectName = "<CANTR REPLACE NAME=project_slaughtering> " . $animalObject->getNameTag();
if ($animalObject->getLoyalTo()) {
  $projectName .= " <CANTR REPLACE NAME=project_loyal_to OWNER=" . $animalObject->getLoyalTo() . ">";
}

// Project subconstructors
$generalSub = new ProjectGeneral($projectName, $character, $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_BUTCHERING_ANIMAL, $animalObject->getId(), StateConstants::HUSBANDRY, 0, 8, 0);
$requirementSub = new ProjectRequirement($turnsNeeded, '');
$outputSub = new ProjectOutput(0, '');

// create object itself
$projectObject = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$projectObject->saveInDb();

$projectName = urlencode($projectName);
Event::create(298, "PROJECT=$projectName")->forCharacter($char)->show();
Event::create(299, "ACTOR=$character PROJECT=$projectName")->nearCharacter($char)->except($char)->show();

redirect("char.inventory");
