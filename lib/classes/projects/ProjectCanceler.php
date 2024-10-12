<?php

include_once _LIB_LOC . "/func.finishproject.inc.php";
include_once _LIB_LOC . "/func.expireobject.inc.php";

class ProjectCanceler
{
  const NO_ACTOR = -1;

  private $character; // id of actor

  private $project_info;
  private $id;
  private $type;
  private $currentDay;
  private $projectAge;
  private $doneTurns, $neededTurns;
  private $workersCount;

  private $initiator;
  //because repairing and manufacturing project has this same type in game,
  //we need aditional conditions to recognize it
  private $repairProject;
  private $hasSomeResourcesAdded;
  //array in form { { rawname, raw_weight }, .. }
  private $usedRaws;
  //array in form { { objname, obj_count }, .. }
  private $usedObjects;
  private $location;
  private $isVehicle = false;
  /** @var bool */
  private $taintEnabled;

  /** @var Db */
  private $db;

  /**
   * @param int $projectId ID of project to cancel
   * @param int $character ID of character executing the cancellation or ProjectCanceler::NO_ACTOR when it's done without a character
   * @param Db $db database
   * @return ProjectCanceler|null instance of ProjectCanceler class with specified id or null if project doesn't exist
   */
  public static function FromId($projectId, $character, Db $db)
  {
    $character = ($character != self::NO_ACTOR) ? intval($character) : intval($GLOBALS['character']);

    $stm = $db->prepare("SELECT p.result like '%deterioration%' as repairType, p.* " .
      "FROM projects p WHERE id = :projectId LIMIT 1");
    $stm->bindInt("projectId", $projectId);
    $stm->execute();
    $project = $stm->fetchObject();
    if (!$project) {
      return null;
    }

    $stm = $db->prepare("SELECT count(*) FROM chars WHERE id != :actor
      AND project = :projectId AND status = :active");
    $stm->bindInt("actor", $character);
    $stm->bindInt("projectId", $projectId);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);

    $project->workersCount = $stm->executeScalar();

    $projectCanceler = new ProjectCanceler($project);
    $projectCanceler->character = $character;

    $stm = $db->prepare("SELECT type FROM locations WHERE id = :id");
    $stm->bindInt("id", $projectCanceler->location);
    $locationType = $stm->executeScalar();
    $projectCanceler->isVehicle = $locationType == LocationConstants::TYPE_VEHICLE;
    $projectCanceler->db = $db;

    $globalConfig = new GlobalConfig($db);
    $projectCanceler->taintEnabled = $globalConfig->isUniversalTaintEnabled();

    return $projectCanceler;
  }

  //return true, if project can be cancel, in another situation
  //it return info message, why project can't be cancel (messages using Cantr tags)
  public function canBeCancel($canceler)
  {

    $byInitiator = $canceler == $this->initiator;
    if ($this->isOnNonCancellableList()) {
      return "<CANTR REPLACE NAME=error_del_project_cannot>";
    }
    //initiator can cancel project when somobody working on it, others can't do it.
    if (!$byInitiator && $this->workersCount > 0) {
      return "<CANTR REPLACE NAME=error_del_project_busy>";
    }

    switch ($this->type) {
      case ProjectConstants::TYPE_GATHERING:
        return $this->canCancelGathering($byInitiator);
        break;
      case ProjectConstants::TYPE_MANUFACTURING:
      case ProjectConstants::TYPE_BUILDING:
      case ProjectConstants::TYPE_OLD_REPAIRING:
        if ($this->repairProject) {
          return $this->canCancelOldRepairing($byInitiator);
        }
        return $this->canCancelManufacturing($byInitiator);
        break;
      case ProjectConstants::TYPE_DISASSEMBLING:
        return $this->canCancelDisassembling($byInitiator);
        break;
      //tear down project and fixing damaged can be cancel almost always
      case ProjectConstants::TYPE_TEAR_DOWN:
      case ProjectConstants::TYPE_FIXING_DAMAGED:
        return true;
        break;
      case ProjectConstants::TYPE_PICKING_LOCK:
        return $this->canCancelLockpicking($byInitiator);
        break;
      case ProjectConstants::TYPE_DISASSEMBLING_OPEN_LOCK:
        return $this->canCancelDisassemblingLock($byInitiator);
        break;
      case ProjectConstants::TYPE_RESTING:
        return $this->canCancelResting($byInitiator);
        break;
      case ProjectConstants::TYPE_BURYING:
        return $this->canCancelBurying($byInitiator);
      case ProjectConstants::TYPE_ALTERING_SIGN:
        return $this->canCancelAlteringSign($byInitiator);
        break;
      case ProjectConstants::TYPE_HEAL_NEAR_DEATH:
        return $this->canCancelHealingNearDeath($byInitiator);
        break;
      case ProjectConstants::TYPE_DESC_BUILDING_CHANGE:
        return $this->canCancelBuildingDescriptionChange($byInitiator);
        break;
      case ProjectConstants::TYPE_TAMING_ANIMAL:
      case ProjectConstants::TYPE_HARVESTING_ANIMAL:
      case ProjectConstants::TYPE_ADOPTING_ANIMAL:
      case ProjectConstants::TYPE_BUTCHERING_ANIMAL:
      case ProjectConstants::TYPE_ADOPTING_STEED:
        return $this->canCancelAnimalProject($byInitiator);
        break;
      case ProjectConstants::TYPE_REPAIRING:
        return $this->canCancelRepairing($byInitiator);
      case ProjectConstants::TYPE_DESC_OBJECT_CHANGE:
        return $this->canCancelObjDescChange();
      case ProjectConstants::TYPE_DESTROYING_BUILDING:
        return $this->canCancelBuildingDestruction();
      case ProjectConstants::TYPE_FIXING_DESTROYED_BUILDING:
        return $this->canCancelFixingDestroyedBuilding();
      case ProjectConstants::TYPE_SADDLING_STEED:
        return $this->canCancelSaddling($byInitiator);
      case ProjectConstants::TYPE_UNSADDLING_STEED:
        return $this->canCancelUnsaddling($byInitiator);
      case ProjectConstants::TYPE_DISASSEMBLING_VEHICLE:
        return $this->canCancelVehicleDisassembling($byInitiator);
      case ProjectConstants::TYPE_BOOSTING_VEHICLE:
        return $this->canCancelBoostingVehicle();
      case ProjectConstants::TYPE_REPAIRING_ROAD:
        return $this->canCancelRoadRepair($byInitiator);
      case ProjectConstants::TYPE_DESTROYING_ROAD:
        return $this->canCancelRoadDestruction($byInitiator);
      default:
        return "<CANTR REPLACE NAME=error_del_project_cannot>";
    }
  }

