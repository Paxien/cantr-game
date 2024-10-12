<?php
require_once("func.expireobject.inc.php");

function projectHasReq($reqInfo)
{
  $mainParts = Parser::rulesToArray($reqInfo);
  foreach ($mainParts as $partName => $mainPart) {
    if (in_array($partName, array("objects", "raws"))) {
      $part = Parser::rulesToArray($mainPart, ",>");
      foreach ($part as $left) {
        if ($left > 0) {
          return false;
        }
      }
    }
  }
  return true;
}

function automatic_add_to_project($project, $character, $add_regardless = false)
{
  //returns true if project can be joined
  $db = Db::get();
  //check if project 
  $stm = $db->prepare("SELECT reqleft FROM projects WHERE id = :projectId LIMIT 1");
  $stm->bindInt("projectId", $project);
  $reqleft = $stm->executeScalar();

  $parts = preg_split('/;/', $reqleft);
  while (list ($key, $val) = each($parts)) {
    $ruleparts = preg_split('/:/', $val);
    $rules["$ruleparts[0]"] = $ruleparts[1];
  }
  $accepted = true;
  $join_project = true;
  $obj_id = array(); //-1 not found 0 = found an object other=object.id of raw
  $obj_actual = array();
  $obj_need = array();
  $obj_name = array();


  if (isset($rules["raws"])) {

    $raws = preg_split('/,/', $rules["raws"]);

    for ($i = 0; $i < count($raws); $i++) {

      if (($add_regardless) || ($accepted)) {

        $raw = preg_split('/>/', $raws[$i]);

        $obj_need[] = $raw[1];
        $obj_name[] = $raw[0];

        $stm = $db->prepare("SELECT o.weight,o.id FROM objects o,rawtypes r
          WHERE o.person= :charId AND r.id = o.typeid AND o.type = 2 AND r.name = :rawName LIMIT 1");
        $stm->bindInt("charId", $character);
        $stm->bindStr("rawName", $raw[0]);
        $stm->execute();
        if (($raw_weight = $stm->fetchObject()) && ($raw[1])) {

          $obj_id[] = $raw_weight->id;
          $obj_actual[] = $raw_weight->weight;


          if (($raw_weight->weight - $raw[1] < 0) and (!$add_regardless)) {
            $accepted = false;
          }
          if (($raw_weight->weight - $raw[1] < 0)) {
            $join_project = false;
          }
        } else { //no raw found or needed

          $obj_id[] = -1;
          $obj_actual[] = 0;

          if (!$add_regardless) {
            $accepted = false;
          }
          if ($raw[1]) {
            $join_project = false;
          }
        }
      }
    }
  }
  // now do the same for objects
  if (isset($rules["objects"])) {

    $objects = preg_split('/,/', $rules["objects"]);

    for ($i = 0; $i < count($objects); $i++) {

      if (($add_regardless) || ($accepted)) {

        $object = preg_split('/>/', $objects[$i]);

        $obj_need[] = $object[1];
        $obj_name[] = $object[0];

        $stm = $db->prepare("SELECT count(o.id) AS count FROM objects o,objecttypes t
          WHERE o.person = :charId AND t.id=o.type  AND t.name = :name");
        $stm->bindInt("charId", $character);
        $stm->bindStr("name", $object[0]);
        $stm->execute();
        if (($count_obj = $stm->fetchObject()) && ($object[1])) {

          $obj_id[] = 0;
          $obj_actual[] = $count_obj->count;


          if (($count_obj->count - $object[1] < 0) and (!$add_regardless)) {
            $accepted = false;
          }
          if (($count_obj->count - $object[1] < 0)) {
            $join_project = false;
          }

        } else { //no object found or required

          $obj_id[] = -1;
          $obj_actual[] = 0;

          if (!$add_regardless) {
            $accepted = false;
          }
          if ($object[1]) {
            $join_project = false;
          }
        }
      }
    }
  }

  if ($accepted) { // ok so lets add the items
    for ($i = 0; $i < count($obj_id); $i++) {

      switch ($obj_id[$i]) {
        case 0 : //object to be added

          $stm = $db->prepare("SELECT o.id FROM objects o,objecttypes t
            WHERE o.person= :charId AND t.id=o.type  AND t.name = :name ORDER by o.deterioration DESC");
          $stm->bindInt("charId", $character);
          $stm->bindStr("name", $obj_name[$i]);
          $stm->execute();

          $nrec = $stm->rowCount();
          $nrec = min($nrec, $obj_need[$i]);

          for ($j = 0; $j < $nrec; $j++) {
            $obj_info = $stm->fetch(PDO::FETCH_NUM);

            use_object($character, $obj_info[0], $project);

          }
          break;

        case -1 : //do nothing
          break;

        default : //raw to be added

          $forProject = new UseForProject(Character::loadById($character), Project::loadById($project));

          $objectId = $obj_id[$i];
          $amount = min($obj_need[$i], $obj_actual[$i]);
          try {
            $object = CObject::loadById($objectId);
            $forProject->useRaw($object, $amount);
          } catch (DisallowedActionException $e) {
            CError::throwRedirectTag("char.inventory", $e->getMessage());
          } catch (InvalidArgumentException $e) {
            CError::throwRedirectTag("char.inventory", "error_too_far_away");
          }
      }

    }

  }
  return $join_project;
}

function use_object($character, $object_id, $projectId)
{
  try {
    $char = Character::loadById($character);
    $object = CObject::loadById($object_id);
    $project = Project::loadById($projectId);
    if ($project === null) { // backward-compatible for old behaviour
      throw new InvalidArgumentException("");
    }
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("char.events", "error_too_far_away");
  }

  $inInventory = $object->getPerson() > 0;

  if (($project->getLocation() != $char->getLocation()) || ($char->getLocation() == 0)) {
    CError::throwRedirectTag("char.events", "error_project_not_same_location");
  }

  $sameLocation = $object->getLocation() == $char->getLocation();
  if (($inInventory && !$char->hasInInventory($object)) || (!$inInventory && !$sameLocation)) {
    CError::throwRedirectTag("char.events", "error_cannot_use_object_not_in_inventory");
  }

  $reqLeft = Parser::rulesToArray($project->getReqLeft());
  if (!array_key_exists("objects", $reqLeft)) {
    CError::throwRedirectTag("char.events", "error_object_not_needed");
  }

  $objLeft = Parser::rulesToArray($reqLeft["objects"], ",>");
  if (!array_key_exists($object->getName(), $objLeft)) {
    CError::throwRedirectTag("char.events", "error_object_not_needed");
  }

  $left = $objLeft[$object->getName()];
  if (!Validation::isPositiveInt($left)) {
    CError::throwRedirectTag("char.events", "error_needed_objects_already_used");
  }

  if ($object->isQuantity()) { // ONLY COINS
    if ($object->getPerson() > 0) {
      $successful = ObjectHandler::coinsToPerson($char->getId(), $object->getType(), $object->getSpecifics(), -1);
    } elseif ($object->getLocation() > 0) {
      $successful = ObjectHandler::coinsToLocation($object->getLocation(), $object->getType(), $object->getSpecifics(), -1);
    }
  } else {
    $successful = expire_object($object->getId());
  }

  if (!$successful) {
    Logger::getLogger("projectsetup")->error("sb trying to cheat on adding obj to project" .
      "[char: " . $GLOBALS['character'] . "][project: " . $project->getId() . "][obj: " . $object->getId() . "]");
    CError::throwRedirectTag("char.events", "error_needed_objects_already_used");
  }

  $objLeft[$object->getName()] -= 1;
  $reqLeft["objects"] = Parser::arrayToRules($objLeft, ",>");
  $project->setReqLeft(Parser::arrayToRules($reqLeft));

  $objName = urlencode($object->getName());
  $projName = urlencode($project->getName());
  Event::create(40, "ACTOR=" . $char->getId() . " OBJECT=$objName OBJECTID=" . $object->getId() .
    " PROJECT=$projName PROJID=" . $project->getId())->
  nearCharacter($char)->except($char)->show();
  Event::create(41, "OBJECT=$objName OBJECTID=" . $object->getId() .
    " PROJECT=$projName PROJID=" . $project->getId())->
  forCharacter($char)->show();
  $objectWeight = $object->getUnitWeight();
  $project->setWeight($project->getWeight() + $objectWeight);
  $project->saveInDb();
}

function automatic_join_project($projectId, $charId)
{
  $project = Project::loadById($projectId);
  $character = Character::loadById($charId);

  if (!$character->isInSameLocationAs($project)) {
    return "<CANTR REPLACE NAME=error_too_far_away>";
  }

  if ($character->isBusy()) {
    return "<CANTR REPLACE NAME=error_working_on_other_project>";
  }

  if ($project->getWayOfProgression() == ProjectConstants::PROGRESS_AUTOMATIC) {
    return "<CANTR REPLACE NAME=error_automatic_project>";
  }

  if ($project->getMaxParticipants() != ProjectConstants::PARTICIPANTS_NO_LIMIT
    && $project->getWorkersCount() >= $project->getMaxParticipants()
  ) {
    return "<CANTR REPLACE NAME=error_max_participants>";
  }

  // In case of digging projects, test whether not all slots are full
  if ($project->isUsingResourceSlots()) {
    $projectLocation = Location::loadById($project->getLocation());
    if ($projectLocation->getAllUsedDiggingSlots() >= $projectLocation->getDiggingSlots()) {
      return "<CANTR REPLACE NAME=error_max_digging_slots>";
    }
  }

  $reqLeft = Parser::rulesToArray($project->getReqLeft());
  if (array_key_exists("raws", $reqLeft)) {
    $rawsLeft = Parser::rulesToArray($reqLeft["raws"], ",>");
    foreach ($rawsLeft as $rawName => $amountLeft) {
      $rawName = TagBuilder::forTag(TagUtil::getRawTagByName($rawName))->build()->interpret();
      if ($amountLeft > 0) {
        return "<CANTR REPLACE NAME=error_use_material_first AMOUNT=" . urlencode($amountLeft)
          . " MATERIAL=" . urlencode($rawName) . ">";
      }
    }
  }

  if (array_key_exists("objects", $reqLeft)) {
    $objectsLeft = Parser::rulesToArray($reqLeft["objects"], ",>");
    foreach ($objectsLeft as $objectName => $numberLeft) {
      if ($numberLeft > 0) {
        return "<CANTR REPLACE NAME=error_use_object_first TIMES=" . urlencode($numberLeft)
          . " OBJECT=" . urlencode($objectName) . ">";
      }
    }
  }

  $character->setProject($project->getId());
  $character->saveInDb();
  return true;
}