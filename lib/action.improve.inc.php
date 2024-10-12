<?php

include_once("func.getdirection.inc.php");

// SANITIZE INPUT
$connectionId = HTTPContext::getInteger('connection');
$actionType = $_REQUEST['actionType'];

$matched = preg_match("/([a-z]+)_(\d+)/", $actionType, $actionMatches);
list(, $action, $toType) = $actionMatches;

if (!$matched) {
  CError::throwRedirect("char.description", "You've tried to commit illegal action");
}

try {
  $connection = Connection::loadById($connectionId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("char.description", "Road data is invalid");
}

if (!in_array($char->getLocation(), array($connection->getStart(), $connection->getEnd()))) {
  CError::throwRedirectTag("char.description", "error_improve_not_location");
}

$targetType = ConnectionType::loadById($toType);

if ($action == "improve") {

  if (!$connection->canBeImprovedTo($targetType)) {
    CError::throwRedirect("char.description", "You've tried to commit illegal action");
  }

  if (!$targetType->isPrimaryType()) {
    $part = $connection->getConnectionPartImprovableTo($targetType);
    if ($connection->isBeingImproved($part)) {
      CError::throwRedirectTag("improve&connection=$connectionId", "error_already_improvement");
    }
  }

  $nextTypeTag = $targetType->getName();
  $destination = $connection->getOppositeLocation($char->getLocation());
  $direction = $connection->getDirectionFromLocation($char->getLocation());
  $projectName = "<CANTR REPLACE NAME=improve_road DEST=$destination TYPE=$nextTypeTag> (".getdirectionname($direction).")";

  $raws = $connection->getRawsToImproveTo($targetType);
  $days = $connection->getDaysToImproveTo($targetType);

  $requirements = "raws:" . Parser::arrayToRules($raws, ",>") . ";days:$days;tools:shovel";
  $turnsNeeded = ProjectConstants::DEFAULT_PROGRESS_PER_DAY * $days;

  $generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
  $typeSub = new ProjectType(ProjectConstants::TYPE_IMPROVING_ROADS, $connection->getId(), 0,
    ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT,
      ProjectConstants::DIGGING_SLOTS_NOT_USE);
  $requirementSub = new ProjectRequirement($turnsNeeded, $requirements);
  $outputSub = new ProjectOutput(0, $connection->getId() . ":" . $targetType->getId());
  
  $project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
  $project->saveInDb();

} elseif ($action == "repair") {
  $repairedPart = $connection->getConnectionPartWithType($targetType);

  if ($repairedPart === null) {
    CError::throwRedirect("improve&connection=$connectionId", "You've tried to commit illegal action");
  }

  if ($repairedPart->getDeterioration() == 0) {
    CError::throwRedirectTag("improve&connection=$connectionId", "error_road_not_damaged");
  }

  $projectExists = Project::locatedIn($char->getLocation())->type(ProjectConstants::TYPE_REPAIRING_ROAD)
    ->subtype($connection->getId())->result($connection->getId() . ":" . $targetType->getId())->exists();
  if ($projectExists > 0) {
    CError::throwRedirectTag("improve&connection=$connectionId", "error_road_project_already_exists");
  }

  $destination = $connection->getOppositeLocation($char->getLocation());
  $projectName = "<CANTR REPLACE NAME=project_repairing_road ROAD=" . $targetType->getName() . " DEST=$destination>";
  $deterRatio = $repairedPart->getDeterioration() / 10000;
  $buildDays = $connection->getDaysToImproveTo($targetType);
  $raws = $connection->getRawsToImproveTo($targetType);
  $raws = Pipe::from($raws)->map(function ($amount) use ($deterRatio) { // repairs are cheaper
    return round($amount * $deterRatio * ConnectionConstants::REPAIR_TO_IMPROVEMENT_COST);
  })->toArray();

  $turnsNeeded = $buildDays * $deterRatio * ConnectionConstants::REPAIR_TO_IMPROVEMENT_TIME * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

  $raws = Parser::arrayToRules($raws, ",>");
  
  // Project subconstructors
  $generalSub = new ProjectGeneral($projectName, $character, $char->getLocation());
  $typeSub = new ProjectType(ProjectConstants::TYPE_REPAIRING_ROAD, $connection->getId(), 0,
    ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT,
      ProjectConstants::DIGGING_SLOTS_NOT_USE);
  $requirementSub = new ProjectRequirement($turnsNeeded, "raws:{$raws}");
  $outputSub = new ProjectOutput(0, $connection->getId() . ":" . $targetType->getId());

  $project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
  $project->saveInDb();
} elseif ($action == "destroy") {

  $destroyedPart = $connection->getConnectionPartWithType($targetType);

  if ($destroyedPart === null || !$targetType->isDestroyable()) {
    CError::throwRedirect("improve&connection=$connectionId", "You've tried to commit illegal action");
  }

  $projectExists = Project::locatedIn($char->getLocation())
    ->type(ProjectConstants::TYPE_DESTROYING_ROAD)->subtype($connection->getId())
    ->result($connection->getId() . ":" . $targetType->getId())->exists();
  if ($projectExists) {
    CError::throwRedirectTag("improve&connection=$connectionId", "error_road_project_already_exists");
  }
  
  $destination = $connection->getOppositeLocation($char->getLocation());
  $projectName = "<CANTR REPLACE NAME=project_destroying_road ROAD=" . $targetType->getName() . " DEST=$destination>";
  $deterRatio = 1 - ($destroyedPart->getDeterioration() / 10000);
  $buildDays = $connection->getDaysToImproveTo($targetType);
  $turnsNeeded = $buildDays * $deterRatio * ConnectionConstants::DESTRUCTION_TO_IMPROVEMENT_TIME * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
  
  // Project subconstructors
  $generalSub = new ProjectGeneral($projectName, $character, $char->getLocation());
  $typeSub = new ProjectType(ProjectConstants::TYPE_DESTROYING_ROAD, $connection->getId(), 0,
    ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT,
      ProjectConstants::DIGGING_SLOTS_NOT_USE);
  $requirementSub = new ProjectRequirement($turnsNeeded, "tools:pickaxe");
  $outputSub = new ProjectOutput(0, $connection->getId() . ":" . $targetType->getId());

  $project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
  $project->saveInDb();
} else {
  CError::throwRedirect("improve&connection=$connectionId", "You've tried to commit illegal action");
}

redirect("char");
