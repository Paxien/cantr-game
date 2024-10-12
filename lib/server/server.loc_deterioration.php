<?php

$page = "server.loc_deterioration";
include "server.header.inc.php"; 

function removeFixedObjectsIn(Location $location) {
  $fixedObjects = CObject::locatedIn($location)
    ->setting(ObjectConstants::SETTING_FIXED)->findAll();
  $sunkStats = new Statistic("sunk", Db::get());
  foreach ($fixedObjects as $fixedObject) { // all fixed objects must be sunk
    $sunkStats->update($fixedObject->getUniqueName(), 0, $fixedObject->getAmount());
    $fixedObject->remove();
    $fixedObject->saveInDb();
  }
}

function removeProjectsIn(Location $location) {
  $projectsIds = Project::locatedIn($location)->findIds();
  foreach ($projectsIds as $projectId) {
    $canceler = ProjectCanceler::FromId($projectId, ProjectCanceler::NO_ACTOR, Db::get());
    $canceler->returnUsedResources(1.0);
    $canceler->deleteThisProject();
  }
}

function moveQuantityObject(CObject $object, Location $goal)
{
  if ($object->getType() == ObjectConstants::TYPE_RAW) {
    ObjectHandler::rawToLocation($goal->getId(),
      $object->getTypeid(), $object->getWeight());
  } else { // currently it means they are coins
    ObjectHandler::coinsToLocation($goal->getId(),
      $object->getType(), $object->getSpecifics(), $object->getAmount());
  }
  $object->remove();
  $object->saveInDb();
}

$db = Db::get();
$today = GameDate::NOW()->getDay();
$sunkStats = new Statistic("sunk", $db);
$sunkRawStats = new Statistic("sunk_raws", $db);

