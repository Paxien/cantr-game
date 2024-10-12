<?php

// SANITIZE INPUT
$to_road = HTTPContext::getInteger('to_road');
$to_building = HTTPContext::getInteger('to_building');
$to_animal = HTTPContext::getInteger('to_animal');
$to_object = HTTPContext::getInteger('object_id');
$to_project = HTTPContext::getInteger('to_project');
$to = HTTPContext::getInteger('to');

if ($to_road) {
  $charLoc = Location::loadById($char->getLocation());
  $rootLoc = $charLoc->getRoot();
  $connection = Connection::loadById($to_road);

  if (($rootLoc->getId() != $charLoc->getId() && ($rootLoc->getId() != $charLoc->getRegion() || $charLoc->getType() != LocationConstants::TYPE_VEHICLE)) // must be outside or in the vehicle almost outside
    || !in_array($rootLoc->getId(), [$connection->getStart(), $connection->getEnd()])
    || !in_array($connection->getOppositeLocation($rootLoc->getId()), [$connection->getStart(), $connection->getEnd()])) {
    CError::throwRedirectTag("char.description", "error_point_road_not_here");
  }
}else if ($to_building) {
  try {
    $toLocation = Location::loadById($to_building);
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("char.buildings", "error_too_far_away");
  }
  if ($char->getLocation() != $toLocation->getRegion()) {
    CError::throwRedirectTag("char.buildings", "error_point_building_not_here");
  }
}else if ($to_animal) {
  $animalPack = AnimalPack::loadFromDb($to_animal);
  if (!$animalPack->ok || !$char->isInSameLocationAs($animalPack)) {
    CError::throwRedirectTag("char.description", "error_point_animal_not_here");
  }
}else if ($to_object) {
  $object = CObject::loadById($to_object);

  $in_inventory = $char->hasInInventory($object->getRoot());

  if (!($char->hasWithinReach($object) || $object->isAccessibleInStorage($char, false, false))) {
    CError::throwRedirectTag("char.events", "error_point_object_not_here");
  }
}
elseif ($to_project) {
  $project = Project::loadById($to_project);
  if (!$char->isInSameLocationAs($project)) {
    CError::throwRedirectTag("char.projects", "error_project_not_same_location");
  }
}elseif ($to) {
  try {
    $toChar = Character::loadById($to);
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("char.events", "error_point_too_far");
  }
  if (!$char->isNearTo($toChar)) {
    CError::throwRedirectTag("char.events", "error_point_too_far");
  }
  if (!$toChar->isAlive()) {
    CError::throwRedirectTag("char.events", "error_target_dead_char");
  }
} else {
  CError::throwRedirectTag("char.events", "error_point_nobody");
}

