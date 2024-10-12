<?php

// SANITIZE INPUT
$projectId = HTTPContext::getInteger('project');
$character = HTTPContext::getInteger('character');
$from_page = HTTPContext::getString('from_page', 'char.projects');

$smarty = new CantrSmarty;

$smarty->assign("from_page", $from_page);

$db = Db::get();
$accepted = true;

try {
  $project = Project::loadById($projectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.projects", "error_project_not_same_location");
}

if (!$char->isInSameLocationAs($project)) {
  CError::throwRedirectTag("char.events", "error_project_not_same_location");
}


// accepted

$smarty->assign("project_name", $project->getName());

/* ******* PARTICIPANTS ******* */

$stm = $db->prepare("SELECT id FROM chars WHERE project = :projectId ORDER BY register DESC");
$stm->bindInt("projectId", $projectId);
$stm->execute();

$smarty->assign("chars", $stm->rowCount());

if ($stm->rowCount()) {
  $lineSkills = [];
  foreach ($stm->fetchScalars() as $charId) {
    try {

      import_lib("func.genes.inc.php");
      $worker = Character::loadById($charId);
      $skill = "";
      if ($project->getSkill() != StateConstants::NONE) {
        $skillDesc = get_skill_adjective($worker->getState($project->getSkill()));
        $skill = " (<CANTR REPLACE NAME=skill_adjective_" . $skillDesc . ">)";
      }

      $line = TagBuilder::forChar($worker)->allowHtml(true)->build()->interpret();

      $lineSkills[] = $line . $skill;

    } catch (InvalidArgumentException $e) {
      Logger::getLogger("info.project")->error("Can't instantiate char $charId seen by " . $char->getId() .
        " participating in project " . $projectId);
    }
  }

  $smarty->assign("lineSkills", $lineSkills);
}

/* ******* INITIATOR ******* */

$smarty->assign("day", $project->getStartDay());
$smarty->assign("turn", $project->getStartHour());
$smarty->assign("initiator", $project->getInitiator());

try {
  $initiator = Character::loadById($project->getInitiator());

  if ($initiator->isInSameLocationAs($project) && $initiator->isAlive()) {
    $smarty->assign("initiatorId", $initiator->getId());
  }
} catch (InvalidArgumentException $e) {
} // very old projects are initiated by characters removed from the database


/* ******* PROGRESS ******* */

$smarty->assign("progress", TextFormat::getPercentFromFraction($project->getFractionDone(), 1));
$smarty->assign("worktime", ceil($project->getTurnsNeeded() / 100));


/* ******* MATERIALS ******* */

$neededArray = Parser::rulesToArray($project->getReqNeeded());
$leftArray = Parser::rulesToArray($project->getReqLeft());

$neededInfoList = [];
if (array_key_exists('raws', $neededArray)) { // if there's a "raws" section
  $rawsNeededArray = Parser::rulesToArray($neededArray['raws'], ",>");
  $rawsLeftArray = Parser::rulesToArray($leftArray['raws'], ",>");
  foreach ($rawsNeededArray as $rawName => $needed) { // iterate through required objects
    $left = $rawsLeftArray[$rawName];
    $done = $needed - $left;
    $rawTag = TagUtil::getRawTagByName($rawName);
    $neededInfo = [];
    $neededInfo['rawTag'] = $rawTag;
    $neededInfo['done'] = $done;
    $neededInfo['needed'] = $needed;
    $neededInfo['left'] = $left;
    $neededInfoList[] = $neededInfo;
  }
}
$smarty->assign("needed", $neededInfoList);

/* ******* OBJECTS ******* */

$objects = [];
if ($neededArray['objects']) { // if there's "objects" section
  $objNeededArray = Parser::rulesToArray($neededArray['objects'], ",>");
  $objLeftArray = Parser::rulesToArray($leftArray['objects'], ",>");

  foreach ($objNeededArray as $objName => $needed) { // iterate through required objects
    $left = $objLeftArray[$objName];
    $done = $needed - $left;

    $subobject = ObjectHandler::getObjectTypeTagByName($objName);
    $objects[] = "$subobject (<CANTR REPLACE NAME=page_build_objneeded AMOUNT1=$done AMOUNT2=$needed AMOUNT3=$left>)";
  }
}
$smarty->assign("objects", $objects);


/* OBJECTS - target of project */

$objsNear = [];
if ($neededArray['objectid']) {

  $objNeededNear = explode(",", $neededArray['objectid']);

  foreach ($objNeededNear as $objectId) {
    $objNear = [];

    $objNear['id'] = $objectId;
    $isObjectNear = ObjectHandler::isObjectInLocation($objectId, $project->getLocation());
    $objNear['isNear'] = $isObjectNear;

    $objsNear[] = $objNear;
  }
}
$smarty->assign("objsNear", $objsNear);

/* ******* TOOLS ******* */

$toolsNeeded = [];
if ($neededArray['tools']) {
  $toolsArray = explode(",", $neededArray['tools']);

  foreach ($toolsArray as $toolName) {
    $toolInfo = [];
    $toolInfo['isPresent'] = CObject::inInventoryOf($char->getId())->name($toolName)->exists();

    $objectName = "item_" . str_replace(" ", "_", $toolName) . "_o";
    $toolInfo['name'] = "<CANTR REPLACE NAME=" . $objectName . ">";
    $toolsNeeded[] = $toolInfo;
  }
} elseif ($project->getType() == ProjectConstants::TYPE_ALTERING_SIGN) {
  $stm = $db->prepare("SELECT COUNT(*) FROM objects INNER JOIN objecttypes ON objecttypes.id = objects.type
    WHERE objects.person = :charId AND objecttypes.rules like '%signwriting%' LIMIT 1");
  $stm->bindInt("charId", $char->getId());

  $toolInfo = [];
  $toolInfo['isPresent'] = $stm->executeScalar();
  $toolInfo['name'] = "<CANTR REPLACE NAME=project_info_any_signwriting_tools>";

  $toolsNeeded[] = $toolInfo;
} elseif ($project->getType() == ProjectConstants::TYPE_BUTCHERING_ANIMAL) {
  $stm = $db->prepare("SELECT o.id FROM rawtools rtl
    INNER JOIN objects o ON o.type = rtl.tool AND o.person = :charId
    WHERE rtl.projecttype = :projectType");
  $stm->bindInt("charId", $char->getId());
  $stm->bindInt("projectType", ProjectConstants::TYPE_BUTCHERING_ANIMAL);
  $hasTool = $stm->executeScalar();

  $toolInfo['isPresent'] = $hasTool != null;
  $toolInfo['name'] = "<CANTR REPLACE NAME=item_cleaver_o>";

  $toolsNeeded[] = $toolInfo;
}
$smarty->assign("toolsNeeded", $toolsNeeded);

/* ******* ADDITIONAL TOOL BOOST ******* */

$toolBoostInfo = [];

$toolUsed = $project->getBoostingTool($char);
if ($toolUsed != null && strpos($project->getReqNeeded(), "ignorerawtools") === false) {
  try {
    $type = ObjectType::loadById($toolUsed->type);
    $objectName = "item_" . $type->getUniqueName() . "_o";
    $toolBoostInfo['name'] = $objectName;
    $toolBoost = ($toolUsed->boost - 100);
    $sign = "+";
    if ($toolBoost < 0) {
      $sign = "-";
    }
    $boost = $sign . $toolBoost;
    $toolBoostInfo['boost'] = $boost;
  } catch (InvalidArgumentException $e) {
    Logger::getLogger("info.project")->error("Unable to load object type" . $toolUsed->type . " whose info was visible");
  }
}

$smarty->assign("toolBoost", $toolBoostInfo);

if (array_key_exists("agricultural", $neededArray) && $neededArray['agricultural']) {
  try {
    $projectLocation = Location::loadById($project->getLocation());
    if ($projectLocation->isOutside()) {
      $agriculturalConditions = Weather::loadByPos($projectLocation->getX(), $projectLocation->getY())->getAgriculturalConditions();
      $smarty->assign("agriculturalConditions", $agriculturalConditions->getDescriptiveHarvestEfficiency());
    }
  } catch (InvalidArgumentException $e) {
    Logger::getLogger("info.project")->warn("Unable to load location {$project->getLocation()} of project {$project->getId()}");
  }
}

/* ***** DESIRED OUTPUT ****** */

if ($project->getType() == ProjectConstants::TYPE_GATHERING) {
  $results = preg_split("/:/", $project->getResult());
  $smarty->assign("quantity", $results[1]);
}
$smarty->assign("result", $results[1]);

if ($project->getType() == ProjectConstants::TYPE_ALTERING_SIGN) {
  $signResults = [];
  $results = preg_split("/[:]/", $project->getResult(), 3);

  $stm = $db->prepare("SELECT name FROM signs WHERE location = :locationId AND signorder = :signorder LIMIT 1");
  $stm->bindInt("locationId", $project->getSubtype());
  $stm->bindInt("signorder", $results[1]);
  $sign_name = $stm->executeScalar();
  switch ($results[0]) {
    case NamingConstants::SIGN_CHANGE:
      $signResults['type'] = "change";
      $signResults['number'] = $results[1];
      $signResults['name'] = $sign_name[0];
      $signResults['newName'] = urldecode($results[2]);
      break;
    case NamingConstants::SIGN_REMOVE:
      $signResults['type'] = "remove";
      $signResults['number'] = $results[1];
      $signResults['name'] = $sign_name[0];
      break;
    case NamingConstants::SIGN_MOVE:
      $signResults['type'] = "move";
      $signResults['number'] = $results[1];
      $signResults['name'] = $sign_name[0];
      $signResults['position'] = $results[2];
      break;
    case NamingConstants::SIGN_ADD:
      $signResults['type'] = "add";
      $signResults['number'] = $results[1];
      $signResults['newName'] = urldecode($results[2]);
      break;
    default:
      break;
  }
  $smarty->assign("signResults", $signResults);
}


/* ***** SHORTCUT BUTTONS ***** */


$joinProject = ($project->getWayOfProgression() != ProjectConstants::PROGRESS_AUTOMATIC) && !$char->isBusy();
$smarty->assign("joinProject", $joinProject);
$smarty->assign("projectId", $project->getId());
$smarty->assign("projectType", $project->getType());
/* ************************* */

$stm = $db->prepare("SELECT * FROM projects WHERE id = :projectId"); // fallback
$stm->bindInt("projectId", $project->getId());
$stm->execute();
$project_info = $stm->fetchObject();
$smarty->assign("project_info", $project_info);
$smarty->assign("character", $character);

$charInfo = new CharacterInfoView($char);
$charInfo->show();

$worker = $char;
if ($project->getWayOfProgression() == ProjectConstants::PROGRESS_AUTOMATIC) {
  $worker = null;
}
$problems = $project->validateProgress($worker);
$smarty->assign("problems", $problems);

show_title("<CANTR REPLACE NAME=title_project>: " . $project->getName());
$smarty->displayLang("info.project.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
