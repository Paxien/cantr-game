<?php

// SANITIZE INPUT
$character = HTTPContext::getInteger('character');
$lockpick = HTTPContext::getInteger('lockpick');
$lock = HTTPContext::getInteger('lock');

$lock_location_name = $_REQUEST['lock_location_name'];


if (!in_array($lockpick, ObjectConstants::$TYPES_LOCKPICKING)) {
  CError::throwRedirectTag("char.events", "error_lockpick_tool");
}

try {
  $keyLock = KeyLock::loadByLockId($lock);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_no_lock_to_pick");
}

if ($keyLock->isObjectLock()) {
  $isNear = ObjectHandler::isObjectInLocation($keyLock->getObjectId(), $char->getLocation());
  if (!$isNear) {
    CError::throwRedirectTag("char.events", "error_too_far_away");
  }
} else {
  $charLoc = Location::loadById($char->getLocation());
  $targetLoc = Location::loadById($keyLock->getLocationId());
  $notInSameLocation = $targetLoc->getId() != $charLoc->getId(); // lock isn't in the same location
  if ($notInSameLocation && !$targetLoc->isAdjacentTo($charLoc)) {
    CError::throwRedirectTag("char.events", "error_too_far_away");
  }
}

$alreadyLockPicking = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_PICKING_LOCK)->result($lock)->exists();
if ($alreadyLockPicking > 0) {
  CError::throwRedirectTag("char.events", "error_lockpick_already");
}

$lockpickingTool = CObject::inInventoryOf($char)->type($lockpick)->find();

if ($lockpickingTool === null) {
  CError::throwRedirectTag("char.events", "error_lockpick_tool");
}

if ($keyLock->isObjectLock()) {
  $lockedObject = CObject::loadById($keyLock->getObjectId());
  $objectTag = TagUtil::getGenericTagForObjectName($lockedObject->getUniqueName());
  $nameOfTarget = "<CANTR REPLACE NAME={$objectTag}>";
  $name = "<CANTR REPLACE NAME=project_picking_lock_object LOCKID={$keyLock->getId()} TARGET={$objectTag}>";
} else {
  $nameOfTarget = $targetLoc->getName();
  $name = "<CANTR REPLACE NAME=project_picking_lock_location LOCKID={$keyLock->getId()} TARGET={$keyLock->getLocationId()}>";
}

$turnsleft = 800;
$type = ProjectConstants::TYPE_PICKING_LOCK;
$subtype = 0;
$skill = 0;
$result = $keyLock->getId();
$turnsneeded = 800;
$reqneeded = "days:1;tools:{$lockpickingTool->getName()}";

$general = new ProjectGeneral($name, $character, $char->getLocation());
$type = new ProjectType($type, $subtype, $skill, ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT, ProjectConstants::DIGGING_SLOTS_NOT_USE);
$requirement = new ProjectRequirement($turnsneeded, $reqneeded);
$output = new ProjectOutput(0, $result);

// create lock picking project
$project = new Project($general, $type, $requirement, $output);
$project->saveInDb();


$nameOfTarget = urlencode($nameOfTarget);

// report to other people in same location
Event::createPublicEvent(17, "ACTOR=$character NUMBER=" . $keyLock->getId() . " BUILDING=$nameOfTarget",
  $character, Event::RANGE_SAME_LOCATION, array($character));

// Report to character
Event::createPersonalEvent(19, "NUMBER=" . $keyLock->getId() . " BUILDING=$nameOfTarget", $character);


// check which location can be "other side"
if ($char->getLocation() == $keyLock->getLocationId()) {
  $otherSide = $targetLoc->getRegion();
} else {
  $otherSide = $keyLock->getLocationId();
}

// Report to people on the 'other side' of the lock
if ($keyLock->isLocationLock() && $otherSide > 0) {
  Event::createEventInLocation(18, "NUMBER=" . $keyLock->getId() . " BUILDING=$nameOfTarget",
    $otherSide, Event::RANGE_SAME_LOCATION);
}

if (!$char->isBusy()) {
  $char->setProject($project->getId());
  $char->saveInDb();
}

redirect("char.events");

