<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

try {
  $sealObject = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasInInventory($sealObject) || ($sealObject->getType() != ObjectConstants::TYPE_SEAL)) {
  CError::throwRedirectTag("char.inventory", "error_copy_seal_not_pocket");
}

if (!$char->getLocation()) {
  CError::throwRedirectTag("char.inventory", "error_copy_seal_not_travelling");
}

$turnsleft = 4 * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
$projectName = "<CANTR REPLACE NAME=project_copy_seal SEAL={$sealObject->getSpecifics()}>";
$requirements = "raws:iron>120,nickel>25,wood>75;tools:hammer,chisel";
$result = "objects.add:person>var>initiator,type>212,weight>220,setting>1,specifics>" . $sealObject->getSpecifics();
$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_MANUFACTURING, ObjectConstants::TYPE_SEAL, StateConstants::NONE, 0, ProjectConstants::PARTICIPANTS_NO_LIMIT, 0);
$requirementSub = new ProjectRequirement($turnsleft, $requirements);
$outputSub = new ProjectOutput(0, $result);

$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

redirect("char.inventory");
