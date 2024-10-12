<?php

$objectId = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

$canBoost = false;
if ($object->hasProperty("BoostTraveling")) {
  $drivingProp = $object->getProperty("BoostTraveling");
  
  if (array_key_exists("active", $drivingProp)) {
    $canBoost = true;
    
    $maxParticipants = 1;
    if (array_key_exists("maxParticipants", $drivingProp)) {
      $maxParticipants = $drivingProp["maxParticipants"];
    }
  }
}

if ($object->hasProperty("BoostSailing")) {
  $sailingProp = $object->getProperty("BoostSailing");
  
  foreach (["deck", "sails"] as $subProp) {
    if (array_key_exists("active", $sailingProp[$subProp])) {
      $canBoost = true;
      
      $maxParticipants = 1;
      if (array_key_exists("maxParticipants", $sailingProp)) {
        $maxParticipants = $sailingProp["maxParticipants"];
      }
    }
  }
}

if (!$canBoost) { // doesn't boost driving nor sailing - somebody's cheating
  CError::throwRedirectTag("char.objects", "error_cant_boost_speed");
}

if ($char->isBusy()) {
  CError::throwRedirectTag("char.objects", "error_cant_work_already_busy");
}

if (!$char->isInSameLocationAs($object)) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

$existingProject = Project::locatedIn($object->getLocation())
  ->type(ProjectConstants::TYPE_BOOSTING_VEHICLE)->subtype($object->getId())->find();
if ($existingProject != null) {
  // project already exists, try to join
  redirect("joinproject", ["project" => $existingProject->getId()]);
  exit;
}


$projectName = "<CANTR REPLACE NAME=project_controlling OBJECT=". $object->getUniqueName() .">";

$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_BOOSTING_VEHICLE, $object->getId(), 0,
  ProjectConstants::PROGRESS_MANUAL, $maxParticipants, ProjectConstants::DIGGING_SLOTS_NOT_USE); // 33 - animal husbandry
$requirementSub = new ProjectRequirement(1, '');
$outputSub = new ProjectOutput(0, '', 0);

// create object itself
$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

$char->setProject($project->getId());
$char->saveInDb();

redirect("char.events");
