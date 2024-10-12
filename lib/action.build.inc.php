<?php
require_once("func.projectsetup.inc.php");

// SANITIZE INPUT
$objectTypeId = HTTPContext::getInteger('objecttype');
$targetcontainer = HTTPContext::getInteger('targetcontainer');
$name = HTTPContext::getString('name', null);
$description = $_REQUEST['description'];
$specifics = HTTPContext::getString('specifics', null);
$number = HTTPContext::getInteger('number', 1);
$useCustomDesc = HTTPContext::getInteger('useCustomDesc');
$colorPickerOption = HTTPContext::getBoolean('colorCheckBox');
$colorPickerValue = HTTPContext::getRawString('favcolor');

if($colorPickerOption)
{
  $name = "<font color=". '"' . $colorPickerValue . '"' . ">" . $name . "</font>";
}

if (!$useCustomDesc) {
  $description = "";
}

$description = TextFormat::withoutNewlines($description);
$db = Db::get();

try {
  $objectType = ObjectType::loadById($objectTypeId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("build", "error_no_objecttype");
}

if ($char->getLocation() == 0) {
  CError::throwRedirectTag("char.events", "error_cannot_build_travelling");
}

try {
  $location = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_cannot_build_travelling");
}

/********* VERIFY CONDITIONS ********/

$conditions = Parser::rulesToArray($objectType->getBuildConditions(), ";:", true);

if ($conditions['borders_lake']) {
  if (!$location->bordersLake()) {
    $errorMessage = 'error_only_near_lake';
  }
}

if ($conditions['borders_sea']) {
  if (!$location->bordersSea()) {
    $errorMessage = 'error_only_near_sea';
  }
}

if ($conditions['borders_water']) {
  if (!$location->bordersWater()) {
    $errorMessage = 'error_only_sea_or_lake';
  }
}

if ($conditions['hasraw']) {
  $condRaws = explode(',', $conditions['hasraw']);

  foreach ($condRaws as $rawName) {
    $rawId = ObjectHandler::getRawIdFromName($rawName);
    $stm = $db->prepare("SELECT COUNT(*) FROM raws WHERE location = :location AND type = :raw");
    $stm->bindInt("location", $location->getId());
    $stm->bindInt("raw", $rawId);
    $rawsCount = $stm->executeScalar();
    if ($rawsCount == 0) {
      $translatedRawName = TagBuilder::forTag(TagUtil::getRawTagByName($rawName))->build()->interpret();
      $errorMessage = "error_build_elsewhere MATERIAL=" . urlencode($translatedRawName);
    }
  }
}

if ($conditions['isnotsubloc']) { // sublocations of BUILDINGS (so excludes only extensions)
  try {
    $parentLocaton = Location::loadById($location->getRegion());
  } catch (InvalidArgumentException $e) {
    $errorMessage = "error_only_main_building";
  }
  $nonBuildings = [
    LocationConstants::TYPE_OUTSIDE,
    LocationConstants::TYPE_VEHICLE,
    LocationConstants::TYPE_SAILING_SHIP
  ];
  if (!in_array($parentLocaton->getType(), $nonBuildings)) {
    $errorMessage = "error_only_main_building";
  }
}

if ($conditions['maxbuildings']) {
  if ($location->getType() == LocationConstants::TYPE_OUTSIDE) {
    $maxBuildings = $location->getProperty("MaxBuildings");
  } elseif ($location->getType() == LocationConstants::TYPE_BUILDING) {
    $maxBuildings = _MAX_ROOMS;
  } elseif (in_array($location->getType(), [LocationConstants::TYPE_VEHICLE, LocationConstants::TYPE_SAILING_SHIP])) {
    $errorMessage = 'error_no_buildings_on_vehicles';
    $maxBuildings = 0;
  }

  $buildingsCount = LocationFinder::any()
    ->region($location->getId())
    ->type(LocationConstants::TYPE_BUILDING)
    ->count();
}

//One location lock per location, as code can not manage multiple locks
//Later improvement might be to allow multiple locks on a single door
//Get the list of ids which are location locks
$db = Db::get();
$stm = $db->prepare("SELECT objecttype_id FROM obj_properties WHERE property_type = 'LocationLock'");
$stm->execute();
$locationLockIds = $stm->fetchScalars();

//Check if the current objectType is a location lock
if (in_array($objectType->getId(),$locationLockIds,true)) {
  //If we it is a location lock, then check the location for any existing location locks
  foreach ($locationLockIds as $eachObjectId) {

    $objectsCount = CObject::locatedIn($char->getLocation())
        ->type($eachObjectId)//($objectType->getId())
        ->count();
      
    if ($objectsCount >= 1) { //If we find a location lock, drop out of loop with the error message
      $errorMessage = "error_max_number_of_object";
      break;
    }

    $manufacturingProjectsCount = Project::locatedIn($char->getLocation())
      ->type(ProjectConstants::TYPE_MANUFACTURING)
      ->subtype($eachObjectId)//($objectType->getId())
      ->count();
    
    if ($manufacturingProjectsCount >= 1) { //If we find a location lock in progress, drop out of loop with the error message
      $errorMessage = "error_max_number_of_object";
      break; 
    }
      
  }
}

if($conditions['maxproponlocation'])
{
  $maxproponloc = explode('>', $conditions['maxproponlocation']);
  $propertyToSeek = $maxproponloc[0];
  $valueToSeek = $maxproponloc[1];

  $treesAtLocation = CObject::locatedIn($char->getLocation())
  ->hasProperty($propertyToSeek)
  ->count();

  $plantingTreesProjectsCount = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_MANUFACTURING)  
  ->countProperties($propertyToSeek);

  $totalCount = $treesAtLocation + $plantingTreesProjectsCount;
  
  if ($totalCount >= $valueToSeek)
  {
    $errorMessage = "error_max_number_of_object";
  }
}  

