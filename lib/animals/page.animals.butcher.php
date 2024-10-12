<?php

$character = HTTPContext::getInteger('character');
$object_id = HTTPContext::getInteger('object_id');

import_lib("func.rules.inc.php");

if ( !objectHaveAccessToAction($object_id, "animal_butcher") ) {
  CError::throwRedirect("char.inventory", "error_butcher_not_possible");
}

if ($char->getLocation() == 0) {
  CError::throwRedirectTag("char.inventory", "error_not_while_travel");
}

$db = Db::get();
$stm = $db->prepare("SELECT o.id FROM objects o INNER JOIN objecttypes ot ON ot.id = o.type
  WHERE o.location = :locationId AND ot.objectcategory = :category");
$stm->bindInt("locationId", $char->getLocation());
$stm->bindInt("category", ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS);
$stm->execute();

$animalNames = [];
foreach ($stm->fetchScalars() as $animalObjectId) {
  $animalObject = DomesticatedAnimalObject::loadFromDb($animalObjectId);
  
  $butcherRaws = $animalObject->getRawPoolArray("butchering_raws");
  $part = 0;
  if (count($butcherRaws)) {
    $part = 1;
    foreach ($butcherRaws as $raw) {
      $part = min($part, $raw['amount'] / $raw['maxAmount']);
    }
  }
  $animalNames[$animalObject->getId()] = $animalObject->getNameTag();
  
  $loyalText = "";
  if ($animalObject->getLoyalTo()) {
    $loyalText = " <CANTR REPLACE NAME=animal_loyal_to OWNER=". $animalObject->getLoyalTo() .">";
  }
  
  $message = "<CANTR REPLACE NAME=project_slaughtering> " . $animalObject->getNameTag() .
    "<br>$loyalText <br><CANTR REPLACE NAME=level_text RAW=meat> <CANTR REPLACE NAME=level_". level($part) .">";

  $text[$animalObject->getId()] = TagBuilder::forText($message)->observedBy($char)->build()->interpret();
}

$smarty = new CantrSmarty;

$smarty->assign("animals", $animalNames);
$smarty->assign("object_id", $object_id);
$smarty->assign("butcher_text", json_encode($text));

$smarty->displayLang("animals/page.animals.butcher.tpl", $lang_abr);

function level ($part) {
  if ($part >= 0.85) return "very_high";
  elseif ($part >= 0.7) return "high";
  elseif ($part >= 0.6) return "above_average";
  elseif ($part >= 0.45) return "average";
  elseif ($part >= 0.3) return "low";
  elseif ($part >= 0.15) return "very_low";
  else return "minimal";
}
