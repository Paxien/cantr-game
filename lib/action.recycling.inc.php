<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($object_id);
  $objectType = ObjectType::loadById($object->getType());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_project_not_same_location");
}

// it's just a precondition
$arrayOfInvalidTypes = array_values(array_merge([1, 2, 30, 37], ObjectConstants::$TYPES_COINS));
if (in_array($object->getType(), $arrayOfInvalidTypes) || $object->isQuantity()) {
  CError::throwRedirectTag("char.objects", "error_object_not_needed");
}


//check if the machine/object is in the same location
if (!$char->isInSameLocationAs($object)) {
  CError::throwRedirectTag("char.objects", "error_project_not_same_location");
}

//check if the object can be disassembled
$objectRules = Parser::rulesToArray($object->getRules());
if (!array_key_exists('recyclable', $objectRules)) {
  CError::throwRedirectTag("char.objects", "error_object_cannot_be_disassembled");
}

$recyclingRules = Parser::rulesToArray($objectRules['recyclable'], ",>");
$buildRequirements = Parser::rulesToArray($object->getBuildRequirements());

$requiredTools = [];
if (array_key_exists("tools", $recyclingRules)) {
  $requiredTools = explode("/", $recyclingRules["tools"]);
} elseif (array_key_exists("tools", $buildRequirements)) {
  $requiredTools = explode(",", $buildRequirements["tools"]);
}

/**
 * @param array $recyclingRules
 * @param array $buildRequirements
 * @return float days for a recycling project
 */
function getDaysToRecycle(array $recyclingRules, array $buildRequirements)
{
  if (array_key_exists("days", $recyclingRules)) {
    return $recyclingRules["days"];
  }
  $daysToBuild = 1;
  if ($buildRequirements['days']) {
    $daysToBuild = $buildRequirements['days'];
  }
  if (array_key_exists("time", $recyclingRules)) {
    return $daysToBuild * $recyclingRules["time"];
  }
  return $daysToBuild;
}

$daysToRecycle = getDaysToRecycle($recyclingRules, $buildRequirements);

$requirements = "";
if (!empty($requiredTools)) {
  foreach ($requiredTools as $toolName) {
    $toolInInventory = CObject::inInventoryOf($char)->name($toolName)->exists();
    if (!$toolInInventory) {
      CError::throwRedirectTag("char.objects", "error_lack_tools");
    }
  }
  $requirements .= "tools:" . implode(",", $requiredTools);
}

/*
 * Dependency is machine name mentioned in build conditions
 */
function isInDependencies(CObject $object, $dependencies)
{
  $depConditions = Parser::rulesToArray($dependencies);
  if (array_key_exists("hasobject", $depConditions)) {
    $objectsNeeded = explode(",", $depConditions["hasobject"]);
    return in_array($object->getName(), $objectsNeeded);
  }
  return false;
}

function tryToFulfillDependenciesWithSameNameAs(CObject $object, $dependingName)
{
  $objectsFulfilling = CObject::locatedIn($object->getLocation())->name($object->getName())->findAll();

  $objectsFulfillingDependenciesCount = Pipe::from($objectsFulfilling)->filter(function(CObject $object) {
    $projectsAffectingObject = [ProjectConstants::TYPE_DISASSEMBLING, ProjectConstants::TYPE_FIXING_DAMAGED];
    return !Project::locatedIn($object->getLocation())->types($projectsAffectingObject)->subtype($object->getId())->exists();
  })->count();


  if ($objectsFulfillingDependenciesCount < 2) { // should be $object that is going to be disasembled and another
    if ($objectsFulfillingDependenciesCount == 0) {
      Logger::getLogger("action.recycling")->error("object " . $dependingName . " see no object to fulfill dependency, there should be at least 1 (" . $object->getId() . ")");
    }
    $depName = urlencode("<CANTR REPLACE NAME=item_" . $dependingName . "_o>");
    CError::throwRedirectTag("char.events", "error_disassembling_violated_dependency DEPENDENCY=" . $depName);
  }
}

$db = Db::get();
if ($object->getLocation() > 0) {
  $stm = $db->prepare("SELECT ot.unique_name, o.setting, ot.build_conditions AS dependencies FROM objects o
    INNER JOIN objecttypes ot ON ot.id = o.type
    WHERE ot.build_conditions LIKE '%hasobject:%' AND o.location = :location");
  $stm->bindInt("location", $object->getLocation());
  $stm->execute();

  foreach ($stm->fetchAll() as $depObject) {
    if (isInDependencies($object, $depObject->dependencies) && $depObject->setting == ObjectConstants::SETTING_FIXED) {
      tryToFulfillDependenciesWithSameNameAs($object, $depObject->unique_name);
    }
  }

  $stm = $db->prepare("SELECT ot.unique_name, ot.build_conditions AS dependencies FROM projects p
    INNER JOIN objecttypes ot ON ot.id = p.subtype AND p.type = :projectType
    WHERE ot.build_conditions LIKE '%hasobject:%' AND p.location = :location");
  $stm->bindInt("projectType", ProjectConstants::TYPE_MANUFACTURING);
  $stm->bindInt("location", $object->getLocation());
  $stm->execute();

  foreach ($stm->fetchAll() as $depObject) {
    if (isInDependencies($object, $depObject->dependencies)) {
      tryToFulfillDependenciesWithSameNameAs($object, $depObject->unique_name);
    }
  }
}

if (!$object->hasNoFixedContents()) {
  CError::throwRedirectTag("char.objects", "error_object_not_empty");
}

// checking if the selected object is in use
if ($object->isInUse()) {
  CError::throwRedirectTag("char.objects", "error_recycling_machine_in_use");
}

$requirements .= ";days:$daysToRecycle";
$requirements .= ";objectid:" . $object->getId();

$itemTag = TagUtil::getGenericTagForObjectName($object->getUniqueName());
$projectName = "<CANTR REPLACE NAME=action_recycle_1> <CANTR REPLACE NAME=$itemTag>";
$turnsNeeded = (int)(800 * $daysToRecycle);

$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_DISASSEMBLING, $object->getId(),
  $object->getProductionSkill(), ProjectConstants::PROGRESS_MANUAL,
  ProjectConstants::PARTICIPANTS_NO_LIMIT, ProjectConstants::DIGGING_SLOTS_NOT_USE);
$requirementSub = new ProjectRequirement($turnsNeeded, $requirements);
$outputSub = new ProjectOutput(0, "");

$project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$project->saveInDb();

Event::create(225, "OBJECT=$object_id ACTOR=$character RAND_OUTPUT=" . $object->getId())
  ->forCharacter($char)->show();
Event::create(224, "OBJECT=$object_id ACTOR=$character RAND_OUTPUT=" . $object->getId())
  ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();

redirect("char.objects");