if ($conditions['maxonloc']) {
  $objectsCount = CObject::locatedIn($char->getLocation())
    ->type($objectType->getId())
    ->count();
  $manufacturingProjectsCount = Project::locatedIn($char->getLocation())
    ->type(ProjectConstants::TYPE_MANUFACTURING)
    ->subtype($objectType->getId())
    ->count();

  $totalCount = $objectsCount + $manufacturingProjectsCount;
  if (in_array($objectType->getCategory(), ["buildings", "vehicles"])) {
    $locationsCount = LocationFinder::any()
      ->type(LocationConstants::TYPE_BUILDING)
      ->region($char->getLocation())
      ->subtype($objectType->getId())
      ->count();
    $totalCount += $locationsCount;
  }

  if ($totalCount >= $conditions['maxonloc']) {
    $errorMessage = "error_max_number_of_object";
  }
}

if ($conditions['maxonobj']) { // max number of objects related to the containing object (e.g. inner lock)
  $objectsCount = CObject::storedIn($targetcontainer)->type($objectType->getId())->count();
  $projects = Project::locatedIn($char->getLocation())->type(ProjectConstants::TYPE_MANUFACTURING)->subtype($objectType->getId())->findAll();
  $projectsAttachedToThisContainer = Pipe::from($projects)->filter(function(Project $project) use ($targetcontainer) {
    return StringUtil::contains($project->getResult(), "attached>$targetcontainer");
  })->count();
  $totalCount = $objectsCount + $projectsAttachedToThisContainer;
  if ($totalCount >= $conditions['maxonobj']) {
    $errorMessage = "error_max_number_of_object";
  }
}

if ($conditions['usesengine']) {
  $usesEngine = false;
  $locationSubtype = $location->getObjectType();
  $rulesArray = Parser::rulesToArray($locationSubtype->getRules());
  if ($rulesArray['engine']) {
    $engines = explode(",", $rulesArray['engine']);
    if (in_array($objectType->getName(), $engines)) {
      $usesEngine = true;
    }
  }

  if (!$usesEngine) {
    $errorMessage = 'error_wrong_engine';
  }
}

if ($conditions['islocation']) {
  if (!in_array($location->getType(), [LocationConstants::TYPE_OUTSIDE, LocationConstants::TYPE_SUBFIELD])) {
    $errorMessage = 'error_build_only_outside';
  }
}

