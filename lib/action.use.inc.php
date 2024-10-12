<?php
include_once("func.projectsetup.inc.php");

$amount = HTTPContext::getInteger('amount');
$machine = HTTPContext::getInteger('machine');
$object = HTTPContext::getInteger('object');
$resource_allocation = $_REQUEST['resource_allocation'];

if (!Validation::isPositiveInt($amount)) {
  CError::throwRedirect("char.objects", "You can only enter a normal value as amount.");
}

try {
  $objectData = CObject::loadById($object);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("char.objects", "No object");
}

try {
  $machineProject = MachineProject::loadById($machine);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("char.objects", "No machine type");
}


$isLegalProject = $machineProject->getMachineType()->getId() == $objectData->getType();
if (!$isLegalProject) {
  CError::throwRedirectTag("char.objects", "error_not_authorized");
}

if (!$char->hasWithinReach($objectData)) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

if ($objectData->isInUse()) {
  CError::throwRedirectTag("char.objects", "error_machine_in_use");
}

$resultAmount = $machineProject->getResultAmount();
if ($amount > 80 * $resultAmount) {
  CError::throwRedirect("char.objects", 'You entered a number that exceeds the maximum amount.');
}

$multiplier = $amount / $resultAmount;
$resultAmount = floor($resultAmount * $multiplier);


$requirements = Parser::rulesToArray($machineProject->getRequirementsString());
if (array_key_exists("raws", $requirements)) {
  $rawsReq = Parser::rulesToArray($requirements["raws"], ",>");
  foreach ($rawsReq as $rawName => &$amount) {
    $amount = max(floor($amount * $multiplier), 1);
  }
  $requirements["raws"] = Parser::arrayToRules($rawsReq, ",>");
}

if (array_key_exists("objects", $requirements)) {
  $objectsReq = Parser::rulesToArray($requirements["objects"], ",>");
  foreach ($objectsReq as $objName => &$amount) {
    $amount = max(floor($amount * $multiplier), 1);
  }
  $requirements["objects"] = Parser::arrayToRules($objectsReq, ",>");
}

if (array_key_exists("days", $requirements)) {
  $requirements["days"] = $requirements["days"] * $multiplier;
}

$multipliedRequirements = Parser::arrayToRules($requirements);
if (StringUtil::contains($machineProject->getRequirementsString(), "ignorerawtools")) {
  $multipliedRequirements .= ";ignorerawtools";
}

$turns = $requirements["days"] * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
if ($turns < 1) {
  $turns = 1;
}

$projectResult = $machineProject->getResultRawTypeId() . ":" . $resultAmount;

$generalSub = new ProjectGeneral($machineProject->getName(), $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_GATHERING, $machineProject->getResultRawTypeId(),
  $machineProject->getSkill(), $machineProject->getProgressMethod(), $machineProject->getMaxParticipants(), ProjectConstants::DIGGING_SLOTS_NOT_USE);
$requirementSub = new ProjectRequirement($turns, $multipliedRequirements);
$outputSub = new ProjectOutput(0, $projectResult);

$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();
$id = $project->getId();

// if object isn't portable then make it "in use". If project is automatic set "in use"
// for portable machines too (to avoid infinite projects on a single machine)
if ($objectData->getSetting() == ObjectConstants::SETTING_FIXED || $project->getWayOfProgression() != ProjectConstants::PROGRESS_MANUAL) {
  $objectData->setSpecifics((string)$project->getId());
  $objectData->saveInDb();
}

switch ($resource_allocation) {
  case "regardless" :
    $join_project = automatic_add_to_project($id, $char->getId(), true);
    if ($join_project) {
      automatic_join_project($id, $char->getId());
    }
    redirect("char.events");
    break;
  case "full" :
    $join_project = automatic_add_to_project($id, $char->getId(), false);
    if ($join_project) {
      automatic_join_project($id, $char->getId());
    }
    redirect("char.events");
    break;
  default :
    redirect("char.inventory");
}
