<?php

$page = "server.die";
include "server.header.inc.php";

function loop_chars(DbStatement $charsStm, $type) {
  foreach ($charsStm->fetchAll() as $char_info) {

    echo "\n$char_info->name dies from $type: ";

    try {
      $charToDie = Character::loadById($char_info->id);

      if ($type == "thirst" || $type == "hunger") {
        $charToDie->dieCharacter(CharacterConstants::CHAR_DEATH_STARVED, 0, true);
      } else {
        $charToDie->intoNearDeath(CharacterConstants::CHAR_DEATH_UNKNOWN, 0);
        // this function doesn't create event so it's done separately
        Event::createPersonalEvent(314, "", $char_info->id);
        Event::createPublicEvent(315, "VICTIM=$char_info->id", $char_info->id, Event::RANGE_NEAR_LOCATIONS, [$char_info->id]);
      }
      $charToDie->saveInDb();
    } catch (InvalidArgumentException $e) {
      Logger::getLogger("server.die")->error("Unable to kill char $char_info->id because it doesn't exist in the database");
    }
  }
}

function increaseWildPackNumber($animalType, $animalLocation, Db $db) {
  $stm = $db->prepare(
  "SELECT wild.id FROM animal_types wt
  INNER JOIN animals wild ON wild.location = :locationId  AND wild.type = wt.id
  WHERE wt.domesticable_into = :animalType ORDER BY rand() LIMIT 1");
  $stm->bindInt("locationId", $animalLocation);
  $stm->bindInt("animalType", $animalType);
  $turnIntoPack = $stm->executeScalar();
  if ($turnIntoPack && $animalLocation) { // add animal to wild pack if possible
    $wildPack = AnimalPack::loadFromDb($turnIntoPack);
    $wildPack->incrementNumber();
  } elseif ($animalLocation > 0) {
    $stm = $db->prepare("SELECT resources FROM animal_types WHERE id = :id");
    $stm->bindInt("id", $animalType);
    $raws = $stm->executeScalar();
    $raws = Parser::rulesToArray($raws, ",>");
    foreach ($raws as $rawName => $amount) {
      echo "adding stuff: $animalLocation  give $rawName $amount<br>";
      ObjectHandler::rawToLocation($animalLocation, ObjectHandler::getRawIdFromName($rawName), 0.1 * $amount);
    }
  }
  return ($turnIntoPack != null && $animalLocation);
}

function notifyAnimalEvent($event_type, $location, $animalName) {
  if (!$location) return; // animal objects without location (in inventory) don't produce event
  Event::createEventInLocation($event_type, "ANIMAL=$animalName", $location, Event::RANGE_SAME_LOCATION);
  echo "$animalName in location $location died/turned into wild <br>";
}

$db = Db::get();
$stm = $db->prepare("SELECT name,id,sex FROM chars,states
  WHERE status = :active
    AND states.person = chars.id
    AND states.type = :type
    AND states.value = :value");
$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->bindInt("type", StateConstants::HEALTH);
$stm->bindInt("value", 0);
$stm->execute();
loop_chars($stm, "damage");

$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->bindInt("type", StateConstants::HUNGER);
$stm->bindInt("value", _SCALESIZE_GSS);
$stm->execute();
loop_chars($stm, "hunger");

$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->bindInt("type", StateConstants::THIRST);
$stm->bindInt("value", _SCALESIZE_GSS);
$stm->execute();
loop_chars($stm, "thirst");


// death/revive people in near death state
$gameDate = GameDate::NOW();
$stm = $db->prepare("SELECT nd.state, c.id AS char_id, c.name, c.sex AS sex FROM char_near_death nd
    INNER JOIN chars c ON nd.char_id = c.id AND c.status <= :active
  WHERE (nd.day = :day AND :hour >= nd.hour) OR (:day2 > nd.day)");
$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->bindInt("day", $gameDate->getDay());
$stm->bindInt("day2", $gameDate->getDay());
$stm->bindInt("hour", $gameDate->getHour());
$stm->execute();
foreach ($stm->fetchAll() as $near_death) {

  if ($near_death->state == CharacterConstants::NEAR_DEATH_NOT_HEALED) { // nobody healed this character => must die
    try {
      $charToDie = Character::loadById($near_death->char_id);
      // we already have info about death cause stored here
      $charToDie->dieCharacter($charToDie->getDeathCause(), $charToDie->getDeathWeapon(), false);
      $charToDie->saveInDb();

      // death event
      Event::createPersonalEvent(302, "", $near_death->char_id);
      Event::createPublicEvent(303, "VICTIM=$near_death->char_id GENDER=$near_death->sex",
        $near_death->char_id, Event::RANGE_NEAR_LOCATIONS, [$near_death->char_id]);
      echo "\n$near_death->name dies because of not being healed from NDS";
    } catch (InvalidArgumentException $e) {
      Logger::getLogger("server.die")
        ->error("Unable to kill char $near_death->char_id in NDS because it doesn't exist");
    }
  } else {
    Event::createPersonalEvent(312, "", $near_death->char_id);
    Event::createPublicEvent(313, "VICTIM=$near_death->char_id GENDER=$near_death->sex",
        $near_death->char_id, Event::RANGE_NEAR_LOCATIONS, array($near_death->char_id));
    echo "\n$near_death->name is healed from NDS";
  }
  $stm = $db->prepare("DELETE FROM char_near_death WHERE char_id = :charId LIMIT 1");
  $stm->bindInt("charId", $near_death->char_id);
  $stm->execute();
}



/*
 * Kill/make wild hungry animals in packs
 */

$stm = $db->prepare("SELECT a.id, a.type, a.location, ad.fullness FROM animal_domesticated ad
  INNER JOIN animals a ON a.id = ad.from_animal
  WHERE ad.fullness < :fullness AND ad.from_animal != 0");
$stm->bindInt("fullness", AnimalConstants::HUNGER_THRESHOLD);
$stm->execute();

foreach ($stm->fetchAll() as $animal_info) {
  $loseChancePerDay = (AnimalConstants::HUNGER_THRESHOLD - $animal_info->fullness) / AnimalConstants::HUNGER_THRESHOLD;
  $loseChancePerTurn = $loseChancePerDay / _TURNS;
  $chancePercent = mt_rand(0, 100000) / 100000;

  if ( $chancePercent <= $loseChancePerTurn ) {
    $dPack = AnimalPack::loadFromDb($animal_info->id);
    $dPack->decrementNumber();

    $eventType = 284;
    if (increaseWildPackNumber($animal_info->type, $animal_info->location, $db))
      $eventType = 285;

    notifyAnimalEvent ( $eventType, $animal_info->location, $dPack->getName() );
  }
}

/* Kill/make wild hungry animals being objects */
$stm = $db->prepare("SELECT obj.id, obj.typeid, mb.object_id > 0 AS is_flying,
  IF(obj.location > 0, obj.location, c.location) AS location,
  ad.fullness FROM animal_domesticated ad
  INNER JOIN objects obj ON obj.id = ad.from_object
  LEFT JOIN chars c ON c.id = obj.person AND obj.person > 0
  LEFT JOIN messenger_birds mb ON mb.object_id = obj.id
  WHERE ad.fullness < :fullness AND ad.from_object != 0"
);
$stm->bindInt("fullness", AnimalConstants::HUNGER_THRESHOLD);
$stm->execute();

foreach ($stm->fetchAll() as $object_info) {
  $loseChancePerDay = (AnimalConstants::HUNGER_THRESHOLD - $object_info->fullness) / AnimalConstants::HUNGER_THRESHOLD;
  $loseChancePerTurn = $loseChancePerDay / _TURNS;
  $chancePercent = mt_rand(0, 100000) / 100000;

  if ($chancePercent <= $loseChancePerTurn) {

    $animalObject = DomesticatedAnimalObject::loadFromDb($object_info->id);
    if ($object_info->is_flying) {
      try {
        $messengerBirdObject = CObject::loadById($object_info->id);
        $messengerBird = new MessengerBird($messengerBirdObject, Db::get());
        $birdX = $messengerBird->getX();
        $birdY = $messengerBird->getY();
        $nearestLocation = LocationFinder::nearPosition($birdX, $birdY)->type(LocationConstants::TYPE_OUTSIDE)->findNearest();
        $objectsInsideIds = CObject::storedIn($messengerBirdObject)->findIds();
        $stm = $db->prepareWithIntList("UPDATE objects SET location = :locationId, attached = 0
          WHERE id IN (:ids)", [
          "ids" => $objectsInsideIds,
        ]);
        $stm->bindInt("locationId", $nearestLocation->getId());

        $messengerBird->remove();
        $messengerBird->saveInDb();

        echo "A flying bird $object_info->id dies in position ($birdX, $birdY) " .
          "and the contents are moved to location " . $nearestLocation->getId() . "<br>\n";
        Event::create(391, "BIRD_ID=" . $messengerBirdObject->getId())->inLocation($nearestLocation)->show();

      } catch (InvalidArgumentException $e) {
        Logger::getLogger("server.die")->error("Unable to move contents of a flying messenger bird $object_info->id", $e);
      }
    } else {
      $eventType = 284;
      if (increaseWildPackNumber($object_info->typeid, $object_info->location, $db)) {
        $eventType = 285;
      }

      notifyAnimalEvent($eventType, $object_info->location, $animalObject->getName());
    }
    $animalObject->annihilate();
  }
}

/*
 * Kill/make wild steed
 */

$stm = $db->query("SELECT l.id AS location, l.region AS region, ad.fullness AS fullness,
  t.id AS travelId, t.travleft AS tleft, t.travneeded AS tneeded, t.locfrom, t.locdest,
  adt.of_animal_type AS animalType
  FROM animal_domesticated ad
  INNER JOIN locations l ON l.id = ad.from_location
  INNER JOIN animal_domesticated_types adt ON adt.of_object_type = l.area
  LEFT JOIN travels t ON t.person = l.id AND t.type > 0
  WHERE ad.from_location > 0");



foreach ($stm->fetchAll() as $animal) {
  $loseChancePerDay = (AnimalConstants::HUNGER_THRESHOLD - $animal->fullness) / AnimalConstants::HUNGER_THRESHOLD;
  $loseChancePerTurn = 10* $loseChancePerDay / _TURNS;
  $chancePercent = mt_rand(0, 100000) / 100000;

  if ( $chancePercent <= $loseChancePerTurn ) {
    try {
      $animalLoc = Location::loadById($animal->location);
      if ($animal->travelId != null) {
        $moveTo = ($animal->tleft >= $animal->tneeded * 0.5) ? $animal->locfrom : $animal->locdest;
        $stm = $db->prepare("DELETE FROM travels WHERE id = :id AND type > 0 LIMIT 1");
        $stm->bindInt("id", $animal->travelId);
        $stm->execute();
        $animalLoc->setRegion($moveTo);
        $animalLoc->saveInDb(); // it's necessary to sync obj with db
      }

      Event::createEventInLocation(340, "ID=$animal->location",
        $animalLoc->getId(), Event::RANGE_SAME_LOCATION); // info that your breed dies

      $projects = Project::locatedIn($animalLoc->getId())->findIds();
      foreach ($projects as $projectId) {
        $canceler = ProjectCanceler::FromId($projectId, ProjectCanceler::NO_ACTOR, Db::get());
        $canceler->returnUsedResources(1.0);
        $canceler->deleteThisProject();
      }

      $fixedObjects = CObject::locatedIn($animal->location)->setting(ObjectConstants::SETTING_FIXED)->findAll();
      foreach ($fixedObjects as $object) {
        $object->remove();
        $object->saveInDb();
      } // remove all fixed objects, then move their contents outside

      $objectsToMove = CObject::locatedIn($animalLoc->getId())->findAll();
      foreach ($objectsToMove as $object) {
          $translocation = new ObjectTranslocation($object, $animalLoc,
            Location::loadById($animalLoc->getRegion()));
          $translocation->setCheckNearness(false)->setCheckCapacity(false);
          $translocation->setCheckObjectSetting(false)->setCheckReceiver(false);
        try {
          $translocation->perform();
        } catch (Exception $e) {
          Logger::getLogger("server.die")->error("Error when trying to translocate {$object->getId()}
            from $animal->location to ". $animalLoc->getRegion(), $e);
        }
      }

      $stm = $db->prepare("UPDATE chars SET location = :newLocationId WHERE location = :animalLocationId");
      $stm->bindInt("newLocationId", $animalLoc->getRegion());
      $stm->bindInt("animalLocationId", $animalLoc->getId());
      $stm->execute();

      if (increaseWildPackNumber($animal->animalType, $animalLoc->getRegion(), $db)) {
        Event::createEventInLocation(342, "ANIMAL=$animal->animalType ID=$animal->location", $animalLoc->getRegion(), Event::RANGE_SAME_LOCATION);
      } else {
        Event::createEventInLocation(341, "ANIMAL=$animal->animalType ID=$animal->location", $animalLoc->getRegion(), Event::RANGE_SAME_LOCATION);
      }
      $animalLoc->remove();
      $animalLoc->saveInDb();
      $stm = $db->prepare("DELETE FROM animal_domesticated WHERE from_location = :locationId");
      $stm->bindInt("locationId", $animalLoc->getId());
      $stm->execute();
    } catch (InvalidArgumentException $e) {
      Logger::getLogger("server.die")->error("no location of id $animal->location", $e);
    }
  }
}

echo "\nDone.";

include "server/server.footer.inc.php";