if ($conditions['isbuilding']) {
  if ($location->getType() != LocationConstants::TYPE_BUILDING) {
    $errorMessage = 'error_only_in_building';
  }
}

if ($conditions['isbuildingorvehicle']) {
  if (!in_array($location->getType(), [
    LocationConstants::TYPE_BUILDING,
    LocationConstants::TYPE_VEHICLE,
    LocationConstants::TYPE_SAILING_SHIP
  ])) {
    $errorMessage = 'error_only_building_or_vehicle';
  }
}

if ($conditions['isnoncentertype']) {
  $allowedBuildingTypes = explode(',', $conditions['isnoncentertype']);

  if (in_array($location->getType(), [
    LocationConstants::TYPE_OUTSIDE,
    LocationConstants::TYPE_SUBFIELD
  ])) {
    $errorMessage = 'error_only_in_buildingtypes TYPES=' . urlencode(implode(',', translateBuildingTypes($allowedBuildingTypes)));
  } else {
    $locationSubtype = $location->getObjectType();
    if (!in_array($locationSubtype->getName(), $allowedBuildingTypes)) {
      $errorMessage = 'error_only_in_buildingtypes TYPES=' . urlencode(implode(',', translateBuildingTypes($allowedBuildingTypes)));
    }
  }
}

function translateBuildingTypes($buildingTypes)
{
  $translatedTypes = [];

  foreach ($buildingTypes as $type) {
    $translatedTypes[] = TagBuilder::forTag(TagUtil::getBuildingTagByName($type))->build()->interpret();
  }

  return $translatedTypes;
}

if ($conditions['isvehicle']) {
  if (!in_array($location->getType(), [
    LocationConstants::TYPE_VEHICLE,
    LocationConstants::TYPE_SAILING_SHIP
  ])) {
    $throwError = true;
    if ($location->getType() == LocationConstants::TYPE_BUILDING) {
      $parentLocation = $location->getParent();

      if (in_array($parentLocation->getType(), [
        LocationConstants::TYPE_VEHICLE,
        LocationConstants::TYPE_SAILING_SHIP
      ])) {
        $throwError = false;
      }
    }

    if ($throwError) {
      $errorMessage = 'error_only_in_vehicle';
    }
  }
}

if ($conditions['isnotvehicle']) {
  if (in_array($location->getType(), [LocationConstants::TYPE_VEHICLE, LocationConstants::TYPE_SAILING_SHIP])) {
    $throwError = true;
  } elseif ($location->getType() == LocationConstants::TYPE_BUILDING) {
    $parentLocation = $location->getParent();

    if (in_array($parentLocation->getType(), [
      LocationConstants::TYPE_VEHICLE,
      LocationConstants::TYPE_SAILING_SHIP
    ])) {
      $throwError = true;
    }
  }

  if ($throwError) {
    $errorMessage = 'error_not_on_vehicles';
  }
}

if ($conditions['area']) {
  if (($location->getType() != LocationConstants::TYPE_OUTSIDE) || ($location->getArea() != $conditions['area'])) {
    $requiredLocationSubtype = ObjectType::loadById($conditions['area']);
    $errorMessage = "error_build_only_area AREATYPE=" . urlencode($requiredLocationSubtype->getUniqueName());
  }
}

if ($conditions['hasobject']) {
  $requiredObjects = explode(',', $conditions['hasobject']);
  //adding initialisation outside the loop for stability
  $objectsCount = 0;
  foreach ($requiredObjects as $requiredObject) {
    $objectsCount = CObject::locatedIn($char->getLocation())->name($requiredObject)->count();
    if ($objectsCount == 0) {
      $errorMessage = "error_build_only_object OBJECT=" . urlencode($requiredObject);
    }
  }
}

