<?php

include_once "func.genes.inc.php";

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

$smarty = new CantrSmarty;

try {
  $object = CObject::loadById($object_id);
  $rawObject = new Resource($object);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if (!$char->hasInInventory($object)) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

$notEatAfterNearDeath = Limitations::getLims($character, Limitations::TYPE_NOT_EAT_AFTER_NEAR_DEATH);

if ($notEatAfterNearDeath > 0) {
  $secsLeft = Limitations::getTimeLeft($character, Limitations::TYPE_NOT_EAT_AFTER_NEAR_DEATH);
  $timeLeft = Limitations::ctodhms($secsLeft);
  CError::throwRedirect("char.events",
    "<CANTR REPLACE NAME=error_not_eat_after_near_death DAYS={$timeLeft['day']} HOURS={$timeLeft['hour']} MINS={$timeLeft['minute']}>");
}

$charStomach = CharacterStomach::ofCharacter($char);

$fullness = $charStomach->getStomachContentsWeight();

$capacity = $charStomach->getStomachMaxCapacity() - $fullness;

$rawname = $rawObject->getUniqueName();

$tag = new tag;
$tag->language = $l;
$tag->contents = "<CANTR REPLACE NAME=raw_$rawname>";


/* ********** VALIDATION ************ */


if (!$rawObject->isEdible()) {
  CError::throwRedirect("char.events", "<CANTR REPLACE NAME=error_cannot_eat_raw TYPE=$rawname>");
}

if ($fullness >= $charStomach->getStomachMaxCapacity()) $full=1;
else $full=0;

function perHundredGrams($rawstrength, $type)
{  
  if ($type=="damage") {
    $factor=_EAT_PERCENT_BACK_HEALING;
    $percdir=-1;//negative because the output is called damage
  }

  if ($type=="tiredness") {
    $factor=_EAT_PERCENT_BACK_TIREDNESS;
    $percdir=-1;
  }

  if ($type=="drunkenness") {
    $factor=_EAT_PERCENT_DRUNKENNESS;
    $percdir=1;
  }
  
  if ($type=="hunger") {
    $factor=_EAT_PERCENT_BACK;
    $percdir=-1;
  }
  
  $perc_up = $rawstrength * $factor;
  $perc_up = (floor ($perc_up) * $percdir)/100;
	return $perc_up;
}

function neededForMax($character, $rawstrength, $type)
{  
  if ($type=="strength") {
    $factor=_EAT_PERCENT_BACK_HEALING;
    $needed = _SCALESIZE_GSS-read_state($character, _GSS_HEALTH);
  }

  if ($type=="energy") {
    $factor=_EAT_PERCENT_BACK_TIREDNESS;
    $needed = read_state($character, _GSS_TIREDNESS);
  }

  if ($type=="drunkenness") {
    $factor=_EAT_PERCENT_DRUNKENNESS;
    $needed = CharacterConstants::PASSOUT_LIMIT - read_state($character, _GSS_DRUNKENNESS);
  }
  
  if ($type=="hunger") {
    $factor=_EAT_PERCENT_BACK;
    $needed = read_state($character, _GSS_HUNGER);
  }
  
	return floor($needed/$rawstrength/$factor*100);
}

if ($rawObject->getNutrition()) $hunger100 = perHundredGrams($rawObject->getNutrition(), "hunger");
else $hunger100 = 0;
if ($rawObject->getStrengthening()) $dmg100 = perHundredGrams($rawObject->getStrengthening(), "damage");
else $dmg100 = 0;
if ($rawObject->getDrunkenness()) $drunk100 = perHundredGrams($rawObject->getDrunkenness(), "drunkenness");
else $drunk100 = 0;
if ($rawObject->getEnergy()) $tired100 = perHundredGrams($rawObject->getEnergy(), "tiredness");
else $tired100 = 0;

if ($rawObject->getNutrition()>0) $hungerMax = neededForMax($character, $rawObject->getNutrition(), "hunger");
else $hungerMax = 0;
if ($rawObject->getStrengthening()>0) $healMax = neededForMax($character, $rawObject->getStrengthening(), "strength");
else $healMax = 0;
if ($rawObject->getDrunkenness()>0) $drunkMax = neededForMax($character, $rawObject->getDrunkenness(), "drunkenness");
else $drunkMax = 0;
if ($rawObject->getEnergy()>0) $energyMax = neededForMax($character, $rawObject->getEnergy(), "energy");
else $energyMax = 0;

if (!$hungerMax&&!$healMax&&!$drunkMax&&!$energyMax) $nothingToGive = 1;
else $nothingToGive = 0;

/* ********** FORM ************* */

$smarty->assign ("rawname", $rawname);

if ($object->getWeight() > $capacity)
  $max = $capacity;
else
  $max = $object->getWeight();
	
$backlink="index.php?page=char.inventory";

$smarty->assign ("TYPE", $rawname);
$smarty->assign ("WEIGHT", $object->getWeight());
$smarty->assign ("MAX", $max);
$smarty->assign ("CAPACITY", $capacity);
$smarty->assign ("object_id", $object_id);
$smarty->assign ("max", $max);
$smarty->assign ("dmg100", $dmg100);
$smarty->assign ("hunger100", $hunger100);
$smarty->assign ("drunk100", $drunk100);
$smarty->assign ("tired100", $tired100);
$smarty->assign ("full", $full);
$smarty->assign ("backlink", $backlink);
$smarty->assign ("healMax", $healMax);
$smarty->assign ("hungerMax", $hungerMax);
$smarty->assign ("drunkMax", $drunkMax);
$smarty->assign ("energyMax", $energyMax);
$smarty->assign ("nothingToGive", $nothingToGive);

$smarty->displayLang ("form.eatraw.tpl", $lang_abr); 
