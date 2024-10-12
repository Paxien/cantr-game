<?php

// SANITIZE INPUT
$character = HTTPContext::getInteger('character');
$object_id = HTTPContext::getInteger('object_id');


try {
  $machine = CObject::loadById($object_id);
  $objectType = ObjectType::loadById($machine->getType());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

$machineProjects = MachineProject::loadProjectsForMachineType($objectType);

if (count($machineProjects) == 1) {
  redirect("use", [
    "object" => $object_id,
    "choice" => "yes"
  ]);
  exit();
}

if (!$char->hasWithinReach($machine)) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if ($machine->isInUse()) {
  CError::throwRedirectTag("char.events", "error_machine_in_use");
}

function getRawTypeTranslatedNameById($id) {
  return TagBuilder::forTag(TagUtil::getRawTagById($id))->build()->interpret();
}

$projectAndResourcesAndName = [];
foreach ($machineProjects as $machineProject) {

  $rawNames = array_keys($machineProject->getRequiredRaws());
  $rawIds = Pipe::from($rawNames)->map(function($rawName) {
    return CObject::getRawIdFromName($rawName);
  })->toArray();

  $rawsInInventoryCount = count(CObject::inInventoryOf($char)->type(ObjectConstants::TYPE_RAW)->typeids($rawIds)->findIds());
  $rawsNeededCount = count($rawIds);

  $projectAndResourcesAndName[] = [$machineProject, $rawsInInventoryCount, $rawsNeededCount, getRawTypeTranslatedNameById($machineProject->getResultRawTypeId())];
}


usort($projectAndResourcesAndName, function($a, $b) { // sort array by percent of owned raws
  $aPerc = 0;
  if ($a[2] != 0) {
    $aPerc = $a[1] / $a[2] * 100;
  }
  $bPerc = 0;
  if ($b[2] != 0) {
    $bPerc = $b[1] / $b[2] * 100;
  }

  if ($bPerc != $aPerc) {
    return $bPerc - $aPerc; // which project has access to higher percent of input resources
  }

  if ($a[1] != $b[1]) { // when percent the same then exact number of matched resources matters
    return $b[1] - $a[1];
  }

  return strcmp($a[3], $b[3]); // if not, then compare name of output resource
});

/** @var $machineProjects MachineProject[] */
$machineProjects = Pipe::from($projectAndResourcesAndName)->map(function($a) {
  return $a[0];
})->toArray();


$groups = [];
foreach ($machineProjects as $project) {
  $foundGroup = false;
  foreach ($groups as $groupResultRaw => &$existingGroup) { // check if matching group already exists
    if ($project->getResultRawTypeId() == $groupResultRaw) {
      $existingGroup[] = $project;
      $foundGroup = true;
    }
  }
  if (!$foundGroup) { // no such group exist, so create a new one
    $groups[$project->getResultRawTypeId()] = [$project];
  }
}


/*
 Removing "lastGroup" for now - maybe it'll be needed again in the future

$lastGroup = [];
foreach ($groups as $projGroup) {
  if (count($projGroup) == 1) { // if there's only one project in a group
    $lastGroup[] = $projGroup[0]; // add it (as there's only one) to the last group
  } else {
    $projectGroups[] = $projGroup;
  }
}


if (count($lastGroup) > 0) {
  $projectGroups[] = $lastGroup;
}
*/

function hasResource($rawName) {
  global $char;
  $rawTypeId = CObject::getRawIdFromName($rawName);
  return CObject::inInventoryOf($char)->type(ObjectConstants::TYPE_RAW)->typeid($rawTypeId)->exists();
}

$createProjectRepresentation = function(MachineProject $element) {
  return [
    "id" => $element->getId(),
    "name" => $element->getName(),
    "raws" => Pipe::from(array_keys($element->getRequiredRaws()))->mapKV(function($key, $rawName) {
      return ["<CANTR REPLACE NAME=" . TagUtil::getRawTagByName($rawName) . ">" => hasResource($rawName)];
    })->toArray(),
  ];
};

$presentationGroups = [];
foreach ($groups as $rawTypeId => $group) {
  $uniqueRawName = str_replace(" ", "_", CObject::getRawNameFromId($rawTypeId));
  $presentationGroups[$uniqueRawName] = Pipe::from($group)->map($createProjectRepresentation)->toArray();
}


$smarty = new CantrSmarty;
$smarty->assign ("projects", $presentationGroups);
$smarty->assign ("object", $object_id);
$smarty->displayLang ("form.select.use.tpl", $lang_abr);
    