//Copied logic for hasobject but inverting the final check
if ($conditions['hasnoobject']) {
  $disallowedObjects = explode(',', $conditions['hasnoobject']);
  $objectTypeIds = ObjectTypeFinder::any()->names($disallowedObjects)->findIds();

  //this step taken from the hasnobuilding
  $disallowedObjectBeingBuilt = Project::locatedIn($char->getLocation())->subtypes($objectTypeIds)->count();
  if ($disallowedObjectBeingBuilt > 0) {
    $errorMessage = "error_disallowed_object_project NOOBJECT=" . urlencode($disallowedObject);
  }
  
  //adding initialisation outside the loop for stability
  $objectsCount = 0;
  //There might be a helper function to find objects from a comma deliminated list
  foreach ($disallowedObjects as $disallowedObject) {
    $objectsCount = CObject::locatedIn($char->getLocation())->name($disallowedObject)->count();

    //if the sum of the above is more than zero then we cannot build it
    if ($objectsCount > 0) {
      $errorMessage = "error_disallowed_object NOOBJECT=" . urlencode($disallowedObject);
    }
  }
}

if ($conditions['hasnobuilding']) {
  $disallowedLocations = explode(',', $conditions['hasnobuilding']);
  $objectTypeIds = ObjectTypeFinder::any()->names($disallowedLocations)->findIds();

  $disallowedBuildings = LocationFinder::any()->region($char->getLocation())->types([
    LocationConstants::TYPE_BUILDING,
    LocationConstants::TYPE_VEHICLE,
  ])->subtypes($objectTypeIds)->count();

  $disallowedBeingBuilt = Project::locatedIn($char->getLocation())->types([
    ProjectConstants::TYPE_MANUFACTURING,
    ProjectConstants::TYPE_TEAR_DOWN,
  ])->subtypes($objectTypeIds)->count();

  if ($disallowedBuildings + $disallowedBeingBuilt > 0) {
    $errorMessage = "error_disallowed_building NOBUILDING=" . urlencode($conditions['hasnobuilding']);
  }
}

if ($conditions['borders_sea_or_isnoncentertype']) {
  if ($location->isOutside()) {
    if (!$location->bordersSea()) {
      $errorMessage = 'error_only_near_sea_or_buildings TYPES=' . urlencode($conditions['borders_sea_or_isnoncentertype']);
    }
  } else {
    $allowedBuildingTypes = explode(',', $conditions['borders_sea_or_isnoncentertype']);
    $locationSubtype = $location->getObjectType();
    if (!in_array($locationSubtype->getName(), $allowedBuildingTypes)) {
      $errorMessage = 'error_only_near_sea_or_buildings TYPES=' . urlencode($conditions['borders_sea_or_isnoncentertype']);
    }
  }
}

if ($conditions['borders_lake_or_isnoncentertype']) {
  if ($location->isOutside()) {
    if (!$location->bordersLake()) {
      $errorMessage = 'error_only_near_lake_or_buildings TYPES=' . urlencode($conditions['borders_lake_or_isnoncentertype']);
    }
  } else {
    $allowedBuildingTypes = explode(',', $conditions['borders_lake_or_isnoncentertype']);
    $locationSubtype = $location->getObjectType();
    if (!in_array($locationSubtype->getName(), $allowedBuildingTypes)) {
      $errorMessage = 'error_only_near_lake_or_buildings TYPES=' . urlencode($conditions['borders_lake_or_isnoncentertype']);
    }
  }
}

if ($conditions['borders_water_or_isnoncentertype']) {
  if ($location->isOutside()) {
    if (!$location->bordersWater()) {
      $errorMessage = 'error_only_near_water_or_buildings TYPES=' . urlencode($conditions['borders_water_or_isnoncentertype']);
    }
  } else {
    $allowedBuildingTypes = explode(',', $conditions['borders_water_or_isnoncentertype']);
    $locationSubtype = $location->getObjectType();
    if (!in_array($locationSubtype->getName(), $allowedBuildingTypes)) {
      $errorMessage = 'error_only_near_water_or_buildings TYPES=' . urlencode($conditions['borders_water_or_isnoncentertype']);
    }
  }
}

if ($conditions['isnoncentertype_or_outside']) {
  $allowedBuildingTypes = explode(',', $conditions['isnoncentertype_or_outside']);
  $locationSubtype = $location->getObjectType();
  if (!$location->isOutside() && !in_array($locationSubtype->getName(), $allowedBuildingTypes)) {
    $errorMessage = 'error_only_outside_or_buildings TYPES=' . urlencode($conditions['isnoncentertype_or_outside']);
  }
}

