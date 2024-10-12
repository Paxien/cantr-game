<?php

$new_desc = $_REQUEST['new_desc'];

try {
  if (!Validation::isPositiveInt($char->getLocation())) {
    throw new InvalidArgumentException("Location shouldn't be 0");
  }
  $building = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("page.description", "error_too_far_away>");
}

if (!$building->isBuilding()) { // if is not a building
  CError::throwRedirect("page.description", "<CANTR REPLACE NAME=error_not_authorized> - trying to add description for a non-building");
}


$lock = KeyLock::loadByLocationId($building->getId());
$hasKey = $lock->hasId() && $lock->hasKey($char->getId()); // when lock exists and char has a key

// already exists description change project
$projectAlreadyExists = Project::locatedIn($building)->type(ProjectConstants::TYPE_DESC_BUILDING_CHANGE)->exists();

if (!$hasKey && $projectAlreadyExists) { // when you don't have a key then you'd have to create another project, but only one is allowed
  CError::throwRedirectTag("char.description", "error_already_desc_change_project");
}

if (!Descriptions::isDescriptionAllowed(Descriptions::TYPE_BUILDING, $new_desc)) {
  CError::throwRedirect("char.description", "error_description_too_long");
}

if ($hasKey) { // instant change

  Descriptions::setDescription($building->getId(), Descriptions::TYPE_BUILDING, $new_desc, $char->getId());

  $event_change_self = _EVENT_CHANGE_BUILDING_DESCRIPTION_SELF;
  $event_change_others = _EVENT_CHANGE_BUILDING_DESCRIPTION_OTHER;
} else { // desc change project

  $toReplace = array("\\n", "\\r", "\n", "\r");
  $short = str_replace($toReplace, "",  htmlspecialchars(TextFormat::getShorterText($new_desc, 20)));
  $turnsNeeded = 80; // 1 hour
  $projectName = '<CANTR REPLACE NAME=project_building_new_description>: "' . $short . '"';
  $reqNeeded = ""; // no requirements

  $generalSub = new ProjectGeneral($projectName, $char->getId(), $building->getId());
  $typeSub = new ProjectType(ProjectConstants::TYPE_DESC_BUILDING_CHANGE, 0, 0, 0, 0, 0); // 33 - animal husbandry
  $requirementSub = new ProjectRequirement($turnsNeeded, $reqNeeded);
  $outputSub = new ProjectOutput(0, $new_desc);

  $project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
  $project->saveInDb();

  if (!$char->isBusy()) {
    $char->setProject($project->getId());
    $char->saveInDb();
  }

  $event_change_self = _EVENT_START_CHANGE_BUILDING_DESCRIPTION_SELF;
  $event_change_others = _EVENT_START_CHANGE_BUILDING_DESCRIPTION_OTHER;
}

Event::create($event_change_self, "LOCID=" . $building->getId())->forCharacter($char)->show();
Event::create($event_change_others, "ACTOR=" . $char->getId() . " LOCID=" . $building->getId())
  ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();

redirect("char.description");