$backLink = "char.events";

  if ($to_road) {

    $types = Pipe::from($connection->getParts())
      ->map(function(ConnectionPart $part) {
        return "<CANTR REPLACE NAME=road_" . $part->getType()->getName() . ">";
      })->toArray();

    $roadName = urlencode(implode(", ", $types));
    $end_name = urlencode("<CANTR LOCNAME ID=" . $connection->getOppositeLocation($rootLoc->getId()) . ">");

    $eventForActor = Event::create(127, "ROAD=$roadName END=$end_name");
    $eventForOthers = Event::create(128, "ACTOR=$character ROAD=$roadName END=$end_name");
  }
  elseif ($to_building) {

    $building_name = urlencode("<CANTR LOCNAME ID=$to_building>");

    $eventForActor = Event::create(129, "BUILDING=$building_name");
    $eventForOthers = Event::create(130, "ACTOR=$character BUILDING=$building_name");
  }
  elseif ($to_animal) {
    $animal_pack = AnimalPack::loadFromDb(intval($to_animal));

    if ($animalPack->getNumber() == 1) {
      $animal_pack->name = urlencode("<CANTR REPLACE NAME=animal_" . $animal_pack->getName() . "_s>");
    } else {
      $animal_pack->name = urlencode("<CANTR REPLACE NAME=animal_" . $animal_pack->getName() . "_p>");
    }

    $animal_number = urlencode($animalPack->getNumber());
    $eventForActor = Event::create(251, "PACK=$animal_pack->name PACKNO=$animal_number");
    $eventForOthers = Event::create(252, "ACTOR=$character PACK=$animal_pack->name PACKNO=$animal_number");
  }
  elseif ($to_object) {
    if (in_array($object->getType(), array(ObjectConstants::TYPE_NOTE, ObjectConstants::TYPE_ENVELOPE, ObjectConstants::TYPE_NOTICEBOARD))) {
      $db = Db::get();
      $stm = $db->prepare("SELECT utf8title FROM obj_notes WHERE id = :id");
      $stm->bindInt("id", $object->getTypeid());
      $noteTitle = $stm->executeScalar();

      $specificName = $noteTitle ? " \"$noteTitle\"" : "";
    }
    elseif (in_array($object->getType(), ObjectConstants::$TYPES_COINS) || $object->getType() == ObjectConstants::TYPE_SEAL) {
      $distinctText = TextFormat::getDistinctHtmlText($object->getSpecifics());
      $specificName = " \"$distinctText\"";
    }
    elseif ($object->getType() == ObjectConstants::TYPE_KEY) {
      $specificName = " <CANTR REPLACE NAME=number_abbr> " . $object->getSpecifics();
    }
    else $specificName = "";
    $customDesc = Descriptions::getDescription($object->getId(), Descriptions::TYPE_OBJECT);
    if (!empty($customDesc)) {
      $specificName .= " ($customDesc)";
    }
    $specificName = urlencode($specificName);

    $rootStorageId = $object->getRoot()->getId();
    if (!$in_inventory) { // point at x
      if ($rootStorageId != $object->getId()) { // it's in storage
        $eventForActor = Event::create(365, "OBJECT=" . $object->getId() . " TITLE=$specificName STORAGE=$rootStorageId");
        $eventForOthers = Event::create(366, "ACTOR=$character OBJECT=" . $object->getId() . " TITLE=$specificName STORAGE=$rootStorageId");
        $backLink = "retrieve&object_id=" . $object->getAttached();
      } else {
        $eventForActor = Event::create(260, "OBJECT=" . $object->getId() . " TITLE=$specificName ");
        $eventForOthers = Event::create(261, "ACTOR=$character OBJECT=" . $object->getId() . " TITLE=$specificName ");
      }
    } else { // point at x held by x
      if ($object->getRoot()->getId() != $object->getId()) { // it's in storage
        $eventForActor = Event::create(367, "OBJECT=" . $object->getId() . " TITLE=$specificName STORAGE=$rootStorageId");
        $eventForOthers = Event::create(368, "ACTOR=$character OBJECT=" . $object->getId() . " TITLE=$specificName STORAGE=$rootStorageId");
        $backLink = "retrieve&object_id=" . $object->getAttached();
      } else {
        $eventForActor = Event::create(264, "OBJECT=" . $object->getId() . " TITLE=$specificName");
        $eventForOthers = Event::create(265, "ACTOR=$character OBJECT=" . $object->getId() . " TITLE=$specificName");
      }
    }
  }
  elseif ($to_project) {
    $projectName = urlencode($project->getName());
    $eventForActor = Event::create(300, "PROJECT=$projectName");
    $eventForOthers = Event::create(301, "ACTOR=$character PROJECT=$projectName");
  }
  else {
    $eventForActor = Event::create(8, "VICTIM=$to");
    $eventForVictim = Event::create(7, "ACTOR=$character");
    $eventForOthers = Event::create(6, "ACTOR=$character VICTIM=$to");
  }

$eventForActor->forCharacter($char)->show();
$excluded = [$char];
if (isset($eventForVictim)) {
  $eventForVictim->forCharacter($toChar)->show();
  $excluded[] = $toChar;
}
$eventForOthers->nearCharacter($char)->andAdjacentLocations()->except($excluded)->show();

  redirect($backLink);
