<?php

include_once("func.genes.inc.php");

$ocharid = HTTPContext::getInteger('ocharid');


$db = Db::get();

try {
  $otherChar = Character::loadById($ocharid);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("char.events", "error_no_char");
}

$lookingAtYourself = $otherChar->getId() == $char->getId();
$isnear = $char->isNearTo($otherChar);

$whenBodyVisible = array(CharacterConstants::CHAR_DECEASED, CharacterConstants::CHAR_BEING_BURIED);
if (in_array($otherChar->getStatus(), $whenBodyVisible) && ($otherChar->getLocation() == $char->getLocation())) {
  redirect("search", ["id" => $ocharid]);
  exit;
}


$tag = new tag;
$tag->language = $l;
$tag->content = "<CANTR CHARNAME ID=". $otherChar->getId() .">";
$tag->html = false;
$tag->character = $character;
$name = $tag->interpret();

$smarty = new CantrSmarty();

$smarty->assign("isnear", $isnear);
$smarty->assign("lookingAtYourself", $lookingAtYourself);

$smartyChar = array();
$smartyChar['name'] = $name;
$smartyChar['id'] = $ocharid;
$smartyChar['ageInDecades'] = floor($otherChar->getAgeInYears()/10);
$smartyChar['location'] = $otherChar->getLocation();

if ($isnear) {

  if ($otherChar->isMale()) {
    $sex = "m";
    $he = '<CANTR REPLACE NAME=char_desc_he>';
    $his = '<CANTR REPLACE NAME=char_desc_his>';
    $him = '<CANTR REPLACE NAME=char_desc_him>';
  } else {
    $sex = "f";
    $he = '<CANTR REPLACE NAME=char_desc_she>';
    $his = '<CANTR REPLACE NAME=char_desc_her>';
    $him = '<CANTR REPLACE NAME=char_desc_her>';
  }

  $smartyChar['sex'] = $sex;
  $smartyChar['he'] = $he;
  $smartyChar['his'] = $his;
  $smartyChar['him'] = $him;

  if ($otherChar->getProject() > 0) {
    try {
      $project = Project::loadById($otherChar->getProject());
      if ($project->getSkill() != StateConstants::NONE) {
        $skill_level = $otherChar->getState($project->getSkill());
        $smartyChar['skillLevelName'] = get_skill_adjective($skill_level);
      }
      $smartyChar['projectName'] = $project->getName();
      $smartyChar['projectId'] = $otherChar->getProject();
    } catch (InvalidArgumentException $e) {
      Logger::getLogger(__FILE__)->warn("Inexistent project " . $otherChar->getProject()
        . " worked on by " . $otherChar->getId());
    }
  }

  if ($lookingAtYourself) {
    $strength_level = read_state($ocharid, _GSS_STRENGTH);
    $smartyChar['strengthLevelName'] = get_strength_adjective($strength_level);

    $smarty->assign("BORNDATE", $otherChar->getRegister());
    $smarty->assign("BORNLOC", $otherChar->getSpawningLocation());
  }

  if (!$lookingAtYourself) {
    $charStates = new CharacterStatesView($otherChar, $char);
    $states = $charStates->getStateDescriptions();

    $smartyChar['hungerDescription'] = $states["hunger"];
    $smartyChar['drunkennessDescription'] = $states["drunkenness"];
  }

  if ($otherChar->isNearDeath()) {
    $smartyChar['isNearDeath'] = true;
    $smartyChar['nearDeathCured'] = $otherChar->getNearDeathState() == CharacterConstants::NEAR_DEATH_HEALED;
  }

  $charDiseases = array();

  $stm = $db->prepare("SELECT COUNT(*) FROM diseases WHERE disease = 2 AND person = :charId LIMIT 1");
  $stm->bindInt('charId', $otherChar->getId());
  $faintDiseases = $stm->executeScalar();
  $charDiseases['faint'] = $faintDiseases > 0;

  $smartyChar['diseases'] = $charDiseases;

  $publicDesc = $otherChar->getCustomDesc();

  $smartyChar['description'] = $publicDesc;

  $charInventory = new CharacterInventoryView($otherChar, $char);
  $smartyChar['clothes'] = $charInventory->getClothes();

//travel infomation

  $travelView = new CharacterTravelView($otherChar, $char);
  $smartyChar['travelText'] = $travelView->getDescription();

  $charInventory = new CharacterInventoryView($otherChar, $char);
  $smartyChar['inventory'] = $charInventory->getVisibleItems();

  $smartyYou = array();

  if (($char->getLocation() > 0) && !$char->isBusy()) {

    if (($otherChar->getProject() > 0) && ($otherChar->getLocation() == $char->getLocation())) {
      $smartyYou['canJoinProject'] = true;
      $smartyChar['project'] = $otherChar->getProject();
    } elseif ($otherChar->isDragging()) {
      $smartyYou['canJoinDragging'] = true;
    }
    $smartyYou['canDrag'] = true;
  }

  $smartyYou['progressBars'] = PlayerSettings::getInstance($player)->get(PlayerSettings::PROGRESS_BARS);

  $smartyChar['damage'] = 1 - read_state($ocharid, _GSS_HEALTH) / _SCALESIZE_GSS;
  $smartyChar['damagePerc'] = floor($smartyChar['damage'] * 100);

  $smartyChar['tiredness'] = read_state($ocharid, _GSS_TIREDNESS) / _SCALESIZE_GSS;
  $smartyChar['tirednessPerc'] = floor($smartyChar['tiredness'] * 100);

  if ($lookingAtYourself) {
    $smartyChar['hunger'] = read_state($ocharid, _GSS_HUNGER) / _SCALESIZE_GSS;
    $smartyChar['hungerPerc'] = floor($smartyChar['hunger'] * 100);

    $smartyChar['drunkenness'] = read_state($ocharid, _GSS_DRUNKENNESS) / _SCALESIZE_GSS;
    $smartyChar['drunkennessPerc'] = floor($smartyChar['drunkenness'] * 100);

    $stm = $db->prepare("SELECT SUM(weight) as fullness FROM stomach WHERE person = :charId");
    $stm->bindInt("charId", $char->getId());
    $smartyChar['fullness'] = intval($stm->executeScalar());
  }

} // This closes the block that is not visible, if the other character is too far away.

