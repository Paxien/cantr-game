<?php

include_once("func.expireobject.inc.php");

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');
$repairHours = HTTPContext::getInteger('repairhours');

$db = Db::get();
$stm = $db->prepare("SELECT o.id, o.person, o.expired_date, o.type, o.deterioration,
  o.weight, ot.unique_name, ot.skill, ot.repair_rate FROM objects o
  INNER JOIN objecttypes ot ON ot.id = o.type
  WHERE o.id = :objectId");
$stm->bindInt("objectId", $object_id);
$stm->execute();
$object_info = $stm->fetchObject();

$existingRepairProjects = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_REPAIRING)->subtype($object_id)->findIds();

if (count($existingRepairProjects) > 0) {
  CError::throwRedirectTag("char.inventory", "error_already_being_repaired");
}

// Making sure the character actually have the object
if (!$object_info || $object_info->expired_date > 0 || $object_info->repair_rate == 0 || $object_info->person != $character) {
  CError::throwRedirectTag("char.inventory", "error_cant_be_repaired");
}

/********* VERIFY CONDITIONS ********/
$fullRepair = ceil($object_info->deterioration / $object_info->repair_rate);

if ($repairHours == -1) {
  $repairHours = $fullRepair;
}

if ($repairHours > $fullRepair || $repairHours <= 0) {
  CError::throwRedirectTag("char.events", "error_repair_too_long");
}

/********* TURNS LEFT / NEEDED ******/
$turnsNeeded = $repairHours * (ProjectConstants::DEFAULT_PROGRESS_PER_DAY / GameDateConstants::HOURS_PER_DAY);

$projectName = "<CANTR REPLACE NAME=project_repairing> <CANTR REPLACE NAME=item_{$object_info->unique_name}_o>";
// Project subconstructors
$generalSub = new ProjectGeneral($projectName, $character, $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_REPAIRING, $object_info->id, $object_info->skill, 0, 8, 0);
$requirementSub = new ProjectRequirement($turnsNeeded, 'objectid:' . $object_info->id); // no requirements
$outputSub = new ProjectOutput(0, ''); // no weight; no result

$projectObject = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$projectObject->saveInDb();


redirect("char.inventory");
