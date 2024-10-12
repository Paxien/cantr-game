<?php

$targetId = HTTPContext::getInteger('target_id');
$toolId = HTTPContext::getInteger('object_id');
$description = $_REQUEST['description'];

$description = TextFormat::withoutNewlines($description);

if ($char->getLocation() == 0) {
  CError::throwRedirectTag("char.inventory", "error_not_while_travel");
}

try {
  $tool = CObject::loadById($toolId);
  $describedObject = CObject::loadById($targetId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasWithinReach($describedObject)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasInInventory($tool)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

// check if object is describable by tool $toolId
$describedObjectRules = Parser::rulesToArray($describedObject->getRules());

$requireMachines = true;
$isDescribable = false;
if (isset($describedObjectRules['describable'])) {
  $describable = Parser::rulesToArray($describedObjectRules['describable'], ",>");
  if (isset($describable['bytool'])) {
    $toolsList = explode("/", $describable['bytool']);
    $isDescribable = in_array($tool->getName(), $toolsList);
  }
  if (isset($describable['ignoremachines'])) {
    $requireMachines = false;
  }
}

import_lib("func.rules.inc.php");
if (!$isDescribable || !$tool->hasAccessToAction("changeobjdesc")) {
  CError::throwRedirectTag("char.inventory", "error_not_describable");
}

$describingProjectExists = Project::locatedIn($char->getLocation())->type(ProjectConstants::TYPE_DESC_OBJECT_CHANGE)
  ->subtype($describedObject->getId())->exists();

if ($describingProjectExists) {
  CError::throwRedirectTag("char.inventory", "error_object_already_being_described");
}

$buildConditions = Parser::rulesToArray($describedObject->getBuildConditions());
if ($requireMachines && isset($buildConditions['hasobject'])) {
  $requiredObjects = explode (',', $buildConditions['hasobject']);
  foreach ($requiredObjects as $requiredObject) {
    $machineExists = CObject::locatedIn($char->getLocation())->name($requiredObject)->exists();
    if (!$machineExists) {
      CError::throwRedirectTag("char.inventory", "error_build_only_object OBJECT=". urlencode($requiredObject));
    }
  }
}

if (!Descriptions::isDescriptionAllowed(Descriptions::TYPE_OBJECT, $description)) {
  CError::throwRedirectTag("char.inventory", "error_description_too_long");
}


$buildReq = Parser::rulesToArray($describedObject->getBuildRequirements());

// project time
if (isset($buildReq['days'])) {
  $daysNeeded = $buildReq['days'] * ProjectConstants::CHANGE_DESC_FRACTION_OF_MANU_TIME;
} else {
  $daysNeeded = ProjectConstants::CHANGE_DESC_DEFAULT_DAYS;
}

$genericObjectName = TagUtil::getGenericTagForObjectName($describedObject->getUniqueName());
$projectName = "<CANTR REPLACE NAME=project_object_new_description> <CANTR REPLACE NAME=$genericObjectName>";
$turnsNeeded = ProjectConstants::DEFAULT_PROGRESS_PER_DAY * $daysNeeded;
$reqNeeded = "objectid:". $targetId .";tools:". implode(",", $toolsList);
$skill = $describedObject->getProductionSkill();

$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_DESC_OBJECT_CHANGE, $targetId, $skill, 0, 4, 0);
$requirementSub = new ProjectRequirement($turnsNeeded, $reqNeeded);
$outputSub = new ProjectOutput( 0, "", 0, $description);

$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

redirect("char.inventory");
