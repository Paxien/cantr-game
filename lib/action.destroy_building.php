<?php

$buildingId = HTTPContext::getInteger('building'); 

if ($char->getLocation() == 0) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

try {
  $charLocation = Location::loadById($char->getLocation());
  $building = Location::loadById($buildingId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if ($building->getRegion() != $charLocation->getId()) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if (!$building->isDestroyable()) {
  CError::throwRedirectTag("char.buildings", "error_builiding_not_destroyable");
}

$rules = Parser::rulesToArray($building->getTypeRules());
$destroyableRules = Parser::rulesToArray($rules['destroyable'], ",>");
if (isset($destroyableRules['tools'])) {
  $tools = explode("/", $destroyableRules['tools']);
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
    CError::throwRedirect("char.buildings", "<CANTR REPLACE NAME=error_destruction_lack_tools TOOLS=$lackingToolsStr>");
  }
}

try {
  $project = $building->beginDestructionProject($char);
} catch (ProjectAlreadyExistsException $e) {
  CError::throwRedirectTag("char.buildings", "error_destruction_project_already_exists");
}

redirect("char.events");
