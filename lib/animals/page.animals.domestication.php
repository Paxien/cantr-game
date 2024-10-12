<?php

$charInfo = new CharacterInfoView($char);
$charInfo->show();

$smarty = new CantrSmarty;

$smarty->assign("locname", TagBuilder::forLocation($char->getLocation())->allowHtml(true)->build()->interpret());

$db = Db::get();
$stm = $db->prepare("SELECT id FROM animals WHERE location = :locationId");
$stm->bindInt("locationId", $char->getLocation());
$stm->execute();
$animals = [];
foreach ($stm->fetchScalars() as $animalObjectId) {
  $animalPack = AnimalPack::loadFromDb($animalObjectId);

  $animalData = [];
  $animalData['name'] = $animalPack->getNameTag();
  $animalData['number'] = $animalPack->getNumber();
  $actions = $animalPack->getDomesticationActions();

  $animalData['domesticated'] = $animalPack->isDomesticated();
  if ($animalPack->isDomesticated()) {
    $animalTypeData = $animalPack->getTypeDetailsString();
    $animalTypeData = Parser::rulesToArray($animalTypeData);

    $animalSpec = $animalPack->getSpecificsString();
    $animalSpec = Parser::rulesToArray($animalSpec);
  }

  foreach ($actions as $actName => &$details) {
    if (in_array($actName, ["milking", "shearing", "collecting"])) {
      list ($rawName, $rawAmount) = explode(">", $animalSpec[$actName . "_raws"]);
      list ($rawTypeName, $rawMax) = explode(">", $animalTypeData[$actName . "_raws"]);

      $part = 0;
      if ($rawMax > 0) {
        $part = $rawAmount / $rawMax;
      }

      $rawName = str_replace(" ", "_", $rawName);
      $details = "<CANTR REPLACE NAME=animal_harvesting_{$actName} ANIMAL=" . $animalPack->getName() . "><br>";
      $details .= "<CANTR REPLACE NAME=level_text RAW={$rawName}> <CANTR REPLACE NAME=level_" . level($part) . "><br>";
      if ($animalTypeData[$actName . "_tools"]) {
        $details .= "<CANTR REPLACE NAME=tools_needed> ";
        $toolsArray = explode(",", $animalTypeData[$actName . "_tools"]);
        foreach ($toolsArray as &$tool) {
          $tool = "<CANTR REPLACE NAME=item_" . str_replace(" ", "_", $tool) . "_o>";
        }
        $details .= implode(", ", $toolsArray);
      }
    } elseif ($actName == "separating") {
      $details = "<CANTR REPLACE NAME=animal_pack_separate_text>";
      $butcherRaws = Parser::rulesToArray($animalSpec["butchering_raws"], ",>");
      $maxButcherRaws = Parser::rulesToArray($animalTypeData["butchering_raws"], ",>");
      $part = 0;
      if (count($butcherRaws) > 0) {
        $part = 1;
        foreach ($butcherRaws as $rawName => $amount) {
          $part = min($part, $amount / explode(">", $maxButcherRaws[$rawName])[0]);
        }
        $details .= "<br><CANTR REPLACE NAME=level_text RAW=meat> <CANTR REPLACE NAME=level_" . level($part) . ">";
      }
    } elseif ($actName == "taming") {
      $details = "<CANTR REPLACE NAME=animal_start_tame>";
    } elseif ($actName == "healing") {
      $wound = $animalPack->getDamage() / $animalPack->getStrength();
      $maxAmount = 10 * $animalPack->getFoodAmount();
      $amount = round($wound * $maxAmount);

      $rawtypeId = reset($animalPack->getFoodTypes()); // first possible food amount type
      $charHas = ObjectHandler::getRawFromPerson($character, $rawtypeId);
      $rawtypeName = str_replace(" ", "_", ObjectHandler::getRawNameFromId($rawtypeId));

      $details = "<CANTR REPLACE NAME=animal_pack_heal_text AMOUNT=$amount RAWTYPE=$rawtypeName CHARHAS=$charHas ANIMAL=" . $animalPack->getName() . ">";
    } else {
      $details = "Not available";
    }
    $tag = new Tag();
    $tag->html = true;
    $tag->content = $details;
    $details = $tag->interpret();
  }

  if ($animalPack->isDomesticated()) {
    $animalData['fullness'] = Animal::getFedTagFromValue($animalPack->getFullness());
  }

  $animals[$animalObjectId] = $animalData;

  $animal_actions[$animalObjectId] = $actions;
}

uasort($animals, function ($a, $b)
{
  return $b['domesticated'] - $a['domesticated'];
});

$smarty->assign("animals", $animals);
$smarty->assign("animal_actions", json_encode($animal_actions));

$smarty->displayLang("animals/page.animals.domestication.tpl", $lang_abr);

function level($part)
{
  if ($part >= 0.85) return "very_high";
  elseif ($part >= 0.7) return "high";
  elseif ($part >= 0.6) return "above_average";
  elseif ($part >= 0.45) return "average";
  elseif ($part >= 0.3) return "low";
  elseif ($part >= 0.15) return "very_low";
  else return "minimal";
}

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
