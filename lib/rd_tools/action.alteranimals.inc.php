<?php

$region = HTTPContext::getRawString('region');
$language_lookup = HTTPContext::getRawString('language_lookup');

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::ALTER_ANIMAL_PLACEMENT)) {

  // SANITIZE INPUT
  $new_animal = HTTPContext::getInteger('new_animal');
  $location = HTTPContext::getInteger('location');
  $typeid = HTTPContext::getInteger('typeid');

  $db = Db::get();

  if (isset($new_animal) and $new_animal != 0 and isset($number)) {
    if ($number > 0) {
      $number = intval($number);
      $stm = $db->prepare("INSERT INTO animals (location,type,number) VALUES (:location, :type, :number)");
      $stm->bindInt("location", $location);
      $stm->bindInt("type", $new_animal);
      $stm->bindInt("number", $number);
      $stm->execute();
    }
  } elseif (isset($typeid) and isset($number) and isset($oldnumber)) {
    if ($number == 0) {
      $stm = $db->prepare("DELETE FROM animals WHERE location = :location AND type = :type");
      $stm->bindInt("location", $location);
      $stm->bindInt("type", $typeid);
      $stm->execute();
    } elseif (($number > 0) and ($oldnumber != $number)) {
      $stm = $db->prepare("UPDATE animals SET number = :number WHERE type = :type");
      $stm->bindInt("number", $number);
      $stm->bindInt("type", $typeid);
      $stm->execute();
    }
  }
}

$redirParams = [];
if ($region) {
  $redirParams["region"] = $region;
}

if ($language_lookup) {
  $redirParams["language_lookup"] = $language_lookup;
}

redirect("listanimals", $redirParams);