// exceptions (building of inner lock is triggered from locking action)
if (!in_array($objectType->getId(), [ObjectConstants::TYPE_INNER_LOCK])) {
  if (!$objectType->getObjectCategory()->isBuildable()) {
    CError::throwRedirectTag("char.events", "error_no_objecttype");
  }
}

if ($objectType->getCategory() == "buildings") {
  // *** Buildings allowed in towns or buildings only
  $accepted = in_array($location->getType(), [
    LocationConstants::TYPE_OUTSIDE,
    LocationConstants::TYPE_BUILDING,
  ]);
  if ($accepted) {
    if ($location->getType() == LocationConstants::TYPE_BUILDING) {

      $parentLocation = $location->getParent();
      $accepted = in_array($parentLocation->getType(), [
        LocationConstants::TYPE_OUTSIDE,
        LocationConstants::TYPE_BUILDING,
      ]);
    }
  }
  if (!$accepted) {
    $errorMessage = "error_no_buildings_on_vehicles";
  }
}

if (!empty($errorMessage)) {
  CError::throwRedirectTag('char.events', $errorMessage);
}

/*** CUSTOM DESCRIPTION - optional ***/

$rules = Parser::rulesToArray($objectType->getRules());
if ($rules['describable']) {
  $descRules = Parser::rulesToArray($rules['describable'], ",>");
  $allowCustomDescription = ($descRules['bymanufacturing'] == "yes");
}

if (!$allowCustomDescription) { // desc not allowed, so remove it
  $description = "";
}

if (!Descriptions::isDescriptionAllowed(Descriptions::TYPE_OBJECT, $description)) {
  CError::throwRedirectTag("char.inventory", "error_description_too_long");
}

/********* DESCRIPTION **************/

$manufacturingProjectName = $objectType->getBuildProjectName();
preg_match_all("/\#(.+?)\#/", $manufacturingProjectName, $desc, PREG_SET_ORDER);

for ($teller = 0; $teller < count($desc); $teller++) {
  $replacement = $$desc[$teller][1]; // TODO do not use double dollar
  $manufacturingProjectName = preg_replace("/" . $desc[$teller][0] . "/", $replacement, $manufacturingProjectName, 1);
}

/********* TURNS LEFT / NEEDED ******/

$reqParts = Parser::rulesToArray($objectType->getBuildRequirements());
if ($reqParts['days']) {
  $turnsleft = $reqParts['days'] * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
}

if ($turnsleft == 0) {
  $turnsleft = 1;
}

// manufacturing requires more work when it has object description
if (!empty($description)) {
  $turnsleft *= 1 + ProjectConstants::CHANGE_DESC_FRACTION_OF_MANU_TIME;
}

/********* RESULT *******************/

$enhancedResult = [];

