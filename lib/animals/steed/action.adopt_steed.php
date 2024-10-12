<?php

$steedId = HTTPContext::getInteger('vehicle');

try {
  $steed = LandVehicle::loadById($steedId);
} catch (InvalidArgumentException $e) {
  //error
  CError::throwRedirect("char.events", "failure");
}

if ($char->getLocation() != $steed->getLocation()->getRegion()) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

$isAlreadyBeingAdopted = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_ADOPTING_STEED)->subtype($steed->getId())->exists();
if ($isAlreadyBeingAdopted) {
  CError::throwRedirectTag("char.objects", "error_animal_being_adopted");
}

if ($steed->isLoyalTo($char)) {
  CError::throwRedirectTag("char.objects", "error_animal_loyal_to_you");
}

$db = Db::get();
$stm = $db->prepare("SELECT tame_rules FROM animal_domesticated_types
  WHERE of_object_type = :objectType"); // small todo - better to have it in steed
$stm->bindInt("objectType", $steed->getVehicleType());
$tameRules = $stm->executeScalar();
$rulesArray = Parser::rulesToArray($tameRules);

$turnsNeeded = (array_key_exists('days', $rulesArray) ? $rulesArray['days'] : 1) * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

$projectName = "<CANTR REPLACE NAME=project_adopting> <CANTR LOCNAME ID=". $steed->getId() .">";
// name contains the current owner

$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_ADOPTING_STEED,
  $steed->getId(), StateConstants::HUSBANDRY, 0, 2, 0);
$requirementSub = new ProjectRequirement($turnsNeeded, $tameRules);
$outputSub = new ProjectOutput(0, '', 0);

// create object itself
$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

$projectName = urlencode($projectName);
Event::create(298, "PROJECT=$projectName")->forCharacter($char)->show();
Event::create(299, "PROJECT=$projectName ACTOR=$character")->nearCharacter($char)->except($char)->show();

redirect("char.events");
