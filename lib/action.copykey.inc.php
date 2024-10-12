<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

try {
  $keyObject = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasInInventory($keyObject) || $keyObject->getType() != ObjectConstants::TYPE_KEY) {
  CError::throwRedirectTag("char.inventory", "error_copy_key_not_pocket");
}

if (!$char->getLocation()) {
  CError::throwRedirectTag("char.inventory", "error_copy_key_not_travelling");
}

$name = "<CANTR REPLACE NAME=project_copying_key KEYID={$keyObject->getSpecifics()}>";
$general = new ProjectGeneral($name, $character, $char->getLocation());
$type = new ProjectType(ProjectConstants::TYPE_MANUFACTURING, ObjectConstants::TYPE_KEY, 0, ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT, ProjectConstants::DIGGING_SLOTS_NOT_USE);
$requirement = new ProjectRequirement(ProjectConstants::DEFAULT_PROGRESS_PER_DAY, 'raws:iron>10;tools:file');
$output = new ProjectOutput(0, "objects.add:person>var>initiator,type>30,weight>10,setting>1,specifics>" . $keyObject->getSpecifics());
$project = new Project($general, $type, $requirement, $output);
$project->saveInDb();

redirect("char.inventory");
