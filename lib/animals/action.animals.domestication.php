<?php

$character = HTTPContext::getInteger('character');
$pack_id = HTTPContext::getInteger('pack_id');
$action_type = $_REQUEST['action_type'];

$pack = AnimalPack::loadFromDb($pack_id);

$db = Db::get();
$logger = Logger::getLogger(__FILE__);

if (!$pack->ok) {
  CError::throwRedirect("animals", "wrong pack id");
}

if (!$char->isInSameLocationAs($pack)) {
  CError::throwRedirectTag("animals", "error_too_far_away");
}

$possibleActions = array("taming", "separating", "healing", "milking", "shearing", "collecting");
if (!in_array($action_type, $possibleActions)) {
  CError::throwRedirect("animals", "wrong action type");
}

if ($action_type == "taming") {

  if ($pack->isDomesticated() || $pack->getDomesticableInto() == null) {
    CError::throwRedirectTag("animals", "error_cant_tame_animal");
  }

  $already_being_tamed = Project::locatedIn($char->getLocation())->type(ProjectConstants::TYPE_TAMING_ANIMAL)->subtype($pack->getDomesticableInto())->exists();

  if ($already_being_tamed) {
    CError::throwRedirectTag("animals", "error_animal_already_tamed");
  }

  $tameRulesArray = $pack->getTameRulesArray(false);
  $turnsNeeded = ($tameRulesArray['days'] ? $tameRulesArray['days'] : 1) * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
  $tameRules = $pack->getTameRulesString();

  $stm = $db->prepare("SELECT weight, of_object_type FROM animal_domesticated_types WHERE of_animal_type = :animalType");
  $stm->bindInt("animalType", $pack->getDomesticableInto());
  $stm->execute();
  list ($animalWeight, $objectType) = $stm->fetch(PDO::FETCH_NUM);

  $domesticatedAnimalType = $pack->getDomesticableInto();
  $resultText = "objects.add:location>var>location,person>0,type>$objectType,weight>$animalWeight,setting>1";

  // Project subconstructors
  $generalSub = new ProjectGeneral("<CANTR REPLACE NAME=project_taming> <CANTR REPLACE NAME=animal_" . $pack->getName() . "_s>", $character, $char->getLocation());
  $typeSub = new ProjectType(ProjectConstants::TYPE_TAMING_ANIMAL, $pack->getDomesticableInto(), 33, 0, 4, 0); // 33 - animal husbandry
  $requirementSub = new ProjectRequirement($turnsNeeded, $tameRules);
  $outputSub = new ProjectOutput($animalWeight, $resultText, 0);

  // create object itself
  $projectObject = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
  $projectObject->saveInDb();


  Event::createPersonalEvent(282, "ANIMAL=" . $pack->getName(), $character);
  Event::createPublicEvent(283, "ACTOR=$character ANIMAL=" . $pack->getName(), $character, Event::RANGE_NEAR_LOCATIONS, array($character));

  redirect("char.events");
  exit();

} elseif ($action_type == "healing") {

  if (!$pack->isDomesticated()) {
    CError::throwRedirectTag("animals", "error_animal_not_domesticated");
  }

  $wounds = $pack->getDamage() / $pack->getStrength();
  $maxAmount = 10 * $pack->getFoodAmount();
  $amount = round($wounds * $maxAmount);

  $foodTypes = $pack->getFoodTypes();
  $rawtype = reset($foodTypes);
  $charHas = ObjectHandler::getRawFromPerson($character, $rawtype);

  $usedAmount = min($amount, $charHas);
  if ($usedAmount > 0) {
    $woundsAfterHealing = $wounds - ($usedAmount / $maxAmount);
    $pack->setDamage(round($woundsAfterHealing * $pack->getStrength()));
    ObjectHandler::rawToPerson($character, $rawtype, (-1) * $usedAmount);
    Event::createPersonalEvent(292, "ANIMAL=" . $pack->getName(), $character);
    Event::createPublicEvent(293, "ACTOR=$character ANIMAL=" . $pack->getName(), $character, Event::RANGE_SAME_LOCATION, array($character));
  }

  redirect("animals");
  exit();
} elseif ($action_type == "separating") {
  $woundedLastInPack = $pack->getNumber() == 1 && $pack->getDamage() > 0;

  if ($woundedLastInPack) {
    CError::throwRedirectTag("animals", "error_animal_cant_separate_wounded");
  }

  $enclosure = CObject::locatedIn($pack->getLocation())->type(AnimalConstants::OBJECTTYPE_ANIMAL_ENCLOSURE_ID)->find();

  if ($enclosure != null) {
    $keyLock = KeyLock::loadByObjectId($enclosure->getId());
    if (!$keyLock->canAccess($char->getId())) {
      $keyLock->redirectToLockpicking();
    }
  }

  if (!$pack->decrementNumber()) {
    $logger->error("Failed to decrement number of animals for " . $pack_id .
      " of type " . $pack->getType() . " (char: " . $char->getId() . ")");
    CError::throwRedirect("animals", "Something went wrong!");
  }

  $stm = $db->prepare("SELECT weight, of_object_type
    FROM animal_domesticated_types WHERE of_animal_type = :animalType");
  $stm->bindInt("animalType", $pack->getType());
  $stm->execute();
  list ($animal_weight, $objectType) = $stm->fetch(PDO::FETCH_NUM);

  $animalObject = ObjectCreator::inLocation($pack->getLocation(), $objectType, ObjectConstants::SETTING_PORTABLE, $animal_weight)
    ->create();

  $stm = $db->prepare("INSERT INTO `animal_domesticated` (from_object, fullness, specifics)
    VALUES (:objectId, :fullness, :specifics)"); // animal-object get the same stats as pack
  $stm->bindInt("objectId", $animalObject->getId());
  $stm->bindInt("fullness", $pack->getFullness());
  $stm->bindStr("specifics", $pack->getSpecificsString());
  $stm->execute();

  Event::createPersonalEvent(290, "ANIMAL=" . $pack->getName(), $character);
  Event::createPublicEvent(291, "ACTOR=$character ANIMAL=" . $pack->getName(), $character, Event::RANGE_SAME_LOCATION, array($character));

  redirect("animals");
  exit();
} else {
  if (!in_array($action_type, array_keys($pack->getDomesticationActions()))) {
    CError::throwRedirect("animals", "wrong action type");
  }
  switch ($action_type) {
    case "milking":
      $projectName = "<CANTR REPLACE NAME=project_milking> ";
      break;
    case "shearing":
      $projectName = "<CANTR REPLACE NAME=project_shearing> ";
      break;
    case "collecting":
      $projectName = "<CANTR REPLACE NAME=project_collecting> ";
  }
  $projectName .= $pack->getNameTag();

  $typeDetails = Parser::rulesToArray($pack->getTypeDetailsString());
  $actionDetails = $typeDetails[$action_type . "_raws"];
  list ($rawName, $maxValue, $dailyIncrease, $dailyHarvest) = explode(">", $actionDetails); // info about the harvesting project

  // tools needed
  $toolDetails = $typeDetails[$action_type . "_tools"];
  $reqNeeded = "";
  if ($toolDetails) {
    $reqNeeded .= "tools:$toolDetails;";
  }

  $turnsNeeded = AnimalConstants::PROJECT_HARVESTING_DAYS * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
  $reqNeeded .= "days:" . AnimalConstants::PROJECT_HARVESTING_DAYS;
  $result = $action_type . ":" . CObject::getRawIdFromName($rawName) . ":" . $dailyHarvest * AnimalConstants::PROJECT_HARVESTING_DAYS;

  $generalSub = new ProjectGeneral($projectName, $character, $char->getLocation());
  $typeSub = new ProjectType(ProjectConstants::TYPE_HARVESTING_ANIMAL, $pack->getType(), 33, 0, 3, 0); // 33 - animal husbandry
  $requirementSub = new ProjectRequirement($turnsNeeded, $reqNeeded);
  $outputSub = new ProjectOutput(0, $result);

  $projectObject = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
  $projectObject->saveInDb();

  $projectName = urlencode($projectName);
  Event::createPersonalEvent(298, "PROJECT=$projectName", $character);
  Event::createPublicEvent(299, "PROJECT=$projectName ACTOR=$character", $character, Event::RANGE_SAME_LOCATION, array($character));

  redirect("char.events");
  exit();
}