  public function cancel()
  {
    switch ($this->type) {
      case ProjectConstants::TYPE_GATHERING:
        $this->cancelGathering();
        break;
      case ProjectConstants::TYPE_MANUFACTURING:
      case ProjectConstants::TYPE_BUILDING:
      case ProjectConstants::TYPE_OLD_REPAIRING:
        if ($this->repairProject) {
          $this->cancelOldRepairing();
          return;
        }
        $this->cancelManufacturing();
        break;
      case ProjectConstants::TYPE_DISASSEMBLING:
        $this->cancelDisassembling();
        break;
      //tear down project can be cancel almost always
      case ProjectConstants::TYPE_TEAR_DOWN:
        $this->cancelTearDown();
        break;
      case ProjectConstants::TYPE_FIXING_DAMAGED:
        $this->cancelFixingDamaged();
        break;
      case ProjectConstants::TYPE_PICKING_LOCK:
        $this->cancelLockpicking();
        break;
      case ProjectConstants::TYPE_DISASSEMBLING_OPEN_LOCK:
        $this->cancelDisassemblingLock();
        break;
      case ProjectConstants::TYPE_BURYING:
        $this->cancelBurying();
        break;
      case ProjectConstants::TYPE_RESTING:
        $this->cancelResting();
        break;
      case ProjectConstants::TYPE_ALTERING_SIGN:
        $this->cancelAlteringSign();
        break;
      case ProjectConstants::TYPE_HEAL_NEAR_DEATH:
        $this->cancelHealingNearDeath();
        break;
      case ProjectConstants::TYPE_DESC_BUILDING_CHANGE:
        $this->cancelBuildingDescriptionChange();
        break;
      case ProjectConstants::TYPE_TAMING_ANIMAL:
      case ProjectConstants::TYPE_HARVESTING_ANIMAL:
      case ProjectConstants::TYPE_ADOPTING_ANIMAL:
      case ProjectConstants::TYPE_BUTCHERING_ANIMAL:
      case ProjectConstants::TYPE_ADOPTING_STEED:
        $this->cancelAnimalProject();
        break;
      case ProjectConstants::TYPE_REPAIRING:
        $this->cancelRepairing();
        break;
      case ProjectConstants::TYPE_DESC_OBJECT_CHANGE:
        $this->cancelObjDescChange();
        break;
      case ProjectConstants::TYPE_DESTROYING_BUILDING:
        $this->cancelDestroyingBuilding();
        break;
      case ProjectConstants::TYPE_FIXING_DESTROYED_BUILDING:
        $this->cancelFixingDestroyedBuilding();
        break;
      case ProjectConstants::TYPE_SADDLING_STEED:
        $this->cancelSaddling();
        break;
      case ProjectConstants::TYPE_UNSADDLING_STEED:
        $this->cancelUnsaddling();
        break;
      case ProjectConstants::TYPE_DISASSEMBLING_VEHICLE:
        $this->cancelVehicleDisassembling();
        break;
      case ProjectConstants::TYPE_BOOSTING_VEHICLE:
        $this->cancelBoostingVehicle();
        break;
      case ProjectConstants::TYPE_REPAIRING_ROAD:
        $this->cancelRoadRepair();
        break;
      case ProjectConstants::TYPE_DESTROYING_ROAD:
        $this->cancelRoadDestruction();
        break;
    }
  }


  private function __construct($project)
  {

    $currentDate = GameDate::NOW();

    $this->id = $project->id;
    $this->type = $project->type;
    $this->initiator = $project->initiator;
    $this->repairProject = $project->repairType;
    $this->workersCount = $project->workersCount;
    $this->location = $project->location;

    $currentTurn = $currentDate->getDay() * GameDateConstants::HOURS_PER_DAY + $currentDate->getHour();
    $initTurn = $project->init_day * GameDateConstants::HOURS_PER_DAY + $project->init_turn;
    $this->currentDay = $currentDate->getDay();
    $this->projectAge = ($currentTurn - $initTurn);

    $this->doneTurns = ($project->turnsneeded - $project->turnsleft) / 100;
    $this->neededTurns = $project->turnsneeded / 100;

    $this->usedRaws = $this->loadUsed('raws', $project->reqneeded, $project->reqleft);
    $this->usedObjects = $this->loadUsed('objects', $project->reqneeded, $project->reqleft);

    $this->hasSomeResourcesAdded = $project->reqneeded != $project->reqleft;
    $this->project_info = $project;

    $this->logger = Logger::getLogger(__CLASS__);
  }

  private function loadUsed($usedTypeName, $reqneeded, $reqleft)
  {
    $reqneeded = $this->arrayFromRules($reqneeded, $usedTypeName);
    $reqleft = $this->arrayFromRules($reqleft, $usedTypeName);
    $data = [];
    foreach ($reqneeded as $name => $value) {
      if (!array_key_exists($name, $reqleft)) {
        continue;
      }
      $data[$name] = $value - $reqleft[$name];
    }
    return $data;
  }

  private function arrayFromRules($rulesString, $ruleArrayName)
  {
    $used = [];
    foreach (explode(';', $rulesString) as $info) {
      list($_n, $data) = explode(':', $info);
      if ($_n == $ruleArrayName) {
        foreach (explode(',', $data) as $singleInfo) {
          list($name, $value) = explode('>', $singleInfo);
          $used[$name] = $value;
        }
      }
    }
    return $used;
  }

