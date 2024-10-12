<?php

$page = "server.animals_food";

include_once("server.header.inc.php");
$db = Db::get();

// load all domesticated animal types
$stm = $db->query("SELECT adt.of_animal_type, adt.food_type, adt.food_amount, at.resources
  FROM animal_domesticated_types adt INNER JOIN animal_types at ON at.id = adt.of_animal_type");
foreach ($stm->fetchAll() as $u) {
  $animalTypes[$u->of_animal_type] = $u;
}

// load all objecttypes which can be used as animal feed containers
$feedContainerTypes = array();
$stm = $db->query("SELECT id FROM objecttypes WHERE rules LIKE '%forfodder%'");
foreach ($stm->fetchAll()as $u) {
  $feedContainerTypes[] = $u->id;
}

$troughsList = implode (", ", $feedContainerTypes);
echo "feed containers: $troughsList <br>\n";

// 1 - hay, 2 - vegetables, 4 - meat
// fodderSet key is bitmask composed of values above
// fodderSet value is array (list) of rawtypes available as feed for animals with possible feed types specified in key
// example: fodderSet[3] = array(hay_rawtype, vegetables_rawtype); // key 3 = 1 + 2 which is bitmask for hay&vegetables
$fodderSet = array();
for ($i = 0; $i < 8; $i++) { // mapping animal possible food types into rawtype ids
  $fodderSet[$i] = array();
  if ($i & AnimalConstants::ANIMAL_EATS_HAY) $fodderSet[$i][] = AnimalConstants::FODDER_HAY_ID;
  if ($i & AnimalConstants::ANIMAL_EATS_VEGETABLES) $fodderSet[$i][] = AnimalConstants::FODDER_VEGETABLES_ID;
  if ($i & AnimalConstants::ANIMAL_EATS_MEAT) $fodderSet[$i][] = AnimalConstants::FODDER_MEAT_ID;
}

// query which get needed data about animal in pack, object form or steed
// for steed (locations) there's subquery that gets animal_type associated with known object_type
$animalStm = $db->query(
  "SELECT COALESCE(a.id, obj.id, loc.id) AS id,
  COALESCE(a.type,
    (SELECT adt.of_animal_type FROM animal_domesticated_types adt
      WHERE adt.of_object_type = obj.type),
    (SELECT adt.of_animal_type FROM animal_domesticated_types adt
      WHERE adt.of_object_type = loc.area)
  ) AS type,
  COALESCE(a.location, obj.location, loc.region) AS location,
  COALESCE(obj.person) AS person,
  COALESCE(obj.attached) AS storage,
  COALESCE(loc.id) AS inner_location,
  IF(from_animal != 0, a.number, 1) AS number,
  ad.fullness AS fullness,
  ad.id AS dom_id
  FROM animal_domesticated ad 
  LEFT JOIN animals a ON a.id = ad.from_animal AND a.id > 0
  LEFT JOIN objects obj ON obj.id = ad.from_object AND obj.id > 0
  LEFT JOIN locations loc ON loc.id = ad.from_location AND loc.id > 0
");

/**
 * Reduces weight of the storage and all its parent storages by a specified $reduced value.
 *
 * @param $storageId int the innermost storage whose weight should be reduced
 * @param $reduced int amount to substract
 * @param Db $db
 */
function reduceWeightOfStorageAndItsParents($storageId, $reduced, Db $db)
{
  while ($storageId !== null) {
    $stm = $db->prepare("UPDATE `objects` SET weight = weight - :weightReduction WHERE id = :objectId");
    $stm->bindInt("weightReduction", $reduced);
    $stm->bindInt("objectId", $storageId);
    $stm->execute();

    $stm = $db->prepare("SELECT attached FROM `objects` WHERE id = :objectId");
    $stm->bindInt("objectId", $storageId);
    $storageId = $stm->executeScalar();
  }
}

foreach ($animalStm->fetchAll() as $animal_info) {
  echo "<hr>\n\n processing: $animal_info->id ($animal_info->type)<br>\n";
  $animalType = $animalTypes[$animal_info->type];

  $fodderList = implode (", ", $fodderSet[$animalType->food_type] );
  $initialHunger = $animalType->food_amount * $animal_info->number;
  $hungerLeft = $initialHunger;
  echo "number: $animal_info->number; needed: $hungerLeft <br>\n";

  $lowerLocIdFirst = false;
  if ($animal_info->person) {
    $loc_spec = "cont.person = $animal_info->person"; // look for containers in inventory of person who holds an animal
  } elseif ($animal_info->storage) {
    $loc_spec = "(cont.id = $animal_info->storage OR cont.attached = $animal_info->storage)";
  } elseif ($animal_info->location > 0 || $animal_info->inner_location > 0) {
    $locs = array();
    if ($animal_info->location > 0) { // not true for travelling steed
      $locs[] = $animal_info->location;
    }
    if ($animal_info->inner_location > 0) { // applicable only for steed (animals-locations)
      $locs[] = $animal_info->inner_location;
    }

    // to make animal (steed) eat first from outside, then from inside
    $lowerLocIdFirst = ($animal_info->inner_location > $animal_info->location);

    $locsList = implode(", ", $locs);
    $loc_spec = "cont.location IN ($locsList)"; // look for containers in these location
  } else {
    $loc_spec = "0 = 1"; // no food source, for example a flying bird
  }

  $locOrder = ($lowerLocIdFirst ? "ASC" : "DESC");

  $stm = $db->query("
  SELECT fodder.id AS id, fodder.weight AS weight,cont.id AS cont_id
  FROM objects cont
  INNER JOIN objects fodder ON fodder.attached = cont.id AND
    fodder.type = 2 AND fodder.typeid IN ($fodderList) 
  WHERE cont.type IN ($troughsList) AND $loc_spec
    AND fodder.weight > 0 ORDER BY cont.location $locOrder, cont.id"); // TODO it's hard to use prepared statement
  $stm->execute();
  $alteredPiles = array();
  $containerWeightLoss = array();
  while ($hungerLeft > 0 && $fodder_data = $stm->fetchObject()) {
    $toBeEaten = min($fodder_data->weight, $hungerLeft); // eats as much as possible
    echo "eat $toBeEaten from $fodder_data->id in $fodder_data->cont_id ";
    $alteredPiles[$fodder_data->id] = $fodder_data->weight - $toBeEaten; // data about new amount of fodder (how much left)
    if (!array_key_exists($fodder_data->cont_id, $containerWeightLoss)) {
      $containerWeightLoss[$fodder_data->cont_id] = 0;
    }
    $containerWeightLoss[$fodder_data->cont_id] += $toBeEaten;
    $hungerLeft -= $toBeEaten;
    echo "eats $toBeEaten grams ";
  }

  if ($hungerLeft == 0) {
    echo " has eaten all what was needed\n";
    if ($animal_info->fullness >= AnimalConstants::HUNGER_THRESHOLD) {
      $fullnessMultiplier = 1.3 - 0.6 * ( $animal_info->fullness / _SCALESIZE_GSS);
    } else {
      $fullnessMultiplier = 4 - 2 * ($animal_info->fullness / _SCALESIZE_GSS);
    }
    // increase ratio by ~1.3 if very hungry, 0.7 if fed
  } else {
    echo "starving, still needs $hungerLeft grams\n";
    $fullnessMultiplier = (-1) * $hungerLeft / $initialHunger;

    $stm = $db->prepare("SELECT name FROM animal_types WHERE id = :id");
    $stm->bindInt("id", $animal_info->type);
    $animalName = $stm->executeScalar();
    $animalName = str_replace(" ", "_", $animalName);
    if ($animal_info->location) { // "animal has nothing to eat" for everyone in the same location
      Event::createEventInLocation(295, "ANIMAL=$animalName", $animal_info->location, Event::RANGE_SAME_LOCATION);
    } elseif ($animal_info->person) { // "animal has nothing to eat" for character who holds an animal in inventory
      Event::createPersonalEvent(295, "ANIMAL=$animalName", $animal_info->person);
    }
    if ($animal_info->inner_location > 0) {
      Event::createEventInLocation(295, "ANIMAL=$animalName", $animal_info->inner_location, Event::RANGE_SAME_LOCATION);
      if ($animal_info->location > 0) {
        Event::createEventInLocation(295, "ANIMAL=$animalName", $animal_info->location, Event::RANGE_SAME_LOCATION);
      }
    }
  }

  $fullnessChange = round(AnimalConstants::EATING_FULLNESS_CHANGE * $fullnessMultiplier);
  $newFullness = max(0, min(AnimalConstants::MAX_FULLNESS, $animal_info->fullness + $fullnessChange));
  echo " | change: $fullnessChange | new fullness: $newFullness";
  $stm = $db->prepare("UPDATE animal_domesticated SET fullness = :fullness WHERE id = :id");
  $stm->bindInt("fullness", $newFullness);
  $stm->bindInt("id", $animal_info->dom_id);
  $stm->execute();

  foreach ($alteredPiles as $id => $newWeight) {
    $stm = $db->prepare("UPDATE `objects` SET weight = :weight WHERE id = :id");
    $stm->bindInt("weight", $newWeight);
    $stm->bindInt("id", $id);
    $stm->execute();
  }
  foreach ($containerWeightLoss as $id => $reduced) {
    reduceWeightOfStorageAndItsParents($id, $reduced, $db);
  }

  if ($animal_info->location) { // create dung if animal not in inventory
    $foodEaten = $initialHunger - $hungerLeft;
    $dungAmount = round($foodEaten * AnimalConstants::FOOD_TO_DUNG_RATIO);
    ObjectHandler::rawToLocation($animal_info->location, AnimalConstants::DUNG_PRODUCED_ID, $dungAmount);

    // yield feathers
    $huntingRaws = Parser::rulesToArray($animalType->resources, ",>");
    if (isset($huntingRaws['feathers'])) {
      $feathersAmount = ceil($animal_info->number * $huntingRaws['feathers'] * AnimalConstants::DAILY_FEATHERS_TO_HUNT_RATIO);
      ObjectHandler::rawToLocation($animal_info->location, ObjectHandler::getRawIdFromName("feathers"), $feathersAmount);
    }
  }
}


import_lib("func.expireobject.inc.php");
expire_multiple_objects("type = 2 AND weight = 0");


include("server/server.footer.inc.php");
