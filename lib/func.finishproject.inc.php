<?php
import_lib("func.expireobject.inc.php");

define("_CREATED_IN_INVENTORY", 1);
define("_CREATED_ON_GROUND", 2);

//this function will create specific weight of specific raws in target character inventory.
//if character have full inventory, this will be create on target location. if you won't set
//target location it will be automatically getted from target character location. if target location
//will be diffrent that char location, raws will be create on target location too.
//function return _CREATED_IN_INVENTORY or _CREATED_ON_GROUND flag.
//$raw_type can be ID, or name of raw type.
function create_raws($target_char, $raw_type, $raw_weight, $target_location = null)
{
  $db = Db::get();
  if (!is_numeric($raw_type)) {
    $raw_type = ObjectHandler::getRawIdFromName($raw_type);
  }

  $stm = $db->prepare("SELECT SUM(weight) FROM objects WHERE person = :charId");
  $stm->bindInt("charId", $target_char);
  $totalweight = $stm->executeScalar();

  $stm = $db->prepare("SELECT location,id FROM chars WHERE id = :charId AND status = :active");
  $stm->bindInt("charId", $target_char);
  $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
  $stm->execute();
  $ochar_info = $stm->fetchObject();
  if (!isset($target_location)) {
    $target_location = $ochar_info->location;
  }

  if ((($totalweight + $raw_weight) > _PEOPLE_MAXWEIGHT) || // initiator carry too much
    ($ochar_info->location != $target_location) || // initiator is not here
    (CharacterHandler::isNearDeath($ochar_info->id)) // initiator is in near death state
  ) {
    ObjectHandler::rawToLocation($target_location, $raw_type, $raw_weight);
    return _CREATED_ON_GROUND;
  } else {
    ObjectHandler::rawToPerson($target_char, $raw_type, $raw_weight);
    return _CREATED_IN_INVENTORY;
  }
}

