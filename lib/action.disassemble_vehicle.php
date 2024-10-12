<?php

$vehicleId = HTTPContext::getInteger('target');

try {
  $vehicle = Location::loadById($vehicleId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if (($char->getLocation() == 0) || ($vehicle->getRegion() != $char->getLocation())) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if (!$vehicle->isDisassemblable()) {
  CError::throwRedirectTag("char.buildings", "error_not_disassemblable");
}

if (!$vehicle->isEmpty()) {
  CError::throwRedirectTag("char.buildings", "error_disassembling_vehicle_not_empty");
}


$rules = Parser::rulesToArray($vehicle->getObjectType()->getRules());
$disassemblingRules = Parser::rulesToArray($rules['disassemblable'], ",>");

// no project in THE SAME location

$alreadyBeingDisassembled = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_DISASSEMBLING_VEHICLE)->subtype($vehicle->getId())->exists();
if ($alreadyBeingDisassembled > 0) {
  CError::throwRedirectTag("char.buildings", "error_vehicle_disassembling_project_already_exists");
}

$projectName = "<CANTR REPLACE NAME=project_disassembling_vehicle> ".
  "<CANTR LOCDESC ID=".$vehicle->getId() ."> <CANTR LOCNAME ID=". $vehicle->getId() .">";

if (isset($disassemblingRules['tools'])) {
  $tools = explode("/", $disassemblingRules['tools']);
  $hasTool = ObjectHandler::getObjectArrayByNameInInventory($tools, $char->getId());

  $lackingTools = array_keys( // return its name
    array_filter($hasTool, function($a) { // for all lacking tools
      return $a === false;
    })
  );
  
  if (count($lackingTools) > 0) {
    $lackingToolsStr = urlencode(implode(", ", array_map(function($item) {
      return "<CANTR REPLACE NAME=item_". $item ."_o>";
    }, $lackingTools)));
    CError::throwRedirect("char.buildings", "<CANTR REPLACE NAME=error_vehicle_disassembling_lack_tools TOOLS=$lackingToolsStr>");
  }
  $reqNeeded = "tools:". implode(",", $tools);
} else {
  $reqNeeded = "";
}

// event for actor
Event::createPersonalEvent(351, "VEHICLE=". $vehicle->getId(), $char->getId());
// event for people outside
Event::createPublicEvent(352, "ACTOR=". $char->getId() ." VEHICLE=". $vehicle->getId(),
  $char->getId(), Event::RANGE_NEAR_LOCATIONS, array($char->getId()));


$daysNeeded = $disassemblingRules['days'];
if (empty($daysNeeded)) {
  $daysNeeded = ProjectConstants::VEHICLE_DISASEMBLING_DEFAULT_DAYS;
}

$turnsNeeded = $daysNeeded * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_DISASSEMBLING_VEHICLE, $vehicle->getId(), 0,
  ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT,
    ProjectConstants::DIGGING_SLOTS_NOT_USE);
$requirementSub = new ProjectRequirement($turnsNeeded, $reqNeeded);
$outputSub = new ProjectOutput( 0, "");

$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

redirect("char.events");
