<?php

//how often this script is runnign during the one Cantr day.
define("_RUN_FREQUENCY", 8);

class GlobalAnimalsManager
{
  private $animalTypes;
  //name => id
  private $areaTypes;
  private $usedLocationSlots;
  private $maxLocationSlots;
  /** @var Db */
  private $db;

  private function prepareReportTable()
  {
    return array('new_animals' => 0, 'attack_attempts' => 0, 'migration_count' => 0, 'overcrowding_death' => 0);
  }

  private function callAttack($animalType, $pack)
  {
    $chance_per_day = $animalType->attack_chance / 100;
    $chance_per_tick = $chance_per_day / _RUN_FREQUENCY;
    //this chance is for normal, avarage population.
    //so, half of max in location have this factor * 1, full in location * 2,
    //smallest population - smaller chance
    $chance_to_attack = $chance_per_tick * min(2, $pack->number / (0.5 * $animalType->max_in_location));

    $random_percent = mt_rand(0, 100000) / 100000;
    if ($random_percent <= $chance_to_attack) {
      //let kill somebody! this is old re-used code
      $pack = AnimalPack::loadFromDb($pack->id);
      if ($pack->ok) {
        $pack->attack();
        return true;
      }
    }
    return false;
  }

  private function bornWild($animalType, $pack, $locationInfo)
  {
    $chance_per_day = $animalType->reproduction_chance / 100;
    $chance_per_tick = $chance_per_day / _RUN_FREQUENCY;
    //this chance is for normal, avarage population.
    //so, half of max in location have this factor * 1, full in location -> 0, when we have
    //very small population this factor is tends to 2.
    $chance_to_born = $chance_per_tick *
      2 * ($animalType->max_in_location - $pack->number) / $animalType->max_in_location;
    $random_percent = mt_rand(0, 100000) / 100000;
    if ($random_percent <= $chance_to_born) {
      $stm = $this->db->prepare("UPDATE animals SET number = number + 1 WHERE id = :id");
      $stm->bindInt("id", $pack->id);
      $stm->execute();
      return true;
    }
    return false;
  }

  private function bornDomesticated($animalType, $pack, $locationInfo)
  {
    $chance_per_day = $animalType->reproduction_chance / 100;
    $chance_per_tick = $chance_per_day / _RUN_FREQUENCY;
    $fullness_multiplier = $pack->fullness / _SCALESIZE_GSS;

    $chance_to_born = $chance_per_tick
      //* sqrt( $animalType->max_in_location / $pack->number ) // TODO
      * $fullness_multiplier;
    $random_percent = mt_rand(0, 100000) / 100000;
    if ($random_percent <= $chance_to_born) {
      // load domesticated animal data
      $animalPack = AnimalPack::loadFromDb($pack->id, $animalType);

      if ($animalPack->ok) {
        $rawPool = Parser::rulesToArray($animalPack->getSpecificsString());

        // iterate through "milking" etc.
        foreach (Animal::breedingActionsArray("_raws") as $act) {
          if ($rawPool[$act] != null) {
            $rawData = Parser::rulesToArray($rawPool[$act], ",>");
            foreach ($rawData as $rawName => &$rawAmount) {
              $rawAmount = ($rawAmount * $animalPack->getNumber()) / ($animalPack->getNumber() + 1);
              $rawAmount = round($rawAmount);
            }
            $rawPool[$act] = Parser::arrayToRules($rawData, ",>");
          }
        }

        $newFullness = ($animalPack->getFullness() * $animalPack->getNumber()) / ($animalPack->getNumber() + 1);
        $animalPack->setNumber($animalPack->getNumber() + 1);
        $animalPack->setFullness($newFullness);
        $animalPack->setSpecifics(Parser::arrayToRules($rawPool));
      }
      return true;
    }
    return false;
  }

