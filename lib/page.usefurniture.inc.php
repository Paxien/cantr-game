<?php

// SANITIZE INPUT
$objectId = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

if ($char->isBusy()) {
  CError::throwRedirectTag("char.objects", "error_no_rest_while_working");
}
if (!$char->isInSameLocationAs($object)) {
  CError::throwRedirectTag("char.objects", "error_rest_place_not_same_location");
}
if ($object->isInUse()) {
  CError::throwRedirectTag("char.objects", "error_furniture_in_use");
}

preg_match("/energy:([0-9]+)/", $object->getRules(), $matches);
$energyIncrease = $matches[1];

preg_match("/maxpeople:([0-9]+)/", $object->getRules(), $matches);
$maxUsers = $matches[1];

$projectName = "<CANTR REPLACE NAME=project_resting OBJECT=" . $object->getUniqueName() . ">";
$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_RESTING, $object->getId(),
  StateConstants::NONE, 0, $maxUsers, 0);
$requirementSub = new ProjectRequirement(0, "");
$outputSub = new ProjectOutput(0, $energyIncrease);

$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

$object->setSpecifics((string)$project->getId());
$object->saveInDb();

$char->setProject($project->getId());
$char->saveInDb();

redirect("char.objects");