if ($lookingAtYourself) {
  $stm = $db->prepare("
    SELECT type, value FROM states s
    WHERE person = :charId AND type NOT IN (5, 6, 7, 8, 10, 12, 13, 14, 15) ORDER BY VALUE desc");
  $stm->bindInt("charId", $char->getId());
  $stm->execute();
  $skills = $stm->fetchAll();

  if (count($skills)) {
    foreach ($skills as $skill) {
      if ($count++ * 2 < count($skills)) {
        $evenlines[] = array("value" => get_skill_adjective($skill->value), "type" => $skill->type);
      } else {
        $oddlines[] = array("value" => get_skill_adjective($skill->value), "type" => $skill->type);
      }
    }

    $smartySkills['evenLines'] = $evenlines;
    $smartySkills['oddLines'] = $oddlines;
  }
}

$stm = $db->prepare("SELECT name, description FROM charnaming
  WHERE observer = :observerId AND observed = :observedId AND type = 1");
$stm->bindInt("observerId", $char->getId());
$stm->bindInt("observedId", $otherChar->getId());
$stm->execute();
if ($charname_info = $stm->fetchObject()) {
  //in database in NAME we had "<CANTR CHARDESC>" in plain text and others special chars in html entieties. let normalize it.
  $charname_info->name = str_replace( "<CANTR CHARDESC>", htmlentities( "<CANTR CHARDESC>" ), $charname_info->name );

  $name = $charname_info->name;
  $description = html_entity_decode (str_replace ("<br />", "\n", $charname_info->description));
} else {
  $description = "";
  if ($lookingAtYourself) {
    $name = html_entity_decode($char->getName());
  } else {
    $name = "";
  }
}

$smartyChar['nameInForm'] = $name;
$smartyYou['yourDescription'] = $description;

if ($lookingAtYourself) {
  $descLims = Limitations::getLims($player, Limitations::TYPE_PLAYER_CHARDESCRIPTION);
  $smartyYou['isDescriptionAllowed'] = $descLims == 0;
}

$constants = array(
  "STOMACH_CAPACITY" => _STOMACH_CAPACITY,
  "STATE_HUNGER_1" => _STATE_HUNGER_1,
  "STATE_HUNGER_2" => _STATE_HUNGER_2,
  "STATE_HUNGER_3" => _STATE_HUNGER_3,
);

$smarty->assign("constants", $constants);
$smarty->assign("char", $smartyChar);
$smarty->assign("you", $smartyYou);
$smarty->assign("travel", $smartyTravel); // TODO this variable is always empty and thus unused in template
$smarty->assign("skills", $smartySkills);
$smarty->displayLang("page.characterdescription.tpl", $lang_abr);

