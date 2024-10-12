<?php

$object_id = HTTPContext::getInteger('object_id');

try {
  $objectToBury = CObject::loadById($object_id);
  $charLocation = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

/* *********** VALIDATION ************* */

if (!$char->isInSameLocationAs($objectToBury)) {
  CError::throwRedirectTag("char.objects", "error_bury_not_same_loc");
}

if (!$objectToBury->hasProperty("Buryable")) {
  CError::throwRedirectTag("char.objects", "error_not_buryable");
}

if (!in_array($charLocation->getType(), [LocationConstants::TYPE_OUTSIDE, LocationConstants::TYPE_SAILING_SHIP])) {
  CError::throwRedirectTag("char.objects", "error_bury_not_outside");
}

if ($objectToBury->getType() == ObjectConstants::TYPE_DEAD_BODY) {
  try {
    $charToBury = Character::loadById($objectToBury->getTypeid());

    if ($charToBury->isAlive()) {
      CError::throwRedirectTag("char.objects", "error_bury_already_buried");
    }
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("char.objects", "error_too_far_away");
  }
}

$projectExists = Project::locatedIn($char->getLocation())->type(ProjectConstants::TYPE_BURYING)->subtype($objectToBury->getId())->exists();
if ($projectExists) {
  CError::throwRedirectTag("char.objects", "error_bury_already_buried");
}

try {
  $dragging = Dragging::loadByVictim(DraggingConstants::TYPE_OBJECT, $objectToBury->getId());

  $draggerTags = Pipe::from($dragging->getDraggers())->map(function ($draggerId) use ($char) {
    return TagBuilder::forChar($draggerId)->observedBy($char)->allowHtml(false)->build()->interpret();
  })->toArray();

  $draggersText = urlencode(implode(", ", $draggerTags));
  CError::throwRedirect("char.objects", "<CANTR REPLACE NAME=error_bury_being_dragged DRAGGER=" . $draggersText . ">");
} catch (InvalidArgumentException $e) {
  // pass through
}

// action accepted
$buryableProp = $objectToBury->getProperty("Buryable");

$daysNeeded = 0.25;
if (array_key_exists("days", $buryableProp)) {
  $daysNeeded = $buryableProp["days"];
}
$projectNameTag = "project_burying_object";
if (array_key_exists("projectTag", $buryableProp)) {
  $projectNameTag = $buryableProp["projectTag"];
}
$turnsNeeded = $daysNeeded * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

$projectName = "<CANTR REPLACE NAME=$projectNameTag OBJECT=" . $objectToBury->getId() . ">";
$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_BURYING, $objectToBury->getId(), StateConstants::BURYING, 0, 4, 0);
$requirementSub = new ProjectRequirement($turnsNeeded, "objectid:" . $objectToBury->getId());
$outputSub = new ProjectOutput(0, $objectToBury->getTypeid());

$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

if (!$char->isBusy()) {
  $char->setProject($project->getId());
  $char->saveInDb();
}

redirect("char.objects");