// it's necessary to store it as a list, because the order matters and there can be duplicates
foreach (explode(";", $objectType->getBuildResult()) as $resultActionDetails) {
  list($resultActionName, $resultActionDetails) = explode(":", $resultActionDetails, 2);

  $subparts = Parser::rulesToArray($resultActionDetails, ",>");
  foreach ($subparts as $subpartKey => $subpartValue) {
    $splitValueParts = explode(">", $subpartValue);
    if (in_array($splitValueParts[0], ['ask', 'unique-ask'])) {
      $subpartValue = $$subpartKey; // TODO fix it
      $subpartValue = trim($subpartValue);

      if (mb_strlen($subpartValue) > 60) {
        CError::throwRedirectTag("build&objecttype={$objectType->getId()}", 'error_parameter_too_long');
      }
      if ($splitValueParts[0] == 'unique-ask') {
        //Following line changed as part of bug fix to prevent &nbsp; explotations, also moved to be checked before doing database to optimise
        if (!Validation::isOnlyAlphabeticOrSpace($subpartValue)) {
          CError::throwRedirectTag("build&objecttype={$objectType->getId()}", "error_unique_name_characters");
        }

        //added the following to check for inprogress items as well
        $stm = $db->prepare("SELECT result FROM projects WHERE subtype = :type");
        $stm->bindInt("type", $objectType->getId());
        $stm->execute();
        foreach ($stm->fetchScalars() as $projectBuildResult) {
          if (preg_match("/(^|,)specifics>" . $subpartValue . "(,|$)/u", $projectBuildResult)) {
            CError::throwRedirectTag("build&objecttype={$objectType->getId()}", 'error_object_needs_unique_name');
          }
        }

        $stm = $db->prepare("SELECT specifics FROM objects WHERE type = :type");
        $stm->bindInt("type", $objectType->getId());
        $stm->execute();
        foreach ($stm->fetchScalars() as $existingName) {
          if (trim($existingName) == trim($subpartValue)) {
            CError::throwRedirectTag("build&objecttype={$objectType->getId()}", 'error_object_needs_unique_name');
          }
        }
      }
      $subpartValue = urlencode($subpartValue);
    }

    if ($splitValueParts[0] == 'var') {
      switch ($splitValueParts[1]) {
        case "typeid":
          $subpartValue = $objectType->getId();
          break;
        // var that show to us ID of object related with this project, for example when we try build lock in noticeboard,
        // we get ID of target noticeboard.
        case "targetcontainer":
          $subpartValue = intval($targetcontainer);
          break;
      }
    }
    $subparts[$subpartKey] = $subpartValue;
  }
  $resultActionDetails = Parser::arrayToRules($subparts, ",>");
  $enhancedResult[] = $resultActionName . ":" . $resultActionDetails;
}
$enhancedResult = implode(";", $enhancedResult);

/********* MASS PRODUCTION **********/
if (ProjectSetup::isMassProductionAllowed($objectType)) {
  $allowMassProduction = true;
  $number = max(1, min($number, ProjectConstants::MASS_PRODUCTION_MAX)); // allowed is [1, 8]
} else {
  $allowMassProduction = false;
  $number = 1;
}

$buildRequirements = $objectType->getBuildRequirements();
if ($allowMassProduction && !strstr($enhancedResult, ";")) {
  $enhancedResult = substr(str_repeat($enhancedResult . ";", $number), 0, -1);

  $turnsleft *= $number;

  $rules = Parser::rulesToArray($buildRequirements, ";:");
  foreach ($rules as $ruleName => &$rule) {
    if (in_array($ruleName, ["raws", "objects"])) {
      $req = Parser::rulesToArray($rule, ",>");
      foreach ($req as &$amt) {
        $amt *= $number;
      }
      $rule = Parser::arrayToRules($req, ",>");
    } elseif ($ruleName == "days") {
      $rule *= $number;
    }
  }
  $buildRequirements = Parser::arrayToRules($rules, ";:");

  if ($number > 1) {
    $manufacturingProjectName = $number . "x " . $manufacturingProjectName;
  }
}

/********* STORING ******************/


$general = new ProjectGeneral($manufacturingProjectName, $char->getId(), $char->getLocation());
$type = new ProjectType(ProjectConstants::TYPE_MANUFACTURING, $objectType->getId(), $objectType->getProductionSkill(), ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT, ProjectConstants::DIGGING_SLOTS_NOT_USE);
$requirement = new ProjectRequirement($turnsleft, $buildRequirements);
$output = new ProjectOutput(0, $enhancedResult, 0, $description);

// create lock picking project
$project = new Project($general, $type, $requirement, $output);
$project->saveInDb();

$projectId = $project->getId();

switch ($resource_allocation) {

  case "regardless" :
    $join_project = automatic_add_to_project($project->getId(), $char->getId(), true);
    if ($join_project) {
      automatic_join_project($project->getId(), $char->getId());
    }
    redirect("char.events");
    break;
  case "full" :
    $join_project = automatic_add_to_project($project->getId(), $char->getId(), false);
    if ($join_project) {
      automatic_join_project($project->getId(), $char->getId());
    }
    redirect("char.events");
    break;
  default :
    redirect("char.inventory");
}

redirect("char.inventory");


/**
 * Notes:
 * 
 * 13/06/23 - Coderlotl
 * Added the check for the condition 'maxproponlocation'. Check the class ProjectFinder.
 */