  /**
   * Checking whether this project in a list of non-cancellable project types (like manufaturing keys).
   * @return bool true when this project can never be cancelled
   */
  private function isOnNonCancellableList()
  {
    $used = array_keys($this->usedObjects);

    $usedCoins = [];
    foreach ($used as $value) {
      if (strrpos($value, 'coin') === strlen($value) - strlen('coin')) {
        $usedCoins [] = $value;
      }
    }

    $coinsSum = 0;
    foreach ($usedCoins as $key)
      $coinsSum += $this->usedObjects[$key];

    $hasUsedKey = array_key_exists('key', $this->usedObjects) && $this->usedObjects['key'] > 0;
    $hasUsedCoint = $coinsSum > 0;
    return $hasUsedKey || $hasUsedCoint;
  }

  private function canCancelGathering($byInitiator)
  {
    if ($byInitiator) {
      $canBeCancel = true;
    } else {
      $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_GATHERING_OTHERS : ProjectConstants::LIMIT_GATHERING_OTHERS;
      $canBeCancel = $this->projectAge >= ($cancelLimit + $this->doneTurns);
    }
    if ($canBeCancel) {
      return true;
    }
    return "<CANTR REPLACE NAME=error_del_project_too_new>";
  }

  private function canCancelManufacturing($byInitiator)
  {
    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_MANUFACTURING_OTHERS : ProjectConstants::LIMIT_MANUFACTURING_OTHERS;
    $canBeCancel = $byInitiator || ($this->projectAge >= ($cancelLimit + $this->doneTurns));
    if ($canBeCancel) {
      return true;
    }
    return "<CANTR REPLACE NAME=error_del_project_too_new>";
  }

  private function canCancelOldRepairing($byInitiator)
  {
    // it will never be possible
    return "<CANTR REPLACE NAME=error_del_project_cannot>";
  }

  private function canCancelDisassembling($byInitiator)
  {
    $canBeCancel = ($this->workersCount == 0);
    if (!$canBeCancel) {
      return "<CANTR REPLACE NAME=error_del_project_busy>";
    }

    return true;
  }

  private function canCancelLockpicking($byInitiator)
  {
    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_PICKING_LOCKS_OTHERS : ProjectConstants::LIMIT_PICKING_LOCKS_OTHERS;
    $canBeCancel = ($this->projectAge >= $cancelLimit);
    if (!$canBeCancel) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    return true;
  }

  private function canCancelDisassemblingLock($byInitiator)
  {
    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_DISASSEMBLING_LOCKS_OTHERS : ProjectConstants::LIMIT_DISASSEMBLING_LOCKS_OTHERS;
    $canBeCancel = ($this->projectAge >= $cancelLimit);
    if (!$canBeCancel) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    return true;
  }

  private function canCancelResting($byInitiator)
  {
    $canBeCancel = ($this->workersCount == 0);
    if (!$canBeCancel) {
      return "<CANTR REPLACE NAME=error_del_project_busy>";
    }

    return true;
  }