$stm = $db->prepare("SELECT l.id FROM locations l
  INNER JOIN location_visits lv ON lv.location = l.id
  WHERE (lv.amortized <= :day1 - 250)
    AND (lv.last <= :day2 - 20)
    AND l.type = :locationType
    AND NOT EXISTS (SELECT id FROM chars WHERE location = l.id AND status = :active)");
$stm->bindInt("day1", $today);
$stm->bindInt("day2", $today);
$stm->bindInt("locationType", LocationConstants::TYPE_SAILING_SHIP);
$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->execute();
// sailing ships untouched for more than 250 days
// which were not visited in a few last days and currently nobody alive on deck
foreach ($stm->fetchScalars() as $shipId) {
  try {
    echo "potentially $shipId can be sunk\n";
    if (mt_rand(0,10000) > 200) { // 2% ch-ce to execute sinking
      continue; // not today :(
    }
    
    $mainDeck = Location::loadById($shipId);

    $stm = $db->prepare("SELECT COUNT(*) FROM chars c
      INNER JOIN locations l ON l.id = c.location
      WHERE l.x = :x AND l.y = :y AND c.status = :active");
    $stm->bindInt("x", $mainDeck->getX());
    $stm->bindInt("y", $mainDeck->getY());
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $isAnybodyOnShip = $stm->executeScalar();

    if ($isAnybodyOnShip) {
      echo "somebody is on deck\n";
      continue;
    }

    echo $mainDeck->getName() ." will be sunk\n";

    foreach ($mainDeck->getSublocationsRecursive() as $sublocId) {
      $subloc = Location::loadById($sublocId);
      // cancel all projects and give resources back
      removeProjectsIn($subloc);

      // remove fixed objects, note that storage contents (like notes) go outside
      removeFixedObjectsIn($subloc);

      $movableObjects = CObject::locatedIn($subloc)
        ->exceptSetting(ObjectConstants::SETTING_FIXED)->findAll();

      // move all objects to main deck
      foreach ($movableObjects as $object) {
        if ($object->isQuantity()) { // raws&coins are different
          moveQuantityObject($object, $mainDeck);
        } else {
          $object->setLocation($mainDeck->getId());
          $object->saveInDb();
        }
      }
      $subloc->remove();
      $subloc->saveInDb();
    }
    
    // all docked ships and cargo holds are already destroyed, their contents are moved to main deck
    // now we want to remove most of stuff and move shipwreck to its final destination
    removeProjectsIn($mainDeck);
    removeFixedObjectsIn($mainDeck);

    $quantityObjects = CObject::locatedIn($mainDeck)
      ->setting(ObjectConstants::SETTING_QUANTITY)->findAll();
    foreach ($quantityObjects as $object) {
      $piecesLeft = ceil(0.03 * $object->getAmount()); // only 3 percent are kept

      $objName = $object->getUniqueName();
      if ($object->getType() == ObjectConstants::TYPE_RAW) {
        $objName = CObject::getRawNameFromId($object->getTypeid());
      }
      $sunkRawStats->update($objName, 0, $object->getAmount() - $piecesLeft);
      $object->setWeight($piecesLeft * $object->getUnitWeight());
      $object->saveInDb();
    }

    while (true) { // remove all storages which aren't note storages
      $storages = CObject::locatedIn($mainDeck)
        ->hasProperty("Storage")->hasNotProperties(["NoteStorage", "Readable"])->findAll();

      if (count($storages) == 0) {
        break;
      }

      foreach ($storages as $storage) {
        $sunkStats->update($storage->getUniqueName(), 0, $storage->getAmount());
        $storage->remove();
        $storage->saveInDb();
      }
    }

    // remove most of normal objects
    $normalObjects = CObject::locatedIn($mainDeck)
      ->exceptSetting(ObjectConstants::SETTING_QUANTITY)->hasNotProperty("Storage")->findAll();

    $alwaysSaved = [ObjectConstants::TYPE_NOTE, ObjectConstants::TYPE_ENVELOPE,
      ObjectConstants::TYPE_SEAL, ObjectConstants::TYPE_DEAD_BODY]; // seals - must exist to keep seal name in notes, dead body - can have lockets
    foreach ($normalObjects as $object) {
      $willSurvive = in_array($object->getType(), $alwaysSaved) || (mt_rand(0, 100000) < 3000); // 3%
      if (!$willSurvive) {
        $sunkStats->update($object->getUniqueName(), 0, $object->getAmount());
        $object->remove();
        $object->saveInDb();
      }
    }
    
    // remove sailing info
    $sailing = Sailing::loadByVesselId($mainDeck->getId());
    $sailing->remove();
    $sailing->saveInDb();

    $stm = $db->prepare("SELECT unique_name FROM objecttypes WHERE id = :id");
    $stm->bindInt("id", $mainDeck->getArea());
    $shipType = $stm->executeScalar();
    // update sinking data
    $sunkShip = new Statistic("sunk_ship", $db);
    $sunkShip->store($shipType, 0);

    // change ship into shipwreck
    
    $nearestCoast = LocationFinder::nearPosition($mainDeck->getX(), $mainDeck->getY())
      ->type(LocationConstants::TYPE_OUTSIDE)->bordersWater()->findNearest();
    $mainDeck->setType(LocationConstants::TYPE_VEHICLE); // shipwreck is like "ruins of ship"

    $stm = $db->prepare("SELECT build_requirements FROM objecttypes WHERE id = :id");
    $stm->bindInt("id", $mainDeck->getArea());
    $req = $stm->executeScalar();
    $reqAssoc = Parser::rulesToArray($req, ";:");

    // ship creates a bigger shipwreck if weight of input raws > 7000
    $rawAssoc = Parser::rulesToArray($reqAssoc['raws'], ",>");
    $isShipHeavy = array_sum($rawAssoc) > 7000;
    
    $wreckType = $isShipHeavy ? ObjectConstants::TYPE_SHIPWRECK : ObjectConstants::TYPE_SMALL_SHIPWRECK;

    $mainDeck->setArea($wreckType);
    $mainDeck->setX($nearestCoast->getX());
    $mainDeck->setY($nearestCoast->getY());
    $mainDeck->setRegion($nearestCoast->getId());
    $mainDeck->saveInDb();

    Event::create(364, "SHIP=" . $mainDeck->getId())->inLocation($nearestCoast)->show();

  } catch (InvalidArgumentException $e) {
    Logger::getLogger("server.loc_destruction")->error("error when sinking abandoned ship with id: ". $mainDeck->getId(), $e);
  }
}


echo "\nfinished!\n\n";

include "server/server.footer.inc.php";