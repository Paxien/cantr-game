<?php

$object_id = HTTPContext::getInteger('object_id');

try {
  $lock = KeyLock::loadByLockId($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

$alreadyExists = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_DISASSEMBLING_OPEN_LOCK)->subtype($lock->getId())->exists();

if ($alreadyExists) {
  CError::throwRedirectTag("char.events", "error_lockdest_already");
}

if (!ObjectHandler::hasObjectByName("screwdriver", $char->getId())) { // requires screwdriver
  $tag = new Tag("<CANTR REPLACE NAME=item_screwdriver_o>");
  $screwdriver = urlencode($tag->interpret());
  CError::throwRedirectTag("char.objects", "error_lack_tools TOOLS=$screwdriver");
}

if ($lock->isLocked()) {
  CError::throwRedirectTag("char.events", "error_lockdest_locked");
}

if ($lock->isLocationLock()) {
  try {
    $lockLocation = Location::loadById($lock->getLocationId());
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("char.events", "error_too_far_away");
  }
  if ($char->getLocation() != $lockLocation->getId()) { // not in the same location
    CError::throwRedirectTag("char.events", "error_too_far_away");
  }
  // everything ok
  $targetName = urlencode($lockLocation->getName());
  $name = "<CANTR REPLACE NAME=project_destroying_lock_location LOCKID={$lock->getId()} TARGET={$lockLocation->getId()}>";
} elseif ($lock->isObjectLock()) {
  try {
    $lockedObject = CObject::loadById($lock->getObjectId());
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("char.events", "error_too_far_away");
  }
  if (!$char->isInSameLocationAs($lockedObject)) {
    CError::throwRedirectTag("char.events", "error_too_far_away");
  }
  // everything ok
  $objectName = TagUtil::getGenericTagForObjectName($lockedObject->getUniqueName());
  $name = "<CANTR REPLACE NAME=project_destroying_lock_object LOCKID={$lock->getId()} TARGET={$objectName}>";
  $targetName = urlencode(sprintf('<CANTR REPLACE NAME=%s>', $objectName));
}


// CREATE PROJECT

$turnsleft = 2400;
$type = ProjectConstants::TYPE_DISASSEMBLING_OPEN_LOCK;
$subtype = $lock->getId();
$result = $lock->getId();
$turnsneeded = 3 * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
$reqneeded = "days:3;tools:screwdriver";

$general = new ProjectGeneral($name, $char->getId(), $char->getLocation());
$type = new ProjectType($type, $subtype, StateConstants::NONE, ProjectConstants::PROGRESS_MANUAL,
  ProjectConstants::PARTICIPANTS_NO_LIMIT, ProjectConstants::DIGGING_SLOTS_NOT_USE);
$requirement = new ProjectRequirement($turnsneeded, $reqneeded);
$output = new ProjectOutput(0, $result);

// create lock picking project
$project = new Project($general, $type, $requirement, $output);
$project->saveInDb();

// Report to other people on the same location
Event::create(1017, "ACTOR=" . $char->getId() . " NUMBER=" . $lock->getId() . " BUILDING=$targetName")
  ->nearCharacter($char)->except($char)->show();

// Report to character
Event::create(1019, "NUMBER=" . $lock->getId() . " BUILDING=$targetName")->forCharacter($char)->show();

if ($lock->isLocationLock()) {
  // Report to people on the 'other side' of the lock
  $otherSide = $lockLocation->getRegion();
  if ($otherSide > 0) { // other side exists, so it's not a travelling vehicle
    Event::create(1018, "NUMBER=" . $lock->getId() . " BUILDING=$targetName")->inLocation($otherSide)->show();
  }
}

if (!$char->isBusy()) {
  $char->setProject($project->getId());
  $char->saveInDb();
}

redirect("char.events");
