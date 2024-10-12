<?php

$object_id_a = HTTPContext::getInteger('object_id');
$validate = HTTPContext::getInteger('validate');

$logger = Logger::getLogger(__FILE__);

$animalObject = DomesticatedAnimalObject::loadFromDb($object_id_a);

if ($animalObject->getType() == null) { // it's probably a race condition
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

if (!$char->isInSameLocationAs($animalObject)) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

try {
  $location = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

$db = Db::get();
$stm = $db->prepare("SELECT id, number FROM animals WHERE type = :type AND location = :locationId");
$stm->bindInt("type", $animalObject->getType());
$stm->bindInt("locationId", $char->getLocation());
$stm->execute();
list ($pack_id, $pack_number) = $stm->fetch(PDO::FETCH_NUM);


if (!$location->isOutside()) {
  CError::throwRedirectTag("char.objects", "error_animal_packs_only_outside");
}

$areaName = $location->getTypeUniqueName();
$stm = $db->prepare("SELECT area_types FROM animal_types WHERE id = :id");
$stm->bindInt("id", $animalObject->getType());
$areaTypes = $stm->executeScalar();
$areaTypes = explode(",", $areaTypes);

if ( !in_array($areaName, $areaTypes) ) {
  CError::throwRedirectTag("char.objects", "error_animal_incorrect_areatype");
}

if ($animalObject->getLoyalTo() != 0 && $animalObject->getLoyalTo() != $char->getId()) {
  CError::throwRedirectTag("char.objects", "error_animal_not_loyal_to_you");
}

// check if somebody adopts or slaughters that animal right now
$stm = $db->prepareWithIntList( "SELECT ch.id FROM projects p 
  INNER JOIN chars ch ON ch.project = p.id
  WHERE p.type IN (:types)
  AND p.subtype = :subtype AND p.location = :locationId", [
  "types" => [ProjectConstants::TYPE_ADOPTING_ANIMAL, ProjectConstants::TYPE_BUTCHERING_ANIMAL],
]);
$stm->bindInt("subtype", $animalObject->getId());
$stm->bindInt("locationId", $animalObject->getLocation());
$adopter = $stm->executeScalar();
if ($adopter > 0) {
  CError::throwRedirectTag("char.objects", "error_animal_in_active_project");
}

$stm = $db->prepare("SELECT max_in_location FROM animal_types WHERE id = :id");
$stm->bindInt("id", $animalObject->getType());
$max_in_location = $stm->executeScalar();
if ($max_in_location == 0) {
  CError::throwRedirectTag("char.objects", "error_animal_not_live_in_pack");
}

if ($max_in_location == 1 || (($pack_number + 1) % $max_in_location == 1)) { // if that action will consume another digging slot
  if (Location::getUsedDiggingSlots ($char->getLocation()) >= Location::getMaxDiggingSlots($char->getLocation()) ) { // if no digging slots available
    CError::throwRedirectTag("char.objects", "error_max_digging_slots");
  }
}

$enclosure = CObject::locatedIn($animalObject->getLocation())->type(AnimalConstants::OBJECTTYPE_ANIMAL_ENCLOSURE_ID)->find();
if ($enclosure != null) {
  $keyLock = KeyLock::loadByObjectId($enclosure->getId());
  if (!$keyLock->canAccess($char->getId())) {
    $keyLock->redirectToLockpicking();
  }
}

$animalIsNotEmpty = CObject::storedIn($animalObject->getId())->exists();
if ($animalIsNotEmpty) {
  CError::throwRedirectTag("char.objects", "error_animal_carrying_objects");
}

if ($animalObject->getLoyalTo() == $char->getId() && !$validate ) {
  $smarty = new CantrSmarty;
  $smarty->assign("object_id", $object_id_a);
  $smarty->displayLang("animals/page.animals.pack_join.tpl", $lang_abr);
}
else { // can join with a pack

  // delete butchering and adoption projects on which nobody is working
  $stm = $db->prepareWithIntList("DELETE FROM projects
  WHERE type IN (:types)
  AND subtype = :subtype AND location = :locationId", [
    "types" => [ProjectConstants::TYPE_ADOPTING_ANIMAL, ProjectConstants::TYPE_BUTCHERING_ANIMAL],
  ]);
  $stm->bindInt("subtype", $animalObject->getId());
  $stm->bindInt("locationId", $animalObject->getLocation());
  $stm->execute();
  if ($pack_id != null) { // when there's already a pack
    $pack = AnimalPack::loadFromDb($pack_id);

    if (!$pack->isDomesticated()) {
      throw new Exception("Wrong pack. It's not domesticated");
    }
    
    $success = $pack->incorporateAnimalObject($animalObject);
  } else { // create a new pack
    import_lib("func.expireobject.inc.php");

    $success = expire_object($object_id_a);
    if ($success) {
      $stm = $db->prepare("INSERT INTO animals (location, type, number, damage) VALUES (:locationId, :type, 1, 0)");
      $stm->bindInt("type", $animalObject->getType());
      $stm->bindInt("locationId", $animalObject->getLocation());
      $stm->execute();
      $pack_id = $db->lastInsertId();

      $stm = $db->prepare("UPDATE animal_domesticated SET from_object = 0, loyal_to = 0, from_animal = :animalId WHERE from_object = :objectId");
      $stm->bindInt("animalId", $pack_id);
      $stm->bindInt("objectId", $animalObject->getId());
      $stm->execute();
    }
  }

  if (!$success) {
    $logger->error("Failed to remove animal ". $animalObject->getId() .
      " of type ". $animalObject->getType() ." (char: ". $char->getId() .")");
    CError::throwRedirect("char.objects", "Something went wrong!");
  }
  Event::createPersonalEvent(288, "ANIMAL=". $animalObject->getName(), $character);
  Event::createPublicEvent(289, "ACTOR=$character ANIMAL=". $animalObject->getName(), $character, Event::RANGE_SAME_LOCATION, array($character));
  
  redirect("char.objects");
  exit();
}