//return id of main created objects . &$create_on_target will be filled by _CREATED_IN_INVENTORY or _CREATED_ON_GROUND (if not null)
function create_object($target_char, $result_object_type, $result_string, $target_location = null, &$create_on_target = null, $description = "")
{
  $db = Db::get();
  if (!is_numeric($result_object_type)) {
    $stm = $db->prepare("SELECT id FROM objecttypes WHERE unique_name LIKE :uniqueName");
    $stm->bindStr("uniqueName", $result_object_type);
    $result_object_type = $stm->executeScalar();
  }
  if (!isset($target_location)) {
    $stm = $db->prepare("SELECT location,id FROM chars WHERE id = :charId AND status = :active");
    $stm->bindInt("charId", $target_char);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $target_location = $stm->executeScalar();
  }

  $id_teller = 0;
  // ";" separates different objects to build
  $results = explode(";", $result_string);

  $id_to_return = 0;
  foreach ($results as $item) {

    // ":" separates parts of single item description
    list($tabledesc, $columndesc) = explode(":", $item);

    // 1st part describes table name and command
    list($tablename, $cmd) = explode(".", $tabledesc);

    // 2nd part describes columns and their values
    $table_columns = explode(",", $columndesc);


    $newSigns = [];

    if ($cmd == "add") {

      $column_names = "";
      $column_values = "";

      // iterate through columns
      $columnsData = [];
      foreach ($table_columns as $column) {

        $column_info = explode(">", $column);

        // if $column_info[1] is "var" then we need to determine its value (it's not given)
        if ($column_info[1] == "var") {

          switch ($column_info[2]) {

            case "initiator" :
              $stm = $db->prepare("SELECT location,id FROM chars WHERE id = :charId AND status = :active");
              $stm->bindInt("charId", $target_char);
              $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
              $charlocation = $stm->executeScalar();

              if ($charlocation == $target_location && !CharacterHandler::isNearDeath($target_char)) {

                // Initiator is here, so he can receive the result
                $column_info[1] = $target_char;
                if (isset($create_on_target)) {
                  $create_on_target = _CREATED_IN_INVENTORY;
                }

              } else {

                // Initiator not here, so leave it on the ground
                if (isset($create_on_target)) {
                  $create_on_target = _CREATED_ON_GROUND;
                }
                $column_info[0] = "location";
                $column_info[1] = $target_location;
              }
              break;

            case "location" :
              //location can be set only once
              if (!isset($columnsData["location"])) {
                $column_info[1] = $target_location;
              }
              break;

            case "firstid" :
              $column_info[1] = $id_array[0];
              break;

            default: // Check for $column_info[2] == 'id[n]' where n is a natural number.
              preg_match("/(id)\[(\d*)\]/", $column_info[2], $idnr);

              switch ($idnr[1]) {
                case "id" :
                  $column_info[1] = $id_array[$idnr[2]];
                  break;
              }

          }
        }
        //store columns data in array. in this way when column will be double we will just override it
        $columnsData[$column_info[0]] = "'" . str_replace("'", "`", urldecode($column_info[1])) . "'";

        if ($column_info[0] == "name") {
          $newSigns[] = urldecode($column_info[1]);
        }
      }

      if ($tablename == "locations") {
        $stm = $db->prepare("SELECT * FROM locations WHERE id = :locationId LIMIT 1");
        $stm->bindInt("locationId", $target_location);
        $stm->execute();
        $loc_info = $stm->fetchObject();

        if ($loc_info->x != null && $loc_info->y != null) {
          $columnsData['x'] = $loc_info->x;
          $columnsData['y'] = $loc_info->y;
        }
      }

      $column_names .= implode(",", array_keys($columnsData));
      $column_values .= implode(",", $columnsData);

      $query = "INSERT INTO " . $tablename . " (" . $column_names . ") VALUES (" . $column_values . ")";

      // TODO query is run as-is without prepared statement
      $db->query($query);
      print " (query: $query)";

      // uses auto_increment
      $id = $db->lastInsertId();
      $id_array[$id_teller++] = $id;

      $stm = $db->prepare("INSERT INTO signs (location,name) VALUES (:id, :sign)");
      foreach ($newSigns as $sign) {
        $stm->bindInt("id", $id);
        $stm->bindStr("sign", $sign);
        $stm->execute();
        print " (sign: $id, '$sign') ";
      }

      if (isset($columnsData['type']) && $columnsData['type'] == "'$result_object_type'") {
        $id_to_return = $id;
      }

      if ($tablename == "objects") {
        if (!empty($description)) {
          Descriptions::setDescription($id, Descriptions::TYPE_OBJECT, $description, $target_char);
        }
      }

      $radioType = CObject::GetRadioTypes()->typeid [$result_object_type];
      // Check, if radio item
      if ($radioType) {
        $stm = $db->prepare("SELECT * FROM locations WHERE id = :locationId LIMIT 1");
        $stm->bindInt("locationId", $target_location);
        $stm->execute();
        $loc_info = $stm->fetchObject();
        $stm = $db->prepare("
          INSERT INTO radios (item, type, repeater, frequency, location, x, y) 
          VALUES (:id, :type, :radioType, :frequency, :location, :x, :y)");
        $stm->execute([
          "id" => $id,
          "type" => $result_object_type,
          "radioType" => CObject::RadioTypeToInt($radioType),
          "frequency" => 100,
          "location" => $target_location,
          "x" => $loc_info->x,
          "y" => $loc_info->y,
        ]);
        print " (radio $radioType) ";
      }

    } elseif ($cmd == "invoke") { // for creating additional/custom event
      if ($tablename = "event" && $columndesc == "signal_fire") { // info that new signal fire started burning
        $stm = $db->prepare("SELECT * FROM locations WHERE id = :locationId LIMIT 1");
        $stm->bindInt("locationId", $target_location);
        $stm->execute();
        $loc_info = $stm->fetchObject();

        Event::createEventInRadius(316, "", $loc_info->x, $loc_info->y, _SIGNALFIRE_RANGE, false);
      }
    }

  }

  return $id_to_return;
}

/**
 * @param $project_info
 * @param $done
 * @param int $percent
 * @return mixed
 * @throws Exception
 */
function finish_project($project_info, $done, $percent = 1)
{
  $db = Db::get();
  $projectname = urlencode($project_info->name);

  /* *********** FINISH: DIGGING ************* */

  if ($project_info->type == ProjectConstants::TYPE_GATHERING) {
    $result = preg_split("/:/", $project_info->result);
    $req = preg_split("/:/", $project_info->reqneeded);

    $result[1] = floor($result[1] * $percent);
    if ($result[1] >= 1) {
      //removed the random deviation as per the change request on trello: #100 Remove random deviation of gathering
      $hCreateResult = create_raws($project_info->initiator, $result[0], $result[1], $project_info->location);
      switch ($hCreateResult) {
        case _CREATED_IN_INVENTORY:
          $id_project = Event::create(90, "PROJECT=$projectname AMOUNT=$result[1] OWNER=$project_info->initiator");
          $message_addition = $result[1] . " grams, ending up in the inventory of <CANTR CHARNAME ID=$project_info->initiator>";
          break;
        case _CREATED_ON_GROUND:
          $id_project = Event::create(89, "PROJECT=$projectname AMOUNT=$result[1]");
          $message_addition = $result[1] . " grams, ending up on the ground";
          break;
      }
    }
    $processingStats = new Statistic("raws_processing", $db);
    $reqNeeded = Parser::rulesToArray($project_info->reqneeded);
    $reqRaws = "";
    if (array_key_exists("raws", $reqNeeded)) {
      $rawsNeeded = Parser::rulesToArray($reqNeeded["raws"], ",>");
      $reqRaws = implode(",", array_keys($rawsNeeded));
    }
    $processingStats->store($result[0] . ";" . $reqRaws, $project_info->initiator, $result[1]);

    $stm = $db->prepare("SELECT COUNT(*) FROM raws WHERE location = :locationId AND type = :rawtype");
    $stm->bindInt("locationId", $project_info->location);
    $stm->bindInt("rawtype", $result[0]);
    $isRaw = $stm->executeScalar();
    if ($isRaw > 0) {
      $gatheringStats = new Statistic("raws_gathering_loc", $db);
      $gatheringStats->store($result[0] . ";" . $reqRaws, $project_info->location, $result[1]);
    }
  }

  /* *********** FINISH: BUILDING PROJECT ********** */

  if ($project_info->type == ProjectConstants::TYPE_MANUFACTURING) {
    print " (result: $project_info->result)";
    $not = null;
    create_object($project_info->initiator, $project_info->subtype, $project_info->result, $project_info->location, $not, $project_info->result_description);

    $manuStats = new Statistic("manufacturing", $db);
    $reqNeeded = Parser::rulesToArray($project_info->reqneeded);
    $reqRaws = "";
    if (array_key_exists("raws", $reqNeeded)) {
      $rawsNeeded = Parser::rulesToArray($reqNeeded["raws"], ",>");
      $reqRaws = implode(",", array_keys($rawsNeeded));
    }
    $manuStats->store($project_info->subtype . ";" . $reqRaws, $project_info->initiator, substr_count($project_info->result, "type>" . $project_info->subtype));

  }

  /* *********** FINISH: BURYING PROJECT *********** */

  if ($project_info->type == ProjectConstants::TYPE_BURYING) {
    try {
      $objectToBury = CObject::loadById($project_info->subtype);
      if ($objectToBury->getType() == ObjectConstants::TYPE_DEAD_BODY) {
        $charToBury = Character::loadById($objectToBury->getTypeid());
        $charToBury->setStatus(CharacterConstants::CHAR_BURIED);
        $charToBury->saveInDb();

        $objectToBury->annihilate();
      } else { // most likely it's buryable storage with bodies inside
        $deadBodiesInside = CObject::storedIn($objectToBury)->type(ObjectConstants::TYPE_DEAD_BODY)->findAll();
        foreach ($deadBodiesInside as $deadBody) {
          $deadBody->annihilate();
          $deadBody->saveInDb();
        }
        // bodies need to be annihilated and other contents moved outside - to avoid exploits in buryable storages
        $objectToBury->remove();
      }

      $statistic = new Statistic("buried", $db);
      $statistic->update($objectToBury->getUniqueName());

      $objectToBury->saveInDb();


    } catch (InvalidArgumentException $e) {
      Logger::getLogger("func.finishproject")->error("Unable to finish burying object " . $objectToBury->getId() .
        " (typeid: " . $objectToBury->getTypeid() . ")", $e);
    }
  }

  /* *********** FINISH: IMPROVEMENT PROJECT ********* */

  if ($project_info->type == ProjectConstants::TYPE_IMPROVING_ROADS) {

    list($connectionId, $targetTypeId) = explode(":", $project_info->result);

    try {
      $targetType = ConnectionType::loadById($targetTypeId);
      $connection = Connection::loadById($connectionId);

      if ($targetType->isPrimaryType()) {
        $connection->addPart($targetType);
      } else {
        $improvedPart = $connection->getConnectionPartImprovableTo($targetType);

        $improvedPart->setType($targetType);

        // there might be some already existing repair/destruction projects for this connection part
        $stm = $db->prepareWithIntList("SELECT id FROM projects WHERE type IN (:types) AND result = :result", [
          "types" => [ProjectConstants::TYPE_REPAIRING_ROAD, ProjectConstants::TYPE_DESTROYING_ROAD],
        ]);
        $stm->bindStr("result", $connectionId . ":" . $improvedPart->getType()->getName());
        $stm->execute();
        foreach ($stm->fetchScalars() as $projectId) {
          $canceler = ProjectCanceler::FromId($projectId, ProjectCanceler::NO_ACTOR, $db);
          $canceler->returnUsedResources(1.0);
          $canceler->deleteThisProject();
        }
      }
      $connection->saveInDb();
    } catch (InvalidArgumentException $e) {
      Logger::getLogger("func.finishproject")->error("connection improvement project was done on non-existing road part " .
        " because no road can be improved to " . $targetTypeId, $e);
    }
  }

  if ($project_info->type == ProjectConstants::TYPE_REPAIRING_ROAD) {
    list($connectionId, $targetTypeId) = explode(":", $project_info->result);

    try {
      $targetType = ConnectionType::loadById($targetTypeId);
      $connection = Connection::loadById($connectionId);
      $part = $connection->getConnectionPartWithType($targetType);
      $buildDays = $connection->getDaysToImproveTo($targetType);
      $fullRepairTurns = $buildDays * ConnectionConstants::REPAIR_TO_IMPROVEMENT_TIME * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

      $deterChange = $project_info->turnsneeded / $fullRepairTurns * 10000;
      $part->setDeterioration($part->getDeterioration() - $deterChange);

      $connection->saveInDb();
    } catch (InvalidArgumentException $e) {
      Logger::getLogger("func.finishproject")->error("connection repair project was done on non-existing " .
        "road part with target type: " . $targetTypeId, $e);
    }
  }

  if ($project_info->type == ProjectConstants::TYPE_DESTROYING_ROAD) {
    list($connectionId, $targetTypeId) = explode(":", $project_info->result);

    try {
      $targetType = ConnectionType::loadById($targetTypeId);
      $connection = Connection::loadById($connectionId);
      $part = $connection->getConnectionPartWithType($targetType);

      $toTypeId = $part->getType()->getImprovedFrom();

      if ($toTypeId > 0) {
        $toType = ConnectionType::loadById($toTypeId);
        $part->setType($toType); // downgrade the connection part
      } else {
        $connection->removePart($part); // remove the connection part
      }
      $connection->saveInDb();

      // there might be some already existing repair/improvement projects for this connection part
      $stm = $db->prepareWithIntList("SELECT id FROM projects WHERE type IN (:types) AND result = :result", [
        "types" => [ProjectConstants::TYPE_REPAIRING_ROAD, ProjectConstants::TYPE_IMPROVING_ROADS],
      ]);
      $stm->bindStr("result", $connectionId . ":" . $targetTypeId);
      $stm->execute();
      foreach ($stm->fetchScalars() as $projectId) {
        $canceler = ProjectCanceler::FromId($projectId, ProjectCanceler::NO_ACTOR, $db);
        $canceler->returnUsedResources(1.0);
        $canceler->deleteThisProject();
      }

      // there might be somebody travelling using a vehicle which will no longer work on downgraded connection
      $stm = $db->prepare("SELECT id FROM travels WHERE connection = :connectionId");
      $stm->bindInt("connectionId", $connection->getId());
      $stm->execute();
      foreach ($stm->fetchScalars() as $travelId) {
        try {
          $travel = Travel::loadById($travelId, $db);
          if ($travel->isVehicle() && !in_array($travel->getVehicle()->getVehicleType(), $connection->getAllowedVehicles())) {
            // this vehicle can no longer travel on this connection, so vehicle must be moved to the nearer of these
            if ($travel->getFractionDone() <= 0.5) {
              $travel->commitTurnAround();
            }
            $destinationId = $travel->getDestination();
            $vehicle = $travel->getVehicle();
            $travel->saveInDb();

            // we have to abandon this travel object - it'll no longer be synced with the database :(
            // todo there should be a better way, but there isn't yet. Land travel mechanism needs to be reworked
            $stm = $db->prepare("DELETE FROM travels WHERE id = :id");
            $stm->bindInt("id", $travel->getId());
            $stm->execute();
            $vehicle->setRegion($destinationId);
            $vehicle->saveInDb();

            // todo will need more specific message and another event for observers
            // inform people in vehicle they have arrived to the closer location
            $destName = urlencode("<CANTR LOCNAME ID=$destinationId>");
            Event::createEventInLocation(65, "PLACE=$destName", $vehicle->getId(), Event::RANGE_SAME_LOCATION);

            // report arrival for travel history
            $stm = $db->prepare("SELECT id FROM chars WHERE location = :locationId AND status = :status");
            $stm->bindInt("locationId", $vehicle->getId());
            $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
            $stm->execute();
            foreach ($stm->fetchScalars() as $charId) {
              $today = GameDate::NOW()->getObject();
              $stm = $db->prepare("INSERT INTO travelhistory (person, location, arrival, day, hour, vehicle)
                VALUES (:charId, :destination, :arrival, :day, :hour, :vehicle)");
              $stm->execute([
                "charId" => $charId,
                "destination" => $destinationId,
                "arrival" => 1,
                "day" => $today->day,
                "hour" => $today->hour,
                "vehicle" => $vehicle->getId(),
              ]);
            }
          }
        } catch (InvalidArgumentException $e) {
          Logger::getLogger("func.finishproject.inc.php", "no valid travel " . $travelId . " on connection " . $connection->getId());
        }
      }

    } catch (InvalidArgumentException $e) {
      Logger::getLogger("func.finishproject")->error("connection destruction project was done on non-existing " .
        "road part with target type: " . $targetTypeId, $e);
    }
  }

  /* *********** FINISH: LOCKPICKING PROJECT ********* */

  if ($project_info->type == ProjectConstants::TYPE_PICKING_LOCK) {

    $success = mt_rand(1, 100000);  // Lockpicking is successful at a 33% chance.

    print " - success: $success";

    if ($success <= 33333) {

      $message_addition = "successfully";
      $id_project = Event::create(68, "PROJECT=$projectname");

      print " - lock no. $project_info->result";

      notify_expiration($project_info->result);
      expire_object($project_info->result);

      // Delete possible project to pick the same lock from the 'other side' of the lock.
      $stm = $db->prepareWithIntList("DELETE FROM projects WHERE type in (:types) AND result = :result AND id != :projectId", [
        "types" => [ProjectConstants::TYPE_PICKING_LOCK, ProjectConstants::TYPE_DISASSEMBLING_OPEN_LOCK],
      ]);
      $stm->bindStr("result", $project_info->result);
      $stm->bindInt("projectId", $project_info->id);
      $stm->execute();

    } else {

      $message_addition = "unsuccessfully";
      $id_project = Event::create(69, "PROJECT=$projectname");
    }
  }

  /* *********** FINISH: DESTROYING LOCK ***************** */
  if ($project_info->type == ProjectConstants::TYPE_DISASSEMBLING_OPEN_LOCK) {
    
    try {
      $disassembled = CObject::loadById($project_info->subtype);
      $initiator = Character::loadById($project_info->initiator);

      $initiatorInLocation = $initiator->isAlive() && ($initiator->getLocation() == $project_info->location);
      
      $value_raws = 0.5;
      $value_objects = 1.0;
      
      $requirements = Parser::rulesToArray($disassembled->getBuildRequirements());
      $maxInv = $initiator->getMaxInventoryWeight();
      if (array_key_exists("raws", $requirements)) {
        $recycledRaws = Parser::rulesToArray($requirements["raws"], ",>");
        foreach ($recycledRaws as $rawName => $amount) {
          $recycledWeight = round($amount * $value_raws);
          $rawType = ObjectHandler::getRawIdFromName($rawName);
          $hasEnoughCapacity = $initiator->getInventoryWeight() + $recycledWeight <= $maxInv;
          if ($initiatorInLocation && $hasEnoughCapacity) {
            ObjectHandler::rawToPerson($initiator->getId(), $rawType, $recycledWeight);
          } else {
            ObjectHandler::rawToLocation($project_info->location, $rawType, $recycledWeight);
          }
        }
      }

      if (array_key_exists("objects", $requirements)) {
        $objectsRecycled = Parser::rulesToArray($requirements["objects"], ",>");
        foreach ($objectsRecycled as $objName => $number) {
          // select random type from the group
          $stm = $db->prepare("SELECT COUNT(*) FROM objecttypes WHERE name = :name");
          $stm->bindStr("name", $objName);
          $maxRand = $stm->executeScalar();
          $selectedObjectType = mt_rand(0, $maxRand - 1);
          $stm = $db->prepare("SELECT id, project_weight FROM objecttypes WHERE name = :name LIMIT :offset, 1");
          $stm->bindStr("name", $objName);
          $stm->bindInt("offset", $selectedObjectType);
          $stm->execute();
          list($objectType, $recycledWeight) = $stm->fetch(PDO::FETCH_NUM);
          import_lib("func.getrandom.inc.php");
          $toCreate = rand_round($value_objects * $number);
          for ($i = 0; $i < $toCreate; $i++) {
            $hasEnoughCapacity = ($initiator->getInventoryWeight() + $recycledWeight) <= $maxInv;
            if ($initiatorInLocation && $hasEnoughCapacity) {
              ObjectCreator::inInventory($project_info->initiator, $objectType,
                ObjectConstants::SETTING_PORTABLE, $recycledWeight)->create();
            } else {
              ObjectCreator::inLocation($project_info->location, $objectType,
                ObjectConstants::SETTING_PORTABLE, $recycledWeight)->create();
            }
          }
        }
      }
    } catch (InvalidArgumentException $e) {
      Logger::getLogger("func.finishproject")->error("Can't instantiate disass. obj: $project_info->subtype or initiator: $project_info->initiator for project $project_info->id.");
    }

    $message_addition = "successfully";
    $id_project = Event::create(68, "PROJECT=$projectname");

    notify_expiration($project_info->result);
    expire_object($project_info->result);

    // Delete possible project to pick the same lock from the 'other side' of the lock.
    $stm = $db->prepareWithIntList("DELETE FROM projects WHERE type in (:types) AND result = :result AND id != :projectId", [
      "types" => [ProjectConstants::TYPE_PICKING_LOCK, ProjectConstants::TYPE_DISASSEMBLING_OPEN_LOCK],
    ]);
    $stm->bindStr("result", $project_info->result);
    $stm->bindInt("projectId", $project_info->id);
    $stm->execute();

  }

  /* *********** FINISH PROJECT - COIN PRESS ********* */

  if ($project_info->type == ProjectConstants::TYPE_PRODUCING_COINS) {

    list($type, $number) = explode(":", $project_info->result);

    try {
      $press = CObject::loadById($project_info->subtype);
      $initiator = Character::loadById($project_info->initiator);

      $initiatorInLocation = $initiator->isAlive() && ($initiator->getLocation() == $project_info->location);

      $coinsWeight = ObjectConstants::WEIGHT_COIN * $number;
      $hasEnoughCapacity = ($initiator->getInventoryWeight() + $coinsWeight) <= $initiator->getMaxInventoryWeight();

      if ($initiatorInLocation && $hasEnoughCapacity) {
        ObjectHandler::coinsToPerson($initiator->getId(), $type, $press->getSpecifics(), $number);
      } else {
        ObjectHandler::coinsToLocation($project_info->location, $type, $press->getSpecifics(), $number);
      }

      // Overloading fields in tables - this query means setting the machine to being 'not in use'
      $stm = $db->prepare("UPDATE objects SET typeid = 0 WHERE id = :objectId LIMIT 1");
      $stm->bindInt("objectId", $project_info->subtype);
      $stm->execute();
    } catch (InvalidArgumentException $e) { // coin press o initiator doesn't exist
      Logger::getLogger("func.finishproject")->error("Can't instantiate press: $project_info->subtype or initiator: $project_info->initiator for project $project_info->id.");
    }
  }
  /* *********** FINISH PROJECT - RECYCLING  */

  if ($project_info->type == ProjectConstants::TYPE_DISASSEMBLING) {
    try {
      $disassembled = CObject::loadById($project_info->subtype);
      $initiator = Character::loadById($project_info->initiator);

      $initiatorInLocation = $initiator->isAlive() && ($initiator->getLocation() == $project_info->location);

      $rules = Parser::rulesToArray($disassembled->getRules());
      if (array_key_exists("recyclable", $rules)) {
        $recyclingRules = Parser::rulesToArray($rules["recyclable"], ",>");
        $value_raws = 0.5;
        $value_objects = 1.0;
        if (array_key_exists("raws", $recyclingRules)) {
          $value_raws = $recyclingRules["raws"] / 100;
        }
        if (array_key_exists("objects", $recyclingRules)) {
          $value_objects = $recyclingRules["objects"] / 100;
        }
      }

      $requirements = Parser::rulesToArray($disassembled->getBuildRequirements());
      $maxInv = $initiator->getMaxInventoryWeight();
      if (array_key_exists("raws", $requirements)) {
        $recycledRaws = Parser::rulesToArray($requirements["raws"], ",>");
        foreach ($recycledRaws as $rawName => $amount) {
          $recycledWeight = round($amount * $value_raws);
          $rawType = ObjectHandler::getRawIdFromName($rawName);
          $hasEnoughCapacity = $initiator->getInventoryWeight() + $recycledWeight <= $maxInv;
          if ($initiatorInLocation && $hasEnoughCapacity) {
            ObjectHandler::rawToPerson($initiator->getId(), $rawType, $recycledWeight);
          } else {
            ObjectHandler::rawToLocation($project_info->location, $rawType, $recycledWeight);
          }
        }
      }
      if (array_key_exists("objects", $requirements)) {
        $objectsRecycled = Parser::rulesToArray($requirements["objects"], ",>");
        foreach ($objectsRecycled as $objName => $number) {
          // select random type from the group
          $stm = $db->prepare("SELECT COUNT(*) FROM objecttypes WHERE name = :name");
          $stm->bindStr("name", $objName);
          $maxRand = $stm->executeScalar();
          $selectedObjectType = mt_rand(0, $maxRand - 1);
          $stm = $db->prepare("SELECT id, project_weight FROM objecttypes WHERE name = :name LIMIT :offset, 1");
          $stm->bindStr("name", $objName);
          $stm->bindInt("offset", $selectedObjectType);
          $stm->execute();
          list($objectType, $recycledWeight) = $stm->fetch(PDO::FETCH_NUM);
          import_lib("func.getrandom.inc.php");
          $toCreate = rand_round($value_objects * $number);
          for ($i = 0; $i < $toCreate; $i++) {
            $hasEnoughCapacity = ($initiator->getInventoryWeight() + $recycledWeight) <= $maxInv;
            if ($initiatorInLocation && $hasEnoughCapacity) {
              ObjectCreator::inInventory($project_info->initiator, $objectType,
                ObjectConstants::SETTING_PORTABLE, $recycledWeight)->create();
            } else {
              ObjectCreator::inLocation($project_info->location, $objectType,
                ObjectConstants::SETTING_PORTABLE, $recycledWeight)->create();
            }
          }
        }
      }
      $stat = new Statistic("disassembling", $db);
      $stat->store($disassembled->getUniqueName(), $initiator->getId());
    } catch (InvalidArgumentException $e) {
      Logger::getLogger("func.finishproject")->error("Can't instantiate disass. obj: $project_info->subtype or initiator: $project_info->initiator for project $project_info->id.");
    }

    $disassembled->remove();
    $disassembled->saveInDb();
  }

  /* *********** FINISH PROJECT - SIGNS *********** */

  if ($project_info->type == ProjectConstants::TYPE_ALTERING_SIGN) {
    $locationNaming = new LocationNaming(Location::loadById($project_info->subtype), $db);
    $locationNaming->applySignsChange($project_info->result);
  }

  /* *********** FINISH PROJECT - TEAR DOWN ************ */
  if ($project_info->type == ProjectConstants::TYPE_TEAR_DOWN) {

    $eventParams = [];
    $reqNeeded = Parser::rulesToArray($project_info->reqneeded);
    $reqLeft = Parser::rulesToArray($project_info->reqleft);

    if (isset($reqNeeded['raws'])) {
      $rawsNeeded = Parser::rulesToArray($reqNeeded['raws'], ",>");
      $rawsLeft = Parser::rulesToArray($reqLeft['raws'], ",>");

      $today = GameDate::NOW()->getDay();
      $projectAgeInDays = $today - $project_info->init_day;
      $daysSinceIntroductionOfTaint = $today - DeteriorationConstants::TAINT_INTRODUCTION_DAY;
      $daysOfTaint = max(0, min($projectAgeInDays, $daysSinceIntroductionOfTaint));

      $globalConfig = new GlobalConfig($db);
      foreach (array_keys($rawsNeeded) as $rawName) {
        $toRefund = $rawsNeeded[$rawName] - $rawsLeft[$rawName];
        if ($globalConfig->isUniversalTaintEnabled()) {
          $toRefund -= DeteriorationManager::accumulatedAmountToTaint($toRefund, $rawName, intval($daysOfTaint), $db);
        }
        if ($toRefund < 1) {
          continue;
        }

        $hCreateResult = create_raws($project_info->initiator, $rawName, $toRefund, $project_info->location);
        switch ($hCreateResult) {
          case _CREATED_IN_INVENTORY:
            $message_addition = $toRefund . " grams, ending up in the inventory of <CANTR CHARNAME ID=$project_info->initiator>";
            $unique_raw_name = str_replace(' ', '_', $rawName);
            $eventParams[] = "$toRefund <CANTR REPLACE NAME=grams_of> <CANTR REPLACE NAME=raw_$unique_raw_name>";
            break;
          case _CREATED_ON_GROUND:
            $message_addition = $toRefund . " grams, ending up on the ground";
            break;
        }
      }
    }
    if (isset($reqNeeded['objects'])) {
      $objectsNeeded = Parser::rulesToArray($reqNeeded['objects'], ",>");
      $objectsLeft = Parser::rulesToArray($reqLeft['objects'], ",>");

      foreach (array_keys($objectsNeeded) as $objectName) {
        $toRefund = $objectsNeeded[$objectName] - $objectsLeft[$objectName];
        if ($toRefund < 1) {
          continue;
        }

        $objectUniqueName = str_replace(' ', '_', $objectName);
        $stm = $db->prepare("SELECT id, build_result FROM objecttypes WHERE unique_name LIKE :uniqueName LIMIT 1");
        $stm->bindStr("uniqueName", $objectUniqueName);
        $stm->execute();
        list($objid, $build_result) = $stm->fetch(PDO::FETCH_NUM);
        if (!$objid) {
          $stm = $db->prepare("SELECT id, build_result FROM objecttypes WHERE name LIKE :name LIMIT 1");
          $stm->bindStr("name", $objectName);
          $stm->execute();
          list ($objid, $build_result) = $stm->fetch(PDO::FETCH_NUM);
        }

        //because ">var>typeid" is normal applied when project is creating.
        $build_result = str_replace('>var>typeid', ">$objid", $build_result);

        $create_on = 0;
        for ($i = 0; $i < $toRefund; $i++) {
          $new_object_id = create_object($project_info->initiator, $objid, $build_result, $project_info->location, $create_on);
        }
        if ($create_on == _CREATED_IN_INVENTORY) { // TODO! No message when objects created on the ground?
          $mess = "<CANTR OBJNAME ID=$new_object_id>";
          if ($toRefund > 1) {
            $mess = "$toRefund" . "x $mess";
          }
          $eventParams[] = $mess;
        }
      }
    }

    if (count($eventParams) > 0) {
      $recoverlist = urlencode(implode(', ', $eventParams));
      $stm = $db->prepare("SELECT 1 FROM chars WHERE id = :charId AND location = :locationId");
      $stm->bindInt("charId", $project_info->initiator);
      $stm->bindInt("locationId", $project_info->location);
      $initiatorInLocation = $stm->executeScalar();
      if ($initiatorInLocation) {
        Event::createPersonalEvent(266, "RECOVER=$recoverlist", $project_info->initiator);
      }
    }
  }

  // FINISH PROJECT - healing near dead char
  if ($project_info->type == ProjectConstants::TYPE_HEAL_NEAR_DEATH) {
    $healedChar = $project_info->subtype;
    $stm = $db->prepare("SELECT location FROM chars WHERE id = :charId");
    $stm->bindInt("charId", $healedChar);
    $charLoc = $stm->executeScalar();
    $isCharAlreadyCured = CharacterHandler::getNearDeathState($healedChar) == CharacterConstants::NEAR_DEATH_HEALED;
    if ($charLoc != $project_info->location || $isCharAlreadyCured) {
      $id_project = Event::create(69, "PROJECT=$projectname");
    } else {

      // make char healed
      $stm = $db->prepare("UPDATE char_near_death SET state = :state WHERE char_id = :charId LIMIT 1");
      $stm->bindInt("state", CharacterConstants::NEAR_DEATH_HEALED);
      $stm->bindInt("charId", $healedChar);
      $stm->execute();

      $stm = $db->prepare("SELECT day, hour FROM char_near_death WHERE char_id = :charId LIMIT 1");
      $stm->bindInt("charId", $healedChar);
      $stm->execute();
      $healed_info = $stm->fetchObject();

      // get day when near dead will lose NDS status and add num of days when he/she can't eat (to make healing impossible)
      $cannotEatTimeEnd = Limitations::dhmstoc($healed_info->day + CharacterConstants::NEAR_DEATH_CANNOT_EAT, $healed_info->hour, 0, 0);
      $secsToEnd = $cannotEatTimeEnd - Limitations::getCtime();

      Limitations::addLim($healedChar, Limitations::TYPE_NOT_EAT_AFTER_NEAR_DEATH, $secsToEnd);

      import_lib("func.genes.inc.php");
      set_state($healedChar, StateConstants::HEALTH, CharacterConstants::NEAR_DEATH_HEALED_HEALTH);

      // notify char in nds that is cured
      Event::createPersonalEvent(310, "", $healedChar);
      // notify all watchers that char is healed
      Event::createPublicEvent(311, "VICTIM=$healedChar", $healedChar, Event::RANGE_NEAR_LOCATIONS, [$healedChar]);
    }
  }


  /* ****** FINISH PROJECT - DESCRIPTION CHANGE ******* */

  if ($project_info->type == ProjectConstants::TYPE_DESC_BUILDING_CHANGE) {
    Descriptions::setDescription($project_info->location, Descriptions::TYPE_BUILDING, $project_info->result, $project_info->initiator);
  }

  /* ****** FINISH PROJECT - ANIMAL TAMING ******* */

  if ($project_info->type == ProjectConstants::TYPE_TAMING_ANIMAL) {
    $stm = $db->prepare("SELECT type_details, of_object_type, tame_rules
      FROM animal_domesticated_types WHERE of_animal_type = :animalType");
    $stm->bindInt("animalType", $project_info->subtype);
    $stm->execute();
    list ($typeData, $objectType, $rules) = $stm->fetch(PDO::FETCH_NUM);
    $typeData = Parser::rulesToArray($typeData);
    $rules = Parser::rulesToArray($rules);

    $stm = $db->prepare("SELECT a.id FROM animals a
      INNER JOIN animal_types at ON at.id = a.type
      WHERE a.location = :locationId AND at.domesticable_into = :animalType");
    $stm->bindInt("locationId", $project_info->location);
    $stm->bindInt("animalType", $project_info->subtype);
    $wildPack = $stm->executeScalar();
    $success_perc = isset($rules['success_chance']) ? $rules['success_chance'] : AnimalConstants::DEFAULT_TAME_SUCCESS_CHANCE;

    if ($wildPack != null && $success_perc >= mt_rand(1, 100)) {
      $id_project = Event::create(68, "PROJECT=$projectname");

      $create_on_target = _CREATED_ON_GROUND;
      $object_id = create_object($project_info->initiator, $objectType, $project_info->result, $project_info->location, $create_on_target);

      // initialise 0 for animal harvestable raw pool
      $specs = [];
      foreach (Animal::breedingActionsArray("_raws") as $opt) {
        if ($typeData[$opt] != null) { // that action is possible for that type of animal
          $actionData = Parser::rulesToArray($typeData[$opt], ",>");
          foreach ($actionData as $raw => &$amount) {
            $amount = 0;
          }
          $specs[$opt] = Parser::arrayToRules($actionData, ",>");
        }
      }
      $specs = Parser::arrayToRules($specs);

      // initialise `animal_domesticated` values
      $stm = $db->prepare("INSERT INTO `animal_domesticated` (from_object, fullness, specifics)
        VALUES (:objectId, :fullness, :specifics)");
      $stm->bindInt("objectId", $object_id);
      $stm->bindInt("fullness", AnimalConstants::INITIAL_FULLNESS);
      $stm->bindStr("specifics", $specs);
      $stm->execute();

      // decrease number of animals
      $pack = AnimalPack::loadFromDb($wildPack);
      $pack->decrementNumber();
    } else {
      $id_project = Event::create(69, "PROJECT=$projectname");
      $pack = AnimalPack::loadFromDb($wildPack);
      $random_percent = mt_rand(0, 100000) / 100000;
      if ($random_percent <= $pack->getAttackChance()) {
        if ($pack->ok) {
          $stm = $db->prepare("SELECT id FROM chars WHERE project = :projectId ORDER BY rand() LIMIT 1");
          $stm->bindInt("projectId", $project_info->id);
          $attackedChar = $stm->executeScalar();
          $pack->attackChar($attackedChar);
        }
      }
    }
  }

  /* ***** FINISH PROJECT - ANIMAL HARVESTING ****** */

  if ($project_info->type == ProjectConstants::TYPE_HARVESTING_ANIMAL) {
    list ($action, $rawType, $rawAmount) = explode(":", $project_info->result);

    $stm = $db->prepare("SELECT id FROM animals WHERE type = :type AND location = :locationId");
    $stm->bindInt("type", $project_info->subtype);
    $stm->bindInt("locationId", $project_info->location);
    $pack_id = $stm->executeScalar();

    if ($pack_id != null) {
      $pack = AnimalPack::loadFromDb($pack_id);

      if ($pack != null && $pack->ok && $pack->isDomesticated()) { // if there exist suitable pack
        $rules = Parser::rulesToArray($pack->getSpecificsString());
        $actName = $action . "_raws";
        if ($rules[$actName]) { // check if that harvesting action is available
          list ($storedRaw, $storedAvgAmt) = explode(">", $rules[$actName]);
          $storedRawId = CObject::getRawIdFromName($storedRaw);
          if ($storedRawId == $rawType) {
            $rawAmount = floor(mt_rand(0.9 * $rawAmount, 1.1 * $rawAmount)); // raw output is +- 10% base value
            $rawAvailable = $storedAvgAmt * $pack->getNumber();
            $rawAmount = min($rawAmount, $rawAvailable); // harvest as much as possible if there's enough in animal raw pool
            $hCreateResult = create_raws($project_info->initiator, $rawType, $rawAmount, $project_info->location);
            switch ($hCreateResult) {
              case _CREATED_IN_INVENTORY:
                $id_project = Event::create(90, "PROJECT=$projectname AMOUNT=$rawAmount OWNER=$project_info->initiator");
                $message_addition = "$rawAmount grams, ending up in the inventory of <CANTR CHARNAME ID=$project_info->initiator>";
                break;
              case _CREATED_ON_GROUND:
                $id_project = Event::create(89, "PROJECT=$projectname AMOUNT=$rawAmount");
                $message_addition = "$rawAmount grams, ending up on the ground";
                break;
            }
            // alter raw pool value
            $newAvgAmt = round(($rawAvailable - $rawAmount) / $pack->getNumber());
            $rules[$actName] = "{$storedRaw}>{$newAvgAmt}";
            $rules = Parser::arrayToRules($rules);
            $pack->setSpecifics($rules);
          }
        }
      }
    }
  }

  /* ****** FINISH PROJECT - ANIMAL ADOPTION ******* */

  if ($project_info->type == ProjectConstants::TYPE_ADOPTING_ANIMAL) {

    $animalObject = DomesticatedAnimalObject::loadFromDb($project_info->subtype);
    $rules = $animalObject->getTameRulesArray();
    $success_perc = isset($rules['success_chance']) ? $rules['success_chance'] : AnimalConstants::DEFAULT_TAME_SUCCESS_CHANCE;

    $stm = $db->prepare("SELECT COUNT(*) FROM chars WHERE id = :charId AND project = :projectId");
    $stm->bindInt("charId", $animalObject->getLoyalTo());
    $stm->bindInt("projectId", $project_info->id);
    $isOwnerWorking = $stm->executeScalar();
    if ($isOwnerWorking) { // if owner is helping then project always succeeds
      $success_perc = 100;
    }
    $stm = $db->prepare("SELECT location FROM chars WHERE id = :charId");
    $stm->bindInt("charId", $project_info->initiator);
    $initiatorLoc = $stm->executeScalar();
    if ($initiatorLoc != $project_info->location) {
      $success_perc = 0;
    }

    $stm = $db->prepare("SELECT 1 FROM objects WHERE id = :objectId AND person = :charId");
    $stm->bindInt("objectId", $animalObject->getId());
    $stm->bindInt("charId", $project_info->initiator);
    $animalInInitiatorInventory = $stm->executeScalar();
    $badLocation = ($animalObject != null) && ($animalObject->getLocation() != $project_info->location) && !$animalInInitiatorInventory;

    if (!$badLocation && mt_rand(1, 100) <= $success_perc) {
      $id_project = Event::create(68, "PROJECT=$projectname");
      Event::create(286, "ACTOR=$project_info->initiator ANIMAL=" . $animalObject->getName())->
      inLocation($project_info->location)->show();
      // update animal loyalty
      $stm = $db->prepare("UPDATE animal_domesticated SET loyalty = :loyalty,
        loyal_to = :ownerId WHERE from_object = :objectId");
      $stm->bindInt("loyalty", AnimalConstants::INITIAL_LOYALTY);
      $stm->bindInt("ownerId", $project_info->initiator);
      $stm->bindInt("objectId", $project_info->subtype);
      $stm->execute();
    } else {
      $id_project = Event::create(69, "PROJECT=$projectname");
    }
  }

  /* ****** FINISH PROJECT - STEED ADOPTION ******* */

  if ($project_info->type == ProjectConstants::TYPE_ADOPTING_STEED) {

    try {
      $steed = LandVehicle::loadById($project_info->subtype);
      if (!$steed instanceof Steed) {
        throw new InvalidArgumentException("this vehicle is not a steed");
      }

      $stm = $db->prepare("SELECT tame_rules FROM animal_domesticated_types WHERE of_animal_type = :animalType");
      $stm->bindInt("animalType", $steed->getVehicleType());
      $tameRules = $stm->executeScalar(); // small todo - better to have it in steed
      $rules = Parser::rulesToArray($tameRules);
      $success_perc = isset($rules['success_chance']) ? $rules['success_chance'] : AnimalConstants::DEFAULT_TAME_SUCCESS_CHANCE;

      $oldOwner = $steed->getLoyalTo();
      $stm = $db->prepare("SELECT COUNT(*) FROM chars WHERE id = :charId AND project = :projectId");
      $stm->bindInt("charId", $oldOwner);
      $stm->bindInt("projectId", $project_info->id);
      $isOwnerWorking = $stm->executeScalar();
      if ($isOwnerWorking) { // if owner is helping then project always succeeds
        $success_perc = 100;
      }
      $stm = $db->prepare("SELECT location FROM chars WHERE id = :charId");
      $stm->bindInt("charId", $project_info->initiator);
      $initiatorLoc = $stm->executeScalar();
      if ($initiatorLoc != $project_info->location) {
        $success_perc = 0;
      }
      $steedLoc = $steed->getLocation();
      $goodLocation = $steedLoc->getRegion() == $project_info->location;

      if ($goodLocation && mt_rand(1, 100) <= $success_perc) {
        $id_project = Event::create(68, "PROJECT=$projectname"); // TODO!!!
        Event::create(360, "ACTOR=$project_info->initiator STEED=" . $steed->getId())->
        inLocation($steedLoc->getRegion())->show();
        // update animal loyalty
        $newOwner = $project_info->initiator;
        $stm = $db->prepare("UPDATE animal_domesticated SET loyalty = :loyalty,
          loyal_to = :ownerId WHERE from_location = :locationId");
        $stm->bindInt("loyalty", AnimalConstants::INITIAL_LOYALTY);
        $stm->bindInt("ownerId", $newOwner);
        $stm->bindInt("locationId", $project_info->subtype);
        $stm->execute();

        $stm = $db->prepare("SELECT unique_name FROM objecttypes WHERE id = :id");
        $stm->bindInt("id", $steed->getVehicleType());
        $animalName = $stm->executeScalar();
        $animalTag = "<CANTR REPLACE NAME=animal_" . $animalName . "_s>";
        $ownerTag = "<CANTR CHARNAME ID={$newOwner}>";
        $steedLoc->setName("$animalTag<CANTR REPLACE NAME=name_steed_of> $ownerTag");
        $steedLoc->saveInDb();

      } else {
        $id_project = Event::create(69, "PROJECT=$projectname");
      }
    } catch (InvalidArgumentException $e) {
      $id_project = Event::create(69, "PROJECT=$projectname");
    }
  }


  /* ****** FINISH PROJECT - ANIMAL SLAUGHTERING/BUTCHERING ******* */

  if ($project_info->type == ProjectConstants::TYPE_BUTCHERING_ANIMAL) {

    $animalObject = DomesticatedAnimalObject::loadFromDb($project_info->subtype);

    if ($animalObject && $animalObject->getLocation() == $project_info->location) {

      $raws = $animalObject->getRawPoolArray("butchering_raws");
      foreach ($raws as $raw) { // create all raw piles
        $rawType = ObjectHandler::getRawIdFromName($raw['name']);
        create_raws($project_info->initiator, $rawType, $raw['amount'], $project_info->location);
      }
      $animalObject->annihilate();
    } else {
      $id_project = Event::create(294, "PROJECT=$projectname");
    }
  }

  /* ****** FINISH PROJECT - OBJECT REPAIRING ******* */

  if ($project_info->type == ProjectConstants::TYPE_REPAIRING) {
    $hours = $project_info->turnsneeded / (ProjectConstants::DEFAULT_PROGRESS_PER_DAY / GameDateConstants::HOURS_PER_DAY);

    $stm = $db->prepare("SELECT o.deterioration, ot.repair_rate FROM objects o 
      INNER JOIN objecttypes ot ON ot.id = o.type WHERE o.id = :objectId");
    $stm->bindInt("objectId", $project_info->subtype);
    $stm->execute();
    list ($objDeter, $repairPerHour) = $stm->fetch(PDO::FETCH_NUM);
    $deterReduce = $hours * $repairPerHour;

    $newDeterioration = max(0, round($objDeter - $deterReduce));
    $stm = $db->prepare("UPDATE objects SET deterioration = :deterioration WHERE id = :objectId");
    $stm->bindInt("deterioration", $newDeterioration);
    $stm->bindInt("objectId", $project_info->subtype);
    $stm->execute();
  }

  if ($project_info->type == ProjectConstants::TYPE_DESC_OBJECT_CHANGE) {
    Descriptions::setDescription($project_info->subtype, Descriptions::TYPE_OBJECT,
      $project_info->result_description, $project_info->initiator);
  }

  /* ****** FINISH PROJECT - BUILDING DESTRUCTION ******* */

  if ($project_info->type == ProjectConstants::TYPE_DESTROYING_BUILDING) {
    try {
      $loc = Location::loadById($project_info->subtype);
      if ($loc->getType() == LocationConstants::TYPE_BUILDING) {
        $isBuildingPredicate = function(Location $loc) {
          return $loc->getType() == LocationConstants::TYPE_BUILDING;
        };
        $sublocs = $loc->getSublocationsRecursive($isBuildingPredicate);
        $sublocs[] = $loc->getId();

        $razeReport = new Statistic("razed", $db);

        foreach ($sublocs as $sublocId) {
          $building = Location::loadById($sublocId);
          if ($building->isDestroyable()) {
            Event::createEventInLocation(327, "", $sublocId, Event::RANGE_SAME_LOCATION, []);
            $building->destroy();

            // remember who has started destruction of the building
            $razeReport->store($loc->getId(), $project_info->initiator, 1);
          } else {
            Event::createEventInLocation(328, "", $sublocId, Event::RANGE_SAME_LOCATION, []);
          }
        }
      }
    } catch (InvalidArgumentException $e) {
      Logger::getLogger("func.finishproject.inc.php")->error("Should never happen. " .
        "Incorrect (sub)location for building destruction", $e);
    }
  }

  /* ****** FINISH PROJECT - SADDLING STEED ******* */

  if ($project_info->type == ProjectConstants::TYPE_SADDLING_STEED) {
    $toBecomeSteed = DomesticatedAnimalObject::loadById($project_info->subtype);

    if (($toBecomeSteed == null) ||
      ($toBecomeSteed->getLocation() != $project_info->location)
    ) { // there's no animal to saddle or not here
      $id_project = Event::create(69, "PROJECT=$projectname");
    } else {
      try {
        $parentLoc = Location::loadById($project_info->location);

        $owner = $toBecomeSteed->getLoyalTo();
        $steedName = $toBecomeSteed->getNameTag() . "<CANTR REPLACE NAME=name_steed_of> <CANTR CHARNAME ID=$owner>";

        // check if such steed existed in the past
        $existingSteed = null;
        $stm = $db->prepare("SELECT location FROM location_object_junction WHERE object = :objectId");
        $stm->bindInt("objectId", $toBecomeSteed->getId());
        $existingSteedId = $stm->executeScalar();
        if ($existingSteedId != null) {
          try {
            $steed = Location::loadById($existingSteedId); // we must check if parameters match
            if ($steed->isVehicle() && ($steed->getArea() == $toBecomeSteed->getObjectType())) {
              $existingSteed = $steed;
            }
          } catch (InvalidArgumentException $e) {
            $existingSteed = null; // to make it explicit
          }
        }
        if ($existingSteed !== null) { // reuse existing location to preserve charnaming
          $stm = $db->prepare("DELETE FROM location_object_junction WHERE location = :location");
          $stm->bindInt("location", $existingSteed->getId());
          $stm->execute();

          $existingSteed->revive();
          $existingSteed->setName($steedName);
          $existingSteed->setRegion($parentLoc->getId());
          $existingSteed->setX($parentLoc->getX());
          $existingSteed->setY($parentLoc->getY());
          $existingSteed->saveInDb();
          $locId = $existingSteed->getId();
        } else { // create a new location
          $stm = $db->prepare("INSERT INTO locations (name, type, region, area, x, y)
            VALUES (:name, :type, :region, :objectType, :x, :y)");
          $stm->bindStr("name", $steedName);
          $stm->bindInt("type", LocationConstants::TYPE_VEHICLE);
          $stm->bindInt("region", $parentLoc->getId());
          $stm->bindInt("objectType", $toBecomeSteed->getObjectType());
          $stm->bindInt("x", $parentLoc->getX());
          $stm->bindInt("y", $parentLoc->getY());
          $stm->execute();
          $locId = $db->lastInsertId();
        }
        $stm = $db->prepare("UPDATE animal_domesticated SET from_animal = 0,
         from_location = :locationId, from_object = 0 WHERE from_object = :objectId");
        $stm->bindInt("locationId", $locId);
        $stm->bindInt("objectId", $toBecomeSteed->getId());
        $stm->execute();
        $toBecomeSteed->annihilate();
      } catch (InvalidArgumentException $e) {
        $id_project = Event::create(69, "PROJECT=$projectname");
        Logger::getLogger("func.finishproject.inc.php")->error("no animal object id $project_info->subtype to saddle", $e);
      }
    }
  }

  /* ****** FINISH PROJECT - UNSADDLING STEED ******* */

  if ($project_info->type == ProjectConstants::TYPE_UNSADDLING_STEED) {

    try {
      $steed = LandVehicle::loadById($project_info->subtype);
      $animalLoc = $steed->getLocation();

      if (($animalLoc->getRegion() == $project_info->location) && $animalLoc->isEmpty()) {
        $stm = $db->prepare("SELECT of_animal_type, weight FROM animal_domesticated_types WHERE of_object_type = :objectId");
        $stm->bindInt("objectId", $steed->getVehicleType());
        $stm->execute();
        list($animalType, $weight) = $stm->fetch(PDO::FETCH_NUM);

        $stm = $db->prepare("INSERT INTO objects (location, person, attached, type, typeid, weight, setting)
          VALUES (:locationId, 0, 0, :type, 0, :weight, :setting)");
        $stm->bindInt("locationId", $steed->getRegion());
        $stm->bindInt("type", $steed->getVehicleType());
        $stm->bindInt("weight", $weight);
        $stm->bindInt("setting", ObjectConstants::SETTING_PORTABLE);
        $stm->execute();
        $objectId = $db->lastInsertId();

        $stm = $db->prepare("UPDATE animal_domesticated SET from_animal = 0, from_location = 0, from_object = :objectId
          WHERE from_location = :locationId");
        $stm->bindInt("objectId", $objectId);
        $stm->bindInt("locationId", $steed->getId());
        $stm->execute();

        $stm = $db->prepare("SELECT build_result FROM objecttypes WHERE id = :id");
        $stm->bindInt("id", ObjectConstants::TYPE_SADDLE);
        $saddleResult = $stm->executeScalar();
        create_object($project_info->initiator, ObjectConstants::TYPE_SADDLE, $saddleResult, $project_info->location); // create saddle

        $stm = $db->prepare("REPLACE INTO location_object_junction (location, object) VALUES (:locationId, :objectId)");
        $stm->bindInt("locationId", $steed->getId());
        $stm->bindInt("objectId", $objectId);
        $stm->execute();
        // update junction table to remember which steed was it, to be able to restore it later

        $steed->remove();
        $steed->saveInDb();
      } else {
        $id_project = Event::create(350, "");
      }
    } catch (InvalidArgumentException $e) {
      $id_project = Event::create(69, "PROJECT=$projectname");
      Logger::getLogger("func.finishproject.inc.php")->error("something went wrong,
        no steed id $project_info->subtype to unsaddle", $e);
    }
  }


  /* ****** FINISH PROJECT - DISASSEMBLING VEHICLES ******* */

  if ($project_info->type == ProjectConstants::TYPE_DISASSEMBLING_VEHICLE) {
    try {
      $vehicle = Location::loadById($project_info->subtype);

      if (($vehicle->getRegion() == $project_info->location) && $vehicle->isEmpty()) {
        $stm = $db->prepare("SELECT rules FROM objecttypes WHERE id = :id");
        $stm->bindInt("id", $vehicle->getArea());
        $disRules = $stm->executeScalar();
        $disRules = Parser::rulesToArray($disRules)['disassemblable'];
        $disRules = Parser::rulesToArray($disRules, ",>");

        $rawRatio = isset($disRules['raws']) ? ($disRules['raws'] / 100) : 0.5; // default for raw refund perc
        $objRatio = isset($disRules['objects']) ? ($disRules['objects'] / 100) : 1.0; // default for obj refund perc

        $stm = $db->prepare("SELECT build_requirements FROM objecttypes WHERE id = :id");
        $stm->bindInt("id", $vehicle->getArea());
        $buildReq = $stm->executeScalar();
        $buildReq = Parser::rulesToArray($buildReq);
        if (isset($buildReq['raws'])) {
          $raws = Parser::rulesToArray($buildReq['raws'], ",>");
          foreach ($raws as $rawName => $amount) {
            create_raws($project_info->initiator, $rawName, round($amount * $rawRatio), $project_info->location);
          }
        }
        if (isset($buildReq['objects'])) {
          $objects = Parser::rulesToArray($buildReq['objects'], ",>");
          foreach ($objects as $objName => $number) {
            $stm = $db->prepare("SELECT COUNT(*) FROM objecttypes WHERE name = :name");
            $stm->bindStr("name", $objName);
            $maxRand = $stm->executeScalar();
            $randomValue = rand(0, $maxRand - 1);
            $stm = $db->prepare("SELECT * FROM objecttypes WHERE name = :name LIMIT :offset, 1");
            $stm->bindStr("name", $objName);
            $stm->bindInt("offset", $randomValue);
            $stm->execute();
            $otData = $stm->fetchObject();
            for ($i = 0; $i < $number; $i++) {
              if ((mt_rand(0, 1000000) / 1000000) <= $objRatio) {
                create_object($project_info->initiator, $otData->id, str_replace("var>typeid", $otData->id, $otData->build_result), $project_info->location);
              }
            }
          }
        }

        $vehicle->remove();
        $vehicle->saveInDb();
      } else {
        $id_project = Event::create(69, "PROJECT=$projectname");
      }
    } catch (InvalidArgumentException $e) {
      $id_project = Event::create(69, "PROJECT=$projectname");
      Logger::getLogger("func.finishproject.inc.php")->error("something went wrong,
        no steed id $project_info->subtype to unsaddle", $e);
    }
  }

  /* *********** FINISH PROJECT - GENERAL ************ */

  if ($project_info->steps) {

    // In cases steps > 0, reduce steps and keep project in database.

    print " - continued ($projectname [$message_addition])";

  } else {

    print " - being finished ($projectname [$message_addition])";

    if (!$id_project) {
      $id_project = Event::create(91, "PROJECT=$projectname");
    }

    $stm = $db->prepare("SELECT id FROM chars WHERE project = :projectId AND status = :active");
    $stm->bindInt("projectId", $project_info->id);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();
    $participants = $stm->fetchScalars();

    $observers = $participants;
    $stm = $db->prepare("SELECT id FROM chars WHERE status = :active AND project != :projectId
                       AND location = :locationId AND id = :charId LIMIT 1");
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->bindInt("projectId", $project_info->id);
    $stm->bindInt("locationId", $project_info->location);
    $stm->bindInt("charId", $project_info->initiator);
    $initiatorId = $stm->executeScalar();
    if ($initiatorId) {
      $observers[] = $initiatorId;
    }

    $id_project->forCharacters($observers)->show();

    $stm = $db->prepare("UPDATE chars SET project = 0 WHERE id = :charId LIMIT 1");
    foreach ($participants as $participantId) {
      $stm->bindInt("charId", $participantId);
      $stm->execute();
    }

    $stm = $db->prepare("DELETE FROM projects WHERE id = :projectId LIMIT 1");
    $stm->bindInt("projectId", $project_info->id);
    $stm->execute();

    $done++;
  }

  return $done;
}