  private function canCancelBurying($byInitiator)
  {
    if ($byInitiator) {
      return true;
    }

    if ($this->workersCount > 0) {
      return "<CANTR REPLACE NAME=error_del_project_busy>";
    }

    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_BURYING_OTHERS : ProjectConstants::LIMIT_BURYING_OTHERS;
    if ($this->projectAge < $cancelLimit) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }
    return true;
  }

  private function canCancelAlteringSign($byInitiator)
  {
    if ($byInitiator) {
      return true;
    }

    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_ALTERING_SIGN_OTHERS : ProjectConstants::LIMIT_ALTERING_SIGN_OTHERS;
    $canBeCancel = ($this->projectAge >= $cancelLimit);
    if (!$canBeCancel) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    return true;
  }

  private function canCancelHealingNearDeath($byInitiator)
  {
    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_HEALING_NEAR_DEATH : ProjectConstants::LIMIT_HEALING_NEAR_DEATH;
    $canBeCancel = ($this->projectAge >= ($cancelLimit));
    if (!$canBeCancel) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    return true;
  }

  private function canCancelBuildingDescriptionChange($byInitiator)
  {
    if ($byInitiator) {
      return true;
    }

    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_DESC_BUILDING_CHANGE : ProjectConstants::LIMIT_DESC_BUILDING_CHANGE;
    $canBeCancelled = ($this->projectAge >= ($cancelLimit));
    if (!$canBeCancelled) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    return true;
  }

  private function canCancelAnimalProject($byInitiator)
  {
    if ($byInitiator) {
      return true;
    }

    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_ANIMAL_OTHERS : ProjectConstants::LIMIT_ANIMAL_OTHERS;
    $canBeCancelled = ($this->projectAge >= ($cancelLimit));
    if (!$canBeCancelled) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    return true;
  }

  private function canCancelRepairing($byInitiator)
  {
    if ($byInitiator) {
      return true;
    }

    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_REPAIRING_OTHERS : ProjectConstants::LIMIT_REPAIRING_OTHERS;
    $canBeCancelled = ($this->projectAge >= ($cancelLimit));
    if (!$canBeCancelled) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    return true;
  }

  private function canCancelObjDescChange()
  {
    if ($this->workersCount == 0) {
      return true;
    }
    return "<CANTR REPLACE NAME=error_del_project_busy>";
  }

  private function canCancelBuildingDestruction()
  {
    if ($this->workersCount == 0) {
      return true;
    }
    return "<CANTR REPLACE NAME=error_del_project_busy>";
  }

  private function canCancelFixingDestroyedBuilding()
  {
    if ($this->workersCount == 0) {
      return true;
    }
    return "<CANTR REPLACE NAME=error_del_project_busy>";
  }

  private function canCancelSaddling($byInitiator)
  {
    if ($byInitiator) {
      return true;
    }
    if ($this->workersCount > 0) {
      return "<CANTR REPLACE NAME=error_del_project_busy>";
    }

    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_SADDLING_OTHERS : ProjectConstants::LIMIT_SADDLING_OTHERS;
    $canBeCancelled = ($this->projectAge >= $cancelLimit + $this->doneTurns);
    if (!$canBeCancelled) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    return true;
  }

  private function canCancelUnsaddling($byInitiator)
  {
    if ($byInitiator) {
      return true;
    }
    if ($this->workersCount > 0) {
      return "<CANTR REPLACE NAME=error_del_project_busy>";
    }

    $cancelLimit = $this->isVehicle ? ProjectConstants::LIMIT_VEHICLE_SADDLING_OTHERS : ProjectConstants::LIMIT_SADDLING_OTHERS;
    $canBeCancelled = ($this->projectAge >= $cancelLimit + $this->doneTurns);
    if (!$canBeCancelled) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    return true;
  }

  private function canCancelVehicleDisassembling($byInitiator)
  {
    if ($byInitiator) {
      return true;
    }
    $cancelLimit = ProjectConstants::LIMIT_DISASSEMBLING_VEHICLE_OTHERS;
    $canBeCancelled = ($this->projectAge >= $cancelLimit + $this->doneTurns);
    if (!$canBeCancelled) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }

    if ($this->workersCount > 0) {
      return "<CANTR REPLACE NAME=error_del_project_busy>";
    }

    return true;
  }

  private function canCancelBoostingVehicle()
  {
    $canBeCancel = ($this->workersCount == 0);
    if (!$canBeCancel) {
      return "<CANTR REPLACE NAME=error_del_project_busy>";
    }

    return true;
  }

  private function canCancelRoadRepair($byInitiator)
  {
    $canBeCanceled = ($this->workersCount == 0);
    if (!$canBeCanceled) {
      return "<CANTR REPLACE NAME=error_del_project_busy>";
    }

    $cancelLimit = ProjectConstants::LIMIT_ROAD_REPAIRING_OTHERS;
    if (!$byInitiator && !$cancelLimit) {
      return "<CANTR REPLACE NAME=error_del_project_too_new>";
    }
    return true;
  }

  private function canCancelRoadDestruction($byInitiator)
  {
    {
      $canBeCanceled = ($this->workersCount == 0);
      if (!$canBeCanceled) {
        return "<CANTR REPLACE NAME=error_del_project_busy>";
      }

      $cancelLimit = ProjectConstants::LIMIT_ROAD_DESTRUCTION_OTHERS;
      if (!$cancelLimit) {
        return "<CANTR REPLACE NAME=error_del_project_too_new>";
      }
      return true;
    }
  }


  private function cancelGathering()
  {
    //Cancellable immediately and yields completed % +/- 20% of the project resources (for the current repetition).
    $percent = $this->doneTurns / $this->neededTurns;
    $rInfo = $this->returnUsedResources(1 - $percent, $this->taintEnabled);
    $taintedInfo = $this->getAmountsOfTaintedRaws(1 - $percent);

    srand((float)microtime() * 1000000);

    list($rawId, $rawWeight) = explode(':', $this->project_info->result);
    $rawWeight = floor($percent * $rawWeight);
    //if this is gathering, not processing project..
    if (array_sum($this->usedRaws) == 0) {
      $rawWeight = rand(0.8 * $rawWeight, 1.2 * $rawWeight);
    }

    if ($rawWeight > 0) {
      create_raws($this->initiator, $rawId, $rawWeight, $this->location);
      $unique_raw_name = ObjectHandler::getRawNameFromId($rawId);
      $unique_raw_name = str_replace(' ', '_', $unique_raw_name);
      $rInfo[] = "$rawWeight <CANTR REPLACE NAME=grams_of> <CANTR REPLACE NAME=raw_$unique_raw_name>";
      $processingStats = new Statistic("raws_processing", Db::get());
      $processingStats->store($rawId . ";" . implode(",", array_keys($this->usedRaws)),
        $this->project_info->initiator, $rawWeight);
    }
    $this->throwPrivateInstantCancelEvent();
    if ($this->initiator != $this->character) {
      $this->throwPublicInstantCancelEvent();
    }
    $this->throwPrivateRecoverEvent($rInfo, $taintedInfo);

    $this->deleteThisProject();
  }

  private function cancelManufacturing()
  {
    if ($this->deleteThisProject()) {
      if ($this->doneTurns == 0) {
        //simply return full of added resources
        $rInfo = $this->returnUsedResources(1.0, $this->taintEnabled);
        $taintedInfo = $this->getAmountsOfTaintedRaws(1.0);
        $this->throwPrivateInstantCancelEvent();
        if ($this->initiator != $this->character) {
          $this->throwPublicInstantCancelEvent();
        }
        $this->throwPrivateRecoverEvent($rInfo, $taintedInfo);
      } else {
        $columnValuePairs = $this->prepareTearDownProjectArray();
        $columnValuePairs = $this->escapeArrayValues($columnValuePairs);
        $columns = implode("`, `", array_keys($columnValuePairs));
        $values = implode(", ", $columnValuePairs);

        $this->db->query("INSERT INTO projects (`$columns`) VALUES ($values)"); // todo far from being perfect
        $stm = $this->db->prepare("UPDATE chars SET project = LAST_INSERT_ID() WHERE id = :id LIMIT 1");
        $stm->bindInt("id", $this->character);
        $stm->execute();

        if ($this->initiator != $this->character) {
          $this->throwPublicBeginCancelEvent();
        }
        $this->throwPrivateBeginCancelEvent();
      }
    }
  }

  private function cancelOldRepairing()
  {
    // unused
  }

  private function cancelDisassembling()
  {
    if ($this->doneTurns <= ProjectConstants::LIMIT_INSTANT_DISASSEMBLING) {
      //instant canceling project
      $this->throwPrivateInstantCancelEvent();
      $this->throwPublicInstantCancelEvent();
    } else {
      //make manufacturing project
      $machineId = $this->project_info->subtype;
      //we need delete old machine
      $newProjectInfo = $this->prepareReverseDisassemblingProjectArray();
      $newProjectInfo = $this->escapeArrayValues($newProjectInfo);
      $columns = implode("`, `", array_keys($newProjectInfo));
      $values = implode(", ", $newProjectInfo);

      $this->throwPrivateBeginCancelEvent();
      $this->throwPublicBeginCancelEvent();

      $this->db->query("INSERT INTO projects (`$columns`) VALUES ($values)"); // todo far from being perfect
      $stm = $this->db->prepare("UPDATE chars SET project = LAST_INSERT_ID() WHERE id = :id LIMIT 1");
      $stm->bindInt("id", $this->character);
      $stm->execute();

      // mark machine as "in use". When this project is active, machine shouldn't be usable
      $stm = $this->db->prepare("UPDATE objects SET specifics = LAST_INSERT_ID() WHERE id = :id");
      $stm->bindInt("id", $machineId);
      $stm->execute();
    }
    $this->deleteThisProject();
  }

  private function cancelTearDown()
  {
    $columnValuePairs = $this->revertTearDownProjectArray();
    $columnValuePairs = $this->escapeArrayValues($columnValuePairs);

    $columns = implode("`, `", array_keys($columnValuePairs));
    $values = implode(", ", $columnValuePairs);

    $this->db->query("INSERT INTO projects (`$columns`) VALUES ($values)"); // todo far from being perfect
    $stm = $this->db->prepare("UPDATE chars SET project = LAST_INSERT_ID() WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $this->character);
    $stm->execute();

    $this->deleteThisProject();
  }

  private function cancelFixingDamaged()
  {

    $columnValuePairs = $this->revertReverseDisassemblingProjectArray();
    $columnValuePairs = $this->escapeArrayValues($columnValuePairs);
    $columns = implode("`, `", array_keys($columnValuePairs));
    $values = implode(", ", $columnValuePairs);

    $machineId = $this->project_info->subtype;

    $this->throwPrivateDisassemblingEvent();
    $this->throwPublicDisassemblingEvent();

    $this->db->query("INSERT INTO projects (`$columns`) VALUES ($values)"); // todo far from being perfect
    $stm = $this->db->prepare("UPDATE chars SET project = LAST_INSERT_ID() WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $this->character);
    $stm->execute();

    // mark machine as "in use". When this project is active, machine shouldn't be usable
    $stm = $this->db->prepare("UPDATE objects SET specifics = LAST_INSERT_ID() WHERE id = :id");
    $stm->bindInt("id", $machineId);
    $stm->execute();

    $this->deleteThisProject();
  }

  private function cancelLockpicking()
  {
    $this->throwPrivateInstantCancelEvent();
    $this->throwPublicInstantCancelEvent();

    $this->deleteThisProject();
  }

  private function cancelDisassemblingLock()
  {
    $this->throwPrivateInstantCancelEvent();
    $this->throwPublicInstantCancelEvent();

    $this->deleteThisProject();
  }

  private function cancelResting()
  {
    $this->deleteThisProject();
  }

  private function cancelBurying()
  {
    $this->throwPrivateInstantCancelEvent();
    if ($this->initiator != $this->character) {
      $this->throwPublicInstantCancelEvent();
    }
    $this->deleteThisProject();
  }

  private function cancelAlteringSign()
  {
    $this->throwPrivateInstantCancelEvent();
    if ($this->initiator != $this->character) {
      $this->throwPublicInstantCancelEvent();
    }
    $this->deleteThisProject();
  }

  private function cancelHealingNearDeath()
  {
    $this->throwPublicInstantCancelEvent();

    $this->deleteThisProject();
  }

  private function cancelBuildingDescriptionChange()
  {
    $this->throwPrivateInstantCancelEvent();
    if ($this->initiator != $this->character) {
      $this->throwPublicInstantCancelEvent();
    }

    $this->deleteThisProject();
  }

  private function cancelAnimalProject()
  {
    if ($this->deleteThisProject()) {
      $this->throwPrivateInstantCancelEvent();
      if ($this->initiator != $this->character) {
        $this->throwPublicInstantCancelEvent();
      }

      $percent = $this->doneTurns / $this->neededTurns;
      $rInfo = $this->returnUsedResources(1 - $percent);
      $this->throwPrivateRecoverEvent($rInfo);
    }
  }

  private function cancelRepairing()
  {
    $this->throwPrivateInstantCancelEvent();
    if ($this->initiator != $this->character) {
      $this->throwPublicInstantCancelEvent();
    }

    $repairedObjectId = $this->project_info->subtype;
    if (ObjectHandler::isObjectInLocation($repairedObjectId, $this->location)) {
      $stm = $this->db->prepare("SELECT o.deterioration, ot.repair_rate FROM objects o
        INNER JOIN objecttypes ot ON ot.id = o.type WHERE o.id = :objectId");
      $stm->bindInt("objectId", $repairedObjectId);
      $stm->execute();

      $objectInfo = $stm->fetchObject();
      $deterReduction = $this->doneTurns * $objectInfo->repair_rate;

      $newDeterioration = (float)max(0, $objectInfo->deterioration - $deterReduction);
      $stm = $this->db->prepare("UPDATE objects SET deterioration = :deterioration WHERE id = :objectId");
      $stm->bindFloat("deterioration", $newDeterioration);
      $stm->bindInt("objectId", $repairedObjectId);
      $stm->execute();
    }
    $this->deleteThisProject();
  }

  private function cancelObjDescChange()
  {
    $this->throwPrivateInstantCancelEvent();
    if ($this->initiator != $this->character) {
      $this->throwPublicInstantCancelEvent();
    }

    $this->deleteThisProject();
  }

  private function cancelDestroyingBuilding()
  {

    $this->throwPrivateInstantCancelEvent();
    $this->throwPublicInstantCancelEvent();

    if ($this->doneTurns > 0) {
      $newProjectInfo = $this->prepareFixingDestroyedBuildingArray();
      $newProjectInfo = $this->escapeArrayValues($newProjectInfo);
      $columns = implode("`, `", array_keys($newProjectInfo));
      $values = implode(", ", $newProjectInfo);

      $this->db->query("INSERT INTO projects (`$columns`) VALUES ($values)"); // todo far from being perfect
      $stm = $this->db->prepare("UPDATE chars SET project = LAST_INSERT_ID() WHERE id = :id LIMIT 1");
      $stm->bindInt("id", $this->character);
      $stm->execute();
    }
    $this->deleteThisProject();
  }

  private function cancelFixingDestroyedBuilding()
  {
    $this->throwPrivateInstantCancelEvent();
    $this->throwPublicInstantCancelEvent();

    $newProjectInfo = $this->prepareDestroyingBuildingArray();
    $newProjectInfo = $this->escapeArrayValues($newProjectInfo);
    $columns = implode("`, `", array_keys($newProjectInfo));
    $values = implode(", ", $newProjectInfo);

    $this->db->query("INSERT INTO projects (`$columns`) VALUES ($values)"); // todo far from being perfect
    $stm = $this->db->prepare("UPDATE chars SET project = LAST_INSERT_ID() WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $this->character);
    $stm->execute();

    $this->deleteThisProject();
  }

  private function cancelSaddling()
  {
    if ($this->deleteThisProject()) {
      $this->throwPrivateInstantCancelEvent();
      $this->throwPublicInstantCancelEvent();

      $this->returnUsedResources(1.0); // return saddle
    }
  }

  private function cancelUnsaddling()
  {
    $this->throwPrivateInstantCancelEvent();
    $this->throwPublicInstantCancelEvent();

    $this->deleteThisProject();
  }

  private function cancelVehicleDisassembling()
  {
    $this->throwPrivateInstantCancelEvent();
    $this->throwPublicInstantCancelEvent();

    $this->deleteThisProject();
  }

  private function cancelBoostingVehicle()
  {
    $this->throwPrivateInstantCancelEvent();

    $this->deleteThisProject();
  }

  private function cancelRoadRepair()
  {
    if ($this->deleteThisProject()) {
      $this->throwPrivateInstantCancelEvent();
      $this->throwPublicInstantCancelEvent();

      list($connectionId, $repairedTypeId) = explode(":", $this->project_info->result);
      try {
        $connection = Connection::loadById($connectionId);
        $type = ConnectionType::loadById($repairedTypeId);

        // check how many turns (hours) are needed to repair 10000 of deterioration
        $turnsToRepair = $connection->getDaysToImproveTo($type) * ConnectionConstants::REPAIR_TO_IMPROVEMENT_TIME * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
        $repairedRatio = $this->doneTurns / $turnsToRepair;

        // we know how much of road was already repaired
        $part = $connection->getConnectionPartWithType($type);
        $part->setDeterioration($part->getDeterioration() - $repairedRatio * 10000);

        $this->returnUsedResources(1.0 - $repairedRatio); // return resources that were not yet used

        $connection->saveInDb();
      } catch (InvalidArgumentException $e) {
        $this->logger->warn("No valid type '$repairedTypeId' for a result string: " . $this->project_info->result);
      }
    }
  }

  /**
   * Destruction project must be finished to make any effect.
   */
  private function cancelRoadDestruction()
  {
    $this->throwPrivateInstantCancelEvent();
    $this->throwPublicInstantCancelEvent();
    $this->deleteThisProject();
  }


  public function deleteThisProject()
  {
    $stm = $this->db->prepare("DELETE FROM projects WHERE id = :id");
    $stm->bindInt("id", $this->id);
    $stm->execute();
    if ($stm->rowCount() == 0) {
      return false;
    }
    $stm = $this->db->prepare("UPDATE chars SET project = 0 WHERE project = :id");
    $stm->bindInt("id", $this->id);
    $stm->execute();

    return true;
  }

  /**
   * Give percentage of used resources to player,
   * @param $percent
   * @param bool $includeTaint if true then the percent of returned raws is further affected by taint
   * @return String[] list of strings representing amounts of recovered resources
   */
  public function returnUsedResources($percent, $includeTaint = false)
  {
    $eventParams = [];
    //now this same with object that was used
    foreach ($this->usedObjects as $objName => $count) {
      $objUniqueName = str_replace(' ', '_', $objName);
      if ($count > 0) {
        for ($i = 0; $i < $count; $i++) {
          $stm = $this->db->prepare("SELECT id, build_result FROM objecttypes WHERE unique_name LIKE :uniqueName");
          $stm->bindStr("uniqueName", $objUniqueName);
          $stm->execute();
          $objType = $stm->fetchObject();

          if (!$objType->id) {
            $stm = $this->db->prepare("SELECT id, build_result FROM objecttypes WHERE name LIKE :name LIMIT 1");
            $stm->bindStr("name", $objName);
            $stm->execute();
            $objType = $stm->fetchObject();
          }

          //because ">var>typeid" is normal applied when project is creating.
          $buildResult = str_replace('>var>typeid', ">$objType->id", $objType->build_result);
          $new_object_id = create_object($this->initiator, $objType->id, $buildResult, $this->location);
        }
        if ($new_object_id > 0) {
          $mess = "<CANTR OBJNAME ID=$new_object_id>";
          if ($count > 1) {
            $mess = "$count" . "x $mess";
          }
          $eventParams [] = $mess;
        }
      }
    }

    foreach ($this->usedRaws as $rawName => $rawWeight) {
      $weightToRestore = floor($rawWeight * $percent);
      if ($includeTaint) {
        $weightToRestore -= $this->totalAmountTainted($weightToRestore, $rawName);
      }
      if ($weightToRestore >= 1) {
        $id = ObjectHandler::getRawIdFromName($rawName);
        create_raws($this->initiator, $id, $weightToRestore, $this->location);
        $unique_raw_name = str_replace(' ', '_', $rawName);
        $eventParams[] = "$weightToRestore <CANTR REPLACE NAME=grams_of> <CANTR REPLACE NAME=raw_$unique_raw_name>";
      }
    }

    return $eventParams;
  }

  public function getAmountsOfTaintedRaws($percent)
  {
    if (!$this->taintEnabled) {
      return [];
    }
    $eventParams = [];
    foreach ($this->usedRaws as $rawName => $rawWeight) {
      $weightToRestore = floor($rawWeight * $percent);
      $amountTainted = $this->totalAmountTainted($weightToRestore, $rawName);
      if ($amountTainted >= 1) {
        $unique_raw_name = str_replace(' ', '_', $rawName);
        $eventParams[] = "$amountTainted <CANTR REPLACE NAME=grams_of> <CANTR REPLACE NAME=raw_$unique_raw_name>";
      }
    }
    return $eventParams;
  }

  private function totalAmountTainted($weight, $rawName)
  {
    $projectAgeInDays = $this->projectAge / GameDateConstants::HOURS_PER_DAY;
    $daysSinceIntroductionOfTaint = $this->currentDay - DeteriorationConstants::TAINT_INTRODUCTION_DAY;
    $ageInDaysOrSinceIntroductionOfTaint = min($projectAgeInDays, $daysSinceIntroductionOfTaint);

    if ($ageInDaysOrSinceIntroductionOfTaint <= 3) { // grace period
      return 0;
    }
    return DeteriorationManager::accumulatedAmountToTaint($weight, $rawName,
      intval($ageInDaysOrSinceIntroductionOfTaint), $this->db);
  }


  private function prepareTearDownProjectArray()
  {
    $pInfo = $this->project_info;
    $projectPrefix = "<CANTR REPLACE NAME=project_tear_down> ";
    $columnValuePairs = [
      "location" => $pInfo->location,
      "name" => $projectPrefix . $pInfo->name,
      "type" => ProjectConstants::TYPE_TEAR_DOWN,
      "subtype" => $pInfo->subtype,
      "turnsleft" => $this->doneTurns * 100,
      "turnsneeded" => $this->neededTurns * 100,
      "result" => $pInfo->result,
      "reqneeded" => $pInfo->reqneeded,
      "reqleft" => $pInfo->reqleft,
      "initiator" => $this->character,
      "init_day" => $pInfo->init_day,
      "init_turn" => $pInfo->init_turn,
      "skill" => $pInfo->skill,
      "result_description" => $pInfo->result_description,
    ];
    return $columnValuePairs;
  }

  private function revertTearDownProjectArray()
  {
    $pInfo = $this->project_info;

    // backward compatibility
    if ((strpos($pInfo->result, "raws") === 0) && (strpos($pInfo->result, "objects:") !== false)) {

      $stm = $this->db->prepare("SELECT build_result FROM objecttypes WHERE id = :id");
      $stm->bindInt("id", $pInfo->subtype);
      $result = $stm->executeScalar();
      $result = str_replace('>var>typeid', ">$pInfo->subtype", $result);
      $projectPrefix = "Tear down: ";
    } else {
      $result = $pInfo->result;
      $projectPrefix = "<CANTR REPLACE NAME=project_tear_down> ";
    }

    $projectName = substr($pInfo->name, strlen($projectPrefix));
    $columnValuePairs = [
      "location" => $pInfo->location,
      "name" => $projectName,
      "type" => ProjectConstants::TYPE_MANUFACTURING,
      "subtype" => $pInfo->subtype,
      "turnsleft" => $this->doneTurns * 100,
      "turnsneeded" => $this->neededTurns * 100,
      "result" => $result,
      "reqneeded" => $pInfo->reqneeded,
      "reqleft" => $pInfo->reqleft,
      "initiator" => $this->character,
      "init_day" => $pInfo->init_day,
      "init_turn" => $pInfo->init_turn,
      "skill" => $pInfo->skill,
      "result_description" => $pInfo->result_description,
    ];
    return $columnValuePairs;
  }

  private function prepareReverseDisassemblingProjectArray()
  {

    $currentDate = GameDate::NOW();

    $machineId = $this->project_info->subtype;
    $stm = $this->db->prepare("SELECT ot.*
      FROM objects o
      LEFT JOIN objecttypes ot ON o.type = ot.id
      WHERE o.id = :objectId LIMIT 1");
    $stm->bindInt("objectId", $machineId);
    $stm->execute();
    $objectType = $stm->fetchObject();
    $objectType->build_result = str_replace('>var>typeid', ">$objectType->id", $objectType->build_result);

    $buildReq = Parser::rulesToArray($objectType->build_requirements);
    $req = "";
    if (isset($buildReq['tools'])) {
      $req = "tools:" . $buildReq['tools'];
    }

    $pInfo = $this->project_info;
    $columnValuePairs = [
      "location" => $pInfo->location,
      "name" => $objectType->build_description,
      "type" => ProjectConstants::TYPE_FIXING_DAMAGED,
      "subtype" => $machineId,
      "turnsleft" => $this->doneTurns * 100,
      "turnsneeded" => $this->neededTurns * 100,
      "reqneeded" => $req,
      "reqleft" => $req,
      "initiator" => $this->character,
      "init_day" => $currentDate->getDay(),
      "init_turn" => $currentDate->getHour(),
      "skill" => $objectType->skill,
    ];
    return $columnValuePairs;
  }

  private function revertReverseDisassemblingProjectArray()
  {

    $currentDate = GameDate::NOW();

    $machineId = $this->project_info->subtype;
    $stm = $this->db->prepare("SELECT ot.*
      FROM objects o
      LEFT JOIN objecttypes ot ON o.type = ot.id
      WHERE o.id = :objectId LIMIT 1");
    $stm->bindInt("objectId", $machineId);
    $stm->execute();
    $objectType = $stm->fetchObject();
    $objectType->build_result = str_replace('>var>typeid', ">$objectType->id", $objectType->build_result);

    $reqLeft = "";
    if (preg_match("/tools:[^;]*/", $objectType->build_requirements, $match)) {
      $reqLeft = $match[0] . ";";
    }
    $reqLeft .= "objectid:{$this->project_info->subtype}";

    $pName = "<CANTR REPLACE NAME=action_recycle_1> <CANTR REPLACE NAME=item_" . $objectType->unique_name . "_o>";
    $pInfo = $this->project_info;
    $columnValuePairs = [
      "location" => $pInfo->location,
      "name" => $pName,
      "type" => ProjectConstants::TYPE_DISASSEMBLING,
      "subtype" => $pInfo->subtype,
      "turnsleft" => $this->doneTurns * 100,
      "turnsneeded" => $this->neededTurns * 100,
      "reqneeded" => $reqLeft,
      "reqleft" => $reqLeft,
      "initiator" => $this->character,
      "init_day" => $currentDate->getDay(),
      "init_turn" => $currentDate->getHour(),
      "skill" => $objectType->skill,
    ];
    return $columnValuePairs;
  }

  private function prepareDestroyingBuildingArray()
  {

    $currentDate = GameDate::NOW();

    $locationId = $this->project_info->subtype;
    $stm = $this->db->prepare("SELECT ot.build_requirements, ot.unique_name, loc.name FROM locations loc
        INNER JOIN objecttypes ot ON ot.id = loc.area WHERE loc.id = :locationId");
    $stm->bindInt("locationId", $locationId);
    $stm->execute();
    $locInfo = $stm->fetchObject();
    $buildReq = Parser::rulesToArray($locInfo->build_requirements);
    $req = "";
    if (isset($buildReq['tools'])) {
      $req = "tools:" . $buildReq['tools'];
    }

    $pInfo = $this->project_info;
    $columnValuePairs = [
      "location" => $pInfo->location,
      "name" => "<CANTR REPLACE NAME=project_destroying_building> " .
        "<CANTR REPLACE NAME=item_{$locInfo->unique_name}_b> \"$locInfo->name\"",
      "type" => ProjectConstants::TYPE_DESTROYING_BUILDING,
      "subtype" => $locationId,
      "turnsleft" => $this->doneTurns * 100,
      "turnsneeded" => $this->neededTurns * 100,
      "reqneeded" => $req,
      "reqleft" => $req,
      "initiator" => $this->character,
      "init_day" => $currentDate->getDay(),
      "init_turn" => $currentDate->getHour(),
      "skill" => 0,
    ];
    return $columnValuePairs;
  }

  private function prepareFixingDestroyedBuildingArray()
  {

    $currentDate = GameDate::NOW();

    $locationId = $this->project_info->subtype;

    $stm = $this->db->prepare("SELECT ot.unique_name, loc.name FROM locations loc
        INNER JOIN objecttypes ot ON ot.id = loc.area WHERE loc.id = :locationId");
    $stm->bindInt("locationId", $locationId);
    $stm->execute();
    $locInfo = $stm->fetchObject();

    $req = "";

    $pInfo = $this->project_info;
    $columnValuePairs = [
      "location" => $pInfo->location,
      "name" => "<CANTR REPLACE NAME=project_fixing_destroyed_building> " .
        "<CANTR REPLACE NAME=item_{$locInfo->unique_name}_b> \"$locInfo->name\"",
      "type" => ProjectConstants::TYPE_FIXING_DESTROYED_BUILDING,
      "subtype" => $locationId,
      "turnsleft" => $this->doneTurns * 100,
      "turnsneeded" => $this->neededTurns * 100,
      "reqneeded" => $req,
      "reqleft" => $req,
      "initiator" => $this->character,
      "init_day" => $currentDate->getDay(),
      "init_turn" => $currentDate->getHour(),
      "skill" => 0,
    ];
    return $columnValuePairs;
  }

  private function escapeArrayValues($toEscape)
  {
    $escaped = [];
    foreach ($toEscape as $key => $value) {
      $escaped[$key] = $this->db->quote($value);
    }
    return $escaped;
  }


  private function throwPublicInstantCancelEvent()
  {
    if ($this->character > 0) {
      $projectName = urlencode($this->project_info->name);
      Event::createEventInLocation(267, "ACTOR=$this->character PROJECTNAME=$projectName",
        $this->location, Event::RANGE_SAME_LOCATION, [$this->character]);
    }
  }

  private function throwPublicBeginCancelEvent()
  {
    if ($this->character > 0) {
      $projectName = urlencode($this->project_info->name);
      Event::createEventInLocation(268, "ACTOR=$this->character PROJECTNAME=$projectName",
        $this->location, Event::RANGE_SAME_LOCATION, [$this->character]);
    }
  }

  private function throwPrivateInstantCancelEvent()
  {
    if ($this->character > 0) {
      $projectName = urlencode($this->project_info->name);
      Event::createPersonalEvent(269, "PROJECTNAME=$projectName", $this->character);
    }
  }

  private function throwPrivateBeginCancelEvent()
  {
    if ($this->character > 0) {
      $projectName = urlencode($this->project_info->name);
      Event::createPersonalEvent(270, "PROJECTNAME=$projectName", $this->character);
    }
  }

  private function throwPublicDisassemblingEvent()
  {
    if ($this->character > 0) {
      $object_id = $this->project_info->subtype;
      Event::createEventInLocation(224, "OBJECT=$object_id ACTOR=$this->character",
        $this->location, Event::RANGE_SAME_LOCATION, [$this->character]);
    }
  }

  private function throwPrivateDisassemblingEvent()
  {
    if ($this->character > 0) {
      $object_id = $this->project_info->subtype;
      Event::createPersonalEvent(225, "OBJECT=$object_id", $this->character);
    }
  }

  private function throwPrivateRecoverEvent($recovItemsAndRawsArray, $lostRawsArray = [])
  {

    $stm = $this->db->prepare("SELECT c.location = :location FROM chars c WHERE c.id = :initiator");
    $stm->bindInt("location", $this->location);
    $stm->bindInt("initiator", $this->initiator);
    $initiatorHere = $stm->executeScalar();
    if ($initiatorHere || ($this->character > 0)) {
      if (count($recovItemsAndRawsArray) > 0) {
        $recoverlist = urlencode(implode(', ', $recovItemsAndRawsArray));
        $eventReceiver = $initiatorHere ? $this->initiator : $this->character;
        if (!empty($lostRawsArray)) {
          $lostList = urlencode(implode(', ', $lostRawsArray));
          Event::createPersonalEvent(379, "RECOVER=$recoverlist LOST=$lostList", $eventReceiver);
        } else {
          Event::createPersonalEvent(266, "RECOVER=$recoverlist", $eventReceiver);
        }
      }
    }
  }
}