  private function migrate($animalType, $pack, $locationInfo)
  {

    $chance_per_day = $animalType->travel_chance / 100;
    $chance_per_tick = $chance_per_day / _RUN_FREQUENCY;
    //this chance is for normal, avarage population.
    //so, half of max in location have this factor * 1, full in location -> 2, when we have
    //very small population this factor is tends to 0.
    $chance_to_migrate = $chance_per_tick *
      min(2, 2 * $pack->number / $animalType->max_in_location);

    $random_percent = mt_rand(0, 100000) / 100000;

    if ($random_percent <= $chance_to_migrate) {
      //replacing "area_type" of animal to area ids ;)
      $areaTypesNames = explode(',', $animalType->area_types);
      $areas = array();
      foreach ($areaTypesNames as $areaTypeName) {
        $areas [] = $this->areaTypes[$areaTypeName];
      }

      $travel_codes = implode(',', $areas);

      $stm = $this->db->prepareWithIntList("SELECT start, end " .
        "FROM `connections` " .
        "WHERE (`end`= :end AND start_area IN (:travelCodes1)) " .
        "OR (`start`= :start AND end_area IN (:travelCodes2)) " .
        "ORDER BY rand()", [
          "travelCodes1" => $travel_codes,
          "travelCodes2" => $travel_codes,
      ]);
      $stm->bindInt("end", $locationInfo->id);
      $stm->bindInt("start", $locationInfo->id);
      $stm->execute();
      //I checked possible routes in random order, check first that pass conditions
      foreach ($stm->fetchAll() as $route) {
        $moveTargetLocation = ($route->start != $locationInfo->id) ? $route->start : $route->end;
        //need check that this location not have to much animal of actual type
        $stm = $this->db->prepare("SELECT id, number FROM animals
            WHERE location = :locationId AND type = :type LIMIT 1");
        $stm->bindInt("locationId", $moveTargetLocation);
        $stm->bindInt("type", $animalType->id);
        $stm->execute();

        $firstMigration = $stm->rowCount() == 0;
        if (!$firstMigration) {
          list($targetPackId, $animalCountInTargetLocation) = $stm->fetch(PDO::FETCH_NUM);
        }
        //if in target location we have to many animals now, we can't migrate there
        if (!$firstMigration && $animalCountInTargetLocation >= $animalType->max_in_location) {
          continue;
        }

        $stm = $this->db->prepare("SELECT COUNT(type) FROM animals a INNER JOIN animal_types at ON at.id = a.type
          WHERE location = :locationId
          AND NOT EXISTS (SELECT * FROM animal_domesticated_types adt WHERE adt.of_animal_type = at.id)");
        $stm->bindInt("locationId", $moveTargetLocation);
        $wildSpeciesInLocation = $stm->executeScalar();
        if ($firstMigration && ($wildSpeciesInLocation >= AnimalConstants::MAX_WILD_SPECIES_IN_LOCATION)) { // too many species already
          continue;
        }

        //teleport one animal
        $stm = $this->db->prepare("UPDATE animals SET number = number - 1 WHERE id = :id LIMIT 1");
        $stm->bindInt("id", $pack->id);
        $stm->execute();
        if ($firstMigration) {
          $stm = $this->db->prepare("INSERT INTO animals(location, type, number, damage) VALUES (:locationId, :type, 1, 0 )");
          $stm->bindInt("locationId", $moveTargetLocation);
          $stm->bindInt("type", $animalType->id);
          $stm->execute();
          $this->generateNewAnimalEvent($animalType, $moveTargetLocation);
        } else {
          $stm = $this->db->prepare("UPDATE animals SET number = number + 1 WHERE id = :id LIMIT 1");
          $stm->bindInt("id", $targetPackId);
          $stm->execute();
        }
        return true;
      }
    }
    return false;
  }

  public function increaseRawPool($animalType, $pack_info)
  {
    // load domesticated animal data
    $pack = AnimalPack::loadFromDb($pack_info->id, $animalType);
    if ($pack->ok) {

      $rawTypeRules = Parser::rulesToArray($pack->getTypeDetailsString());
      $rawPool = Parser::rulesToArray($pack->getSpecificsString());

      // iterate through "milking" etc.
      foreach (Animal::breedingActionsArray("_raws") as $actName) {
        if (array_key_exists($actName, $rawPool)) {
          // array of raw increase for that action
          $rawRules = Parser::rulesToArray($rawTypeRules[$actName], ",>");
          // value of raw pool of the pack for that action
          $tab = Parser::rulesToArray($rawPool[$actName], ",>");

          $tab = $this->newPoolFromRules($tab, $rawRules, $pack);

          $rawPool[$actName] = Parser::arrayToRules($tab, ",>");
        }
      }
      $pack->setSpecifics(Parser::arrayToRules($rawPool));
    }
  }

  private function newPoolFromRules($packRaws, & $typeRules, $pack)
  {
    foreach ($packRaws as $raw => &$amount) {
      list ($maxValue, $dailyIncrease, $dailyHarvest) = explode(">", $typeRules[$raw]);

      $hourlyIncrease = $dailyIncrease / _RUN_FREQUENCY;
      $threshold = AnimalConstants::HUNGER_THRESHOLD; // below that animal is considered as hungry ( => decreasing raw pool)

      if ($pack->getFullness() < $threshold) { // different formula for increase and decrease
        $fullnessFactor = 0.5 * ($pack->getFullness() - $threshold) / $threshold;
        $amountFactor = (0.85 + 0.3 * ($amount / $maxValue)); // when there's bigger amount of raw then decrease is bigger (+-15%)
      } else {
        $fullnessFactor = 0.5 + 0.5 * ($pack->getFullness() - $threshold) / (_SCALESIZE_GSS - $threshold);
        $amountFactor = (1.15 - 0.3 * ($amount / $maxValue)); // when there's smaller amount of raw then increase is bigger (+-15%)
      }

      import_lib("func.getrandom.inc.php");
      $newAmount = $amount + $hourlyIncrease * $fullnessFactor * $amountFactor;
      $newAmount = rand_round($newAmount);

      $amount = max(0, min($newAmount, $maxValue)); // amount must be in range [0, maxValue]
    }
    return $packRaws;
  }


  private function generateNewAnimalEvent($type, $location)
  {
    //generate event for observers
    $uniqueName = str_replace(' ', '_', $type->name);
    $encName = urlencode("<CANTR ANIMAL NAME=$uniqueName>");

    Event::create(271, "ANIMALTYPE=$encName")->inLocation($location)->show();
  }

  public function process($location)
  {
    $stm = $this->db->prepare("SELECT l.*, " .
      "(SELECT COUNT(*) FROM chars c WHERE c.location = l.id AND c.status = :active) AS charcount
      FROM locations l WHERE l.id = :locationId LIMIT 1");
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->bindInt("locationId", $location);
    $stm->execute();
    $locationInfo = $stm->fetchObject();

    $stm = $this->db->prepare("SELECT a.id, a.type, a.damage, a.number, ad.fullness FROM animals a
      LEFT JOIN animal_domesticated ad ON ad.from_animal = a.id WHERE location = :locationId");
    $stm->bindInt("locationId", $location);
    $stm->execute();

    $animalInLocation = array();
    foreach ($stm->fetchAll() as $animalPack) {
      $animalPack->name = $this->animalTypes[$animalPack->type]->name;
      $animalInLocation[$animalPack->id] = $animalPack;
    }

    $report = $this->prepareReportTable();
    foreach ($animalInLocation as $pack) {
      $animalType = $this->animalTypes[$pack->type];

      //call animal attack in locations, where we have any chars
      if ($locationInfo->charcount > 0) {
        if ($this->callAttack($animalType, $pack)) {
          $report['attack_attempts']++;
        }
      }

      //reproduction for wild and domesticated animals
      if ($animalType->domesticated == null) { // wild
        //born new wild animals in locations, where population isn't reach maximum
        if (/* $pack->number > 1 &&  Czez said single animals should reproduce too */
          $pack->number < $animalType->max_in_location) {
          if ($this->bornWild($animalType, $pack, $locationInfo)) {
            $report['new_animals']++;
          }
        }
      } else { // domesticated
        //born new domesticated animals in locations, if it doesn't exceed digging slots limit
        $newSlotNeeded = $animalType->max_in_location == 1 || (($pack->number + 1) % $animalType->max_in_location) == 1;
        $slotAvailable = $this->usedLocationSlots[$locationInfo->id] < $this->maxLocationSlots[$locationInfo->id];
        $atLeastPair = $pack->number > 1;

        if ($atLeastPair && (!$newSlotNeeded || $slotAvailable)) {
          if ($this->bornDomesticated($animalType, $pack, $locationInfo)) {
            $report['new_animals']++;
            if ($newSlotNeeded) {
              $this->usedLocationSlots[$locationInfo->id]++;
            }
          }
        }
      }

      //migrate somewhere to neighb locations, last pair and domesticated never migrate

//    Migration disabled by GreeK at 9.03.2019 as requested by Sanchez - RD chair
//      if ($pack->number > 2 && $animalType->domesticated == null) {
//        if ($this->migrate($animalType, $pack, $locationInfo)) {
//          $report['migration_count']++;
//        }
//      }

      // increase inner raw pool for domesticated animals
      if ($animalType->domesticated != null) {
        $this->increaseRawPool($animalType, $pack);
      }

      // if there are too many wild animals (usually when `max_in_location` is changed by RD), then their number can be reduced
      if ($pack->number > 1
        && $pack->number > $animalType->max_in_location
        && $animalType->domesticated == null
      ) {
        if ($this->deathOfOvercrowding($pack)) {
          $report['overcrowding_death']++;
        }
      }

    }
    return $report;
  }

  //by location
  public function processAll()
  {
    $report = $this->prepareReportTable();
    $stm = $this->db->query("SELECT location FROM animals GROUP BY location");
    foreach ($stm->fetchScalars() as $location) {
      $lReport = $this->process($location);
      foreach ($lReport as $repEntry => $value)
        $report[$repEntry] += $value;
    }

    return $report;
  }

  public function __construct()
  {
    $this->db = Db::get();

    $stm = $this->db->query("SELECT at.*, adt.id AS domesticated FROM animal_types at LEFT JOIN animal_domesticated_types adt ON at.id = adt.of_animal_type");
    $this->animalTypes = array();
    foreach ($stm->fetchAll() as $animalType) {
      $this->animalTypes[$animalType->id] = $animalType;
    }

    $this->usedLocationSlots = array();
    $this->maxLocationSlots = array();

    $stm = $this->db->query(
      "SELECT loc.id AS id, COUNT(ch.id) AS count
    FROM locations loc
    LEFT JOIN projects p ON p.location = loc.id AND p.uses_digging_slot = 1
    LEFT JOIN chars ch ON ch.project = p.id
    WHERE loc.type = 1 GROUP BY loc.id"
    ); // counts number of chars working on projects which use digging slots in all locations
    foreach ($stm->fetchAll() as $locationSlots) {
      if (!array_key_exists($locationSlots->id, $this->usedLocationSlots)) {
        $this->usedLocationSlots[$locationSlots->id] = 0;
      }
      $this->usedLocationSlots[$locationSlots->id] += $locationSlots->count;
    }

    $stm = $this->db->query(
      "SELECT loc.id AS id, SUM(CEIL(a.number / at.max_in_location)) AS count 
    FROM locations loc
    LEFT JOIN animals a ON a.location = loc.id
    INNER JOIN animal_types at ON at.id = a.type AND at.max_in_location > 0
    INNER JOIN animal_domesticated_types adt ON adt.of_animal_type = at.id
    WHERE loc.type = 1 GROUP BY loc.id"
    ); // counts number slots used by domesticated animals in all locations (ceil(curent_number/max_in_loc) == slots used)
    foreach ($stm->fetchAll() as $animalSlots) {
      $this->usedLocationSlots[$animalSlots->id] += $animalSlots->count;
    }

    $stm = $this->db->query("SELECT id, digging_slots AS value FROM locations WHERE type = 1");
    foreach ($stm->fetchAll() as $maxSlots) {
      $this->maxLocationSlots[$maxSlots->id] = $maxSlots->value;
    }

    $stm = $this->db->prepare("SELECT id, unique_name FROM objecttypes WHERE objectcategory = :category");
    $stm->bindInt("category", ObjectConstants::OBJCAT_TERRAIN_AREAS);
    $stm->execute();
    $this->areaTypes = array();
    foreach ($stm->fetchAll() as $areaType) {
      $this->areaTypes[$areaType->unique_name] = $areaType->id;
    }
  }

  private function deathOfOvercrowding($pack)
  {
    $random_percent = mt_rand(0, 100000) / 100000;
    $DEATH_CHANCE = 0.5 / _RUN_FREQUENCY;
    if ($random_percent < $DEATH_CHANCE) {
      // don't show any event, because this happens for OOC reasons
      $pack->number--;
      $stm = $this->db->prepare("UPDATE animals SET number = number - 1 WHERE id = :id");
      $stm->bindInt("id", $pack->id);
      $stm->execute();
      return true;
    }
    return false;
  }
}
