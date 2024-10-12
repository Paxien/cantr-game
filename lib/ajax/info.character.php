<?php

$ocharId = HTTPContext::getInteger('ochar');

$i_inventory = $_REQUEST['i_inventory'];

try {
  $ochar = Character::loadById($ocharId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("", "error_too_far_away");
}


if (!$char->isNearTo($ochar)) {
  $tooFarAway = true;
}

$nameTag = new Tag("<CANTR CHARNAME ID=" . $ochar->getId() . ">", false);
$nameTag = new Tag($nameTag->interpret(), false);
$name = $nameTag->interpret();

$db = Db::get();
$stm = $db->prepare("SELECT name FROM charnaming WHERE observer = :observer
  AND observed = :observed AND type = 1");
$stm->bindInt("observer", $char->getId());
$stm->bindInt("observed", $ochar->getId());
$rawName = $stm->executeScalar();

try {
  $project = Project::loadById($ochar->getProject());

  $projectId = $project->getId();
  $projectTag = new Tag($project->getName(), false);
  $projectTag = new Tag($projectTag->interpret(), false);
  $projectTag = $projectTag->interpret();
  $skillId = $project->getSkill();
} catch (InvalidArgumentException $e) {
  $projectId = null;
  $projectTag = null;
  $skillId = 0;
}

try {
  if ($char->getId() != $ochar->getId()) {
    throw new InvalidArgumentException("only character can see thy dragging");
  }
  $dragging = Dragging::loadByDragger($ochar->getId());
  $draggingView = new DraggingView($dragging, $ochar);
  $draggingName = $draggingView->getInterpretedName(false);
  $draggingName .= " (" . TextFormat::getPercentFromFraction($dragging->getFractionDone()) . "%)";
} catch (InvalidArgumentException $e) {
  $draggingName = null;
}


$travelView = new CharacterTravelView($ochar, $char);
$travelDesc = $travelView->getInterpretedDescription();
if (empty($travelDesc)) {
  $travelDesc = null;
}

$stm = $db->prepare("SELECT description FROM charnaming
  WHERE observer = :observer AND observed = :observed AND type = 1");
$stm->bindInt("observer", $char->getId());
$stm->bindInt("observed", $ochar->getId());
$personalDesc = $stm->executeScalar();
if (empty($personalDesc)) {
  $personalDesc = "";
}

$charData = array(
  "name" => $name,
  "rawName" => htmlspecialchars_decode($rawName),
  "personalDescription" => $personalDesc,
);

/**
 * @param $skillId
 * @param $ochar Character
 * @return string
 */
function getDescriptiveSkillLevel($skillId, Character $ochar)
{
  if ($skillId > 0) {
    $skillLevel = $ochar->getState($skillId);
    $atag = new Tag("<CANTR REPLACE NAME=skill_adjective_" . get_skill_adjective($skillLevel) . ">");
    return $atag->interpret();
  }
  return "";
}

if (!$tooFarAway) {

  $skillLevel = getDescriptiveSkillLevel($skillId, $ochar);
  $ageDescription = str_replace(" ", "_", $ochar->getDescription());
  $ageTag = new Tag("<CANTR REPLACE NAME=char_" . $ageDescription . ">");

  if ($ochar->getLocation() > 0) {
    $locationTag = new Tag("<CANTR LOCNAME ID=" . $ochar->getLocation() . ">", false);
    $locationTag = new Tag($locationTag->interpret(), false);
    $locationName = $locationTag->interpret();
    $locationTag = new Tag("<CANTR LOCDESC ID=" . $ochar->getLocation() . ">", false);
    $locationTag = new Tag($locationTag->interpret(), false);
    $locationDesc = $locationTag->interpret();
  } else {
    $locationName = null;
    $locationDesc = null;
  }

  import_lib("func.genes.inc.php");
  $health = read_state($ochar->getId(), _GSS_HEALTH);
  $tiredness = read_state($ochar->getId(), _GSS_TIREDNESS);

  $charStates = new CharacterStatesView($ochar, $char);

  $charData = array_merge($charData, array(
    "ageDescription" => $ageTag->interpret(),
    "projectName" => $projectTag,
    "projectId" => $projectId,
    "projectSkillLevel" => $skillLevel,
    "dragging" => $draggingName,
    "travelling" => $travelDesc,
    "locationName" => $locationName,
    "locationType" => $locationDesc,
    "states" => array(
      "health" => floor($health / 100),
      "tiredness" => floor($tiredness / 100),
    ),
    "stateDescriptions" => $charStates->getInterpretedStateDescriptions(),
  ));
}

/*
 * BEGIN Helper functions for creating clothing list
 */

$isNotHidden = function($clothing) {
  return !$clothing['hidden'];
};

$toEventPageFormat = function($clothing)
{
  return array(
    "name" => $clothing['name'],
    "description" => $clothing['desc'],
  );
};

/*
 * END Helper functions for creating clothing list
 */

if (!$tooFarAway && $i_inventory) {

  $charInventory = new CharacterInventoryView($ochar, $char);

  $charData = array_merge($charData, array(
    "inventory" => $charInventory->getVisibleItems(),
    "clothes" => array_values(array_map($toEventPageFormat,
        array_filter($charInventory->getInterpretedClothes(), $isNotHidden))
    ),
    "description" => nl2br($ochar->getCustomDesc()),
  ));
}

$toEncode = array("character" => $charData);

if ($tooFarAway) {
  $tag = new Tag("<CANTR REPLACE NAME=error_too_far_away>");
  $toEncode["e"] = $tag->interpret();
}

echo json_encode($toEncode);

