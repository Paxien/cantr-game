<?php


$charInfo = new CharacterInfoView($char);
$charInfo->show();


$smarty = new CantrSmarty;

$smarty->assign("locname", TagBuilder::forLocation($char->getLocation())->allowHtml(true)->build()->interpret());

// Ask the location for the animals nearby, and display them
$db = Db::get();
$stm = $db->prepare("SELECT id FROM animals WHERE location = :locationId GROUP BY type");
$stm->bindInt("locationId", $char->getLocation());
$stm->execute();

$packsToHunt = 0;

$animals_data = array();

foreach ($stm->fetchScalars() as $animalId) {
  $pack = AnimalPack::loadFromDb($animalId);
  $canBeHunted = false;
  if ($pack->isHuntingPossible($character)) {
    $canBeHunted = true;
    $packsToHunt++;
  }


  $animal = array();
  $animal['id'] = $pack->getId();
  $animal['type'] = $pack->getType();
  $animal['name'] = $pack->getNameTag();
  $animal['number'] = $pack->getNumber();
  $animal['can_be_hunted'] = $canBeHunted;
  $animal['is_domesticated'] = $pack->isDomesticated();

  $animals_data[] = $animal;
}

$stm = $db->prepare("SELECT o.id FROM objects o
      INNER JOIN objecttypes ot ON ot.id = o.type
    WHERE o.person = :charId AND ot.rules LIKE '%hit%' GROUP BY o.type");
$stm->bindInt("charId", $char->getId());
$stm->execute();

$weapon_array = array();
foreach ($stm->fetchScalars() as $weaponId) {
  try {
    $weaponData = new Weapon(CObject::loadById($weaponId));
    $weaponData->checkUseBy($char);

    $weapon_array[] = array(
      'hit' => $weaponData->getAnimalHit(),
      'id' => $weaponId,
      'name' => $weaponData->getNameTag(),
    );
  } catch (Exception $e) {
  } // nothing serious, just skip a weapon
}

usort($weapon_array, function ($a, $b) {
  return $a['hit'] < $b['hit'];
});

$weapon_array[] = array(
  "hit" => 1,
  'id' => "manual",
  'name' => "<CANTR REPLACE NAME=weapon_bare_fist>",
);


$smarty->assign("packs_to_hunt", $packsToHunt);
$smarty->assign("animals_data", $animals_data);
$smarty->assign("weapons", $weapon_array);

$smarty->displayLang("animals/page.animals.hunt.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
