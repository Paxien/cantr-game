<?php

import_lib("func.genes.inc.php");

class ProjectGeneral
{
  public $name;
  public $initiator;
  public $location;
  public $init_day;
  public $init_turn;

  public function __construct($name, $initiator, $location, $init_day = null, $init_turn = null)
  {
    $this->name = $name;
    $this->initiator = $initiator;
    $this->location = $location;
    if ($init_day == null) {
      $gameDate = GameDate::NOW();
      $init_day = $gameDate->getDay();
      $init_turn = $gameDate->getHour();
    }
    $this->init_day = $init_day;
    $this->init_turn = $init_turn;
  }

}

// *********
class ProjectType
{
  public $type;
  public $subtype;
  public $skill;
  public $automatic;
  public $max_participants;
  public $uses_digging_slot;

  public function __construct($type, $subtype, $skill, $automatic, $max_participants, $uses_digging_slot)
  {
    $this->type = $type;
    $this->subtype = $subtype;
    $this->skill = $skill;
    $this->automatic = $automatic;
    $this->max_participants = $max_participants;
    $this->uses_digging_slot = $uses_digging_slot;
  }
}

// *********
class ProjectRequirement
{
  public $turnsneeded;
  public $reqneeded;

  public function __construct($turnsneeded, $reqneeded)
  {
    $this->turnsneeded = (float) $turnsneeded;
    $this->reqneeded = $reqneeded;
  }
}

// *********
class ProjectOutput
{
  public $weight;
  public $result;
  public $steps;
  public $result_description;

  public function __construct($weight, $result, $steps = 0, $result_description = "")
  {
    $this->weight = $weight;
    $this->result = $result;
    $this->steps = $steps;
    $this->result_description = $result_description;
  }
}

// *********
class ProjectRequirementLeft
{
  public $turnsleft;
  public $reqleft;

  public function __construct($turnsleft, $reqleft)
  {
    $this->turnsleft = (float) $turnsleft;
    $this->reqleft = $reqleft;
  }
}

/**
 * Class which holds information about one certain project. Can be constructed as new project (__construct), from data from mysql_fetch_object (loadFromFetchObject) or by ID from table `projects` (loadById)
 */
class Project
{

  private $id = null; // project id in database, null if no row in db fits that object

  private $general; // ProjectGeneral
  private $type; // ProjectType
  private $requirement; // ProjectRequirement
  private $output; // ProjectOutput
  private $requirementLeft; // ProjectRequirementLeft

  private $fetch_info; // raw data from db

  // fields for server.projects
  private $toolsUsed = [];
  private $rawToolBoost;

  // traits
  private $traitCanRequireRawTool;
  private $traitOnlyInitiatorOrOwnerCanWork;
  private $traitRequireLockNear;
  private $traitRequireObjectNear;
  private $traitRequireLocationNear;
  private $traitRequirePersonNear;
  private $traitRequireSignNear;
  private $traitMustBeUnlocked;
  private $traitLocationMustBeEmpty;
  private $traitObjectMustBeEmpty;
  private $traitRequireRawTool;
  private $traitIgnoreRawTools;
  private $traitIsAffectedByAgriculturalConditions;
  private $traitIsRawtypeUsed;
  private $traitIncreaseWorkTiredness;
  private $traitActionEveryTurn;
  private $traitIsProjectFinishable;
  private $traitRawToolRequiresCategory;
  private $traitDecayToolsAndMachines;

  // log data
  public $toolByNameHeldByChar = [];

  private $logger;
  // loading data
  /** @var Db */
  private $db;
  /** @var GlobalConfig */
  private $globalConfig;

  /**
   * constructor to create new project object (without row in db)
   */
  public function __construct(ProjectGeneral $general, ProjectType $type, ProjectRequirement $requirement, ProjectOutput $output, ProjectRequirementLeft $requirementLeft = null, $db = null)
  {
    $this->general = clone $general;
    $this->type = clone $type;
    $this->requirement = clone $requirement;
    $this->output = clone $output;
    if ($requirementLeft != null) {
      $this->requirementLeft = clone $requirementLeft;
    } else {
      $this->requirementLeft = new ProjectRequirementLeft($requirement->turnsneeded, $requirement->reqneeded);
    }

    // traits used for checking specific requirements depending on type
    $this->loadTraits();

    $this->logger = Logger::getLogger(__CLASS__);
    if ($db === null) {
      $db = Db::get();
    }
    $this->db = $db;
    $this->globalConfig = new GlobalConfig($this->db);
  }

  /**
   *constructor to create project object from mysql_fetch_object
   * @param stdClass $fetch_obj object with properties representing rows from db
   * @return Project
   */
  static public function loadFromFetchObject(stdClass $fetch_obj, $db)
  {
    // create "subclasses"
    $general = new ProjectGeneral($fetch_obj->name, $fetch_obj->initiator, $fetch_obj->location, $fetch_obj->init_day, $fetch_obj->init_turn);
    $type = new ProjectType($fetch_obj->type, $fetch_obj->subtype, $fetch_obj->skill, $fetch_obj->automatic, $fetch_obj->max_participants, $fetch_obj->uses_digging_slot);
    $requirement = new ProjectRequirement($fetch_obj->turnsneeded, $fetch_obj->reqneeded);
    $output = new ProjectOutput($fetch_obj->weight, $fetch_obj->result, $fetch_obj->steps, $fetch_obj->result_description);
    $requirementLeft = new ProjectRequirementLeft($fetch_obj->turnsleft, $fetch_obj->reqleft);

    // create object itself
    $projectObject = new Project($general, $type, $requirement, $output, $requirementLeft, $db);

    // set id
    $projectObject->id = $fetch_obj->id;
    // set raw object data
    $projectObject->fetch_obj = clone $fetch_obj;

    // traits used for checking specific requirements depending on type
    $projectObject->loadTraits();

    return $projectObject;
  }

  public static function loadById($id, $db = null)
  {
    if ($db === null) {
      $db = Db::get();
    }
    $stm = $db->prepare("SELECT * FROM projects WHERE id = :projectId");
    $stm->bindInt("projectId", $id);
    $stm->execute();
    if ($fetchObj = $stm->fetchObject()) {
      return Project::loadFromFetchObject($fetchObj, $db);
    }
    throw new InvalidArgumentException("ID $id for project is not a number");
  }

  /**
   * Check if project was loaded properly from the database
   */
  public function loaded()
  {
    return ($this->id != null);
  }

  // "ability" of certain actions depending on type

  private function loadTraits()
  {
    $type = $this->type->type;

    $this->traitCanRequireRawTool = in_array($type, [ProjectConstants::TYPE_GATHERING]); // it's different than "tools" in reqneeded
    $this->traitOnlyInitiatorOrOwnerCanWork = in_array($type,
      [ProjectConstants::TYPE_ADOPTING_ANIMAL, ProjectConstants::TYPE_ADOPTING_STEED]);
    $this->traitRequireLockNear = in_array($type, [ProjectConstants::TYPE_PICKING_LOCK, ProjectConstants::TYPE_DISASSEMBLING_OPEN_LOCK]);
    $this->traitRequireObjectNear = in_array($type, [ProjectConstants::TYPE_BURYING, ProjectConstants::TYPE_ADOPTING_ANIMAL,
      ProjectConstants::TYPE_BUTCHERING_ANIMAL, ProjectConstants::TYPE_REPAIRING,
      ProjectConstants::TYPE_DISASSEMBLING, ProjectConstants::TYPE_SADDLING_STEED,
      ProjectConstants::TYPE_BOOSTING_VEHICLE, ProjectConstants::TYPE_DESC_OBJECT_CHANGE]);
    $this->traitRequireLocationNear = in_array($type, [ProjectConstants::TYPE_UNSADDLING_STEED, ProjectConstants::TYPE_DISASSEMBLING_VEHICLE]);
    $this->traitRequirePersonNear = in_array($type, [ProjectConstants::TYPE_HEAL_NEAR_DEATH]);
    $this->traitMustBeUnlocked = in_array($type, [ProjectConstants::TYPE_DISASSEMBLING_OPEN_LOCK]);
    $this->traitRequireRawTool = in_array($type, ProjectConstants::$TYPES_REQUIRING_RAWTOOLS); // can be changed to true in code later

    $this->traitIgnoreRawTools = (strpos($this->requirement->reqneeded, "ignorerawtools") !== false); // ignores requirements/boost from table rawtools
    $this->traitIsAffectedByAgriculturalConditions = StringUtil::contains($this->requirement->reqneeded, "agricultural:1");

    $this->traitIsRawtypeUsed = in_array($type, [ProjectConstants::TYPE_GATHERING]);
    $this->traitRequireSignNear = in_array($type, [ProjectConstants::TYPE_ALTERING_SIGN]);

    $this->traitLocationMustBeEmpty = in_array($type, [ProjectConstants::TYPE_UNSADDLING_STEED,
      ProjectConstants::TYPE_DISASSEMBLING_VEHICLE]);

    $this->traitObjectMustBeEmpty = in_array($type, [ProjectConstants::TYPE_DISASSEMBLING]);

    $this->traitIncreaseWorkTiredness = !in_array($type, [ProjectConstants::TYPE_RESTING]);
    $this->traitActionEveryTurn = in_array($type, [ProjectConstants::TYPE_RESTING,
      ProjectConstants::TYPE_BOOSTING_VEHICLE]);
    $this->traitIsProjectFinishable = !in_array($type, [ProjectConstants::TYPE_RESTING,
      ProjectConstants::TYPE_BOOSTING_VEHICLE]);

    $this->traitRawToolRequiresCategory = in_array($type, [ProjectConstants::TYPE_REPAIRING]);

    $this->traitDecayToolsAndMachines = true;

  }

  /**
   * @param $char
   * @return array of IDs which are event ids of problems preventing project progress. If empty array then progress may be made
   * @throws IllegalStateException
   */
  public function validateProgress($char)
  {
    $isManualProgress = $char instanceof Character;
    $preventingProgress = [];

    $preventingProgress = array_values(array_merge($preventingProgress, $this->isReqNeededAllowed($char)));
    $preventingProgress = array_values(array_merge($preventingProgress, $this->validateRequirementsLeft()));

    if ($this->traitCanRequireRawTool) {
      if ($this->isRawToolRequired()) {
        $this->traitRequireRawTool = true;
      }
    }

    if ($this->traitOnlyInitiatorOrOwnerCanWork) {
      if ($char->getId() != $this->general->initiator) {
        if ($this->type->type == ProjectConstants::TYPE_ADOPTING_ANIMAL) {
          $stm = $this->db->prepare("SELECT loyal_to FROM animal_domesticated WHERE from_object = :objectId");
          $stm->bindInt("objectId", $this->type->subtype);
          $animalOwner = $stm->executeScalar();
        } elseif ($this->type->type == ProjectConstants::TYPE_ADOPTING_STEED) {
          $stm = $this->db->prepare("SELECT loyal_to FROM animal_domesticated WHERE from_location = :locationId");
          $stm->bindInt("locationId", $this->type->subtype);
          $animalOwner = $stm->executeScalar();
        }
        if ($char->getId() != $animalOwner) { // make sure it's not current owner of animal
          $preventingProgress[] = "project_problem_not_initiator";
        }
      }
    }

    if ($this->traitRequireLockNear) {
      if (!$this->isLockNear()) {
        $preventingProgress[] = "project_problem_target_not_here";
      }
    }

    if ($this->traitRequireObjectNear) {
      $objectNeeded = $this->type->subtype;
      if ($objectNeeded && !$this->isObjectNear($objectNeeded)) {
        $preventingProgress[] = "project_problem_target_not_here";
      }
    }

    if ($this->traitRequireLocationNear) {
      $locationNeeded = $this->type->subtype;
      if ($locationNeeded && !$this->isLocationNear($locationNeeded)) {
        $preventingProgress[] = "project_problem_target_not_here";
      }
    }

    if ($this->traitRequirePersonNear) {
      if ($this->type->type == ProjectConstants::TYPE_HEAL_NEAR_DEATH) {
        $personNeeded = $this->type->subtype;
      }
      if ($personNeeded && !$this->isPersonNear($personNeeded)) {
        $preventingProgress[] = "project_problem_target_not_here";
      }
    }

    if ($this->traitLocationMustBeEmpty) {
      $locationId = $this->type->subtype;
      if (!$this->isLocationEmpty($locationId)) {
        $preventingProgress[] = "project_problem_target_not_empty";
      }
    }

    if ($this->traitObjectMustBeEmpty) {
      $objectId = $this->type->subtype;
      if (!$this->objectHasNoFixedContents($objectId)) {
        $preventingProgress[] = "project_problem_target_not_empty";
      }
    }

    if ($this->traitRequireSignNear) {
      if (!$this->isSignNear()) {
        $preventingProgress[] = "project_problem_sign_not_here";
      } // error - sign not here
    }

    if ($this->traitMustBeUnlocked) {
      if (!$this->isLockUnlocked()) {
        $preventingProgress[] = "project_problem_lock_not_unlocked";
      } // error - lock must be unlocked
    }

    if (!$this->traitIgnoreRawTools) {
      if ($isManualProgress) {
        $this->rawToolBoost = $this->getRawToolBoost($char);
      }

      if ($this->traitRequireRawTool) {
        if ($this->rawToolBoost == 0) { // no boost tool was found
          $preventingProgress[] = "project_problem_missing_tools";
        }
      }
    }

    return $preventingProgress;
  }

  private function isReqNeededAllowed($char)
  {
    $reqArray = Parser::rulesToArray($this->requirement->reqneeded);

    $stm = $this->db->prepare("SELECT type, region, area FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $this->general->location);
    $stm->execute();
    $loc_info = $stm->fetchObject();
    if (!$loc_info) {
      return ["project_problem_project_not_here"];
    } // not same location, because we are in location which doesn't exist

    $isManualProgress = $char instanceof Character;
    $preventingProgress = [];

    if (array_key_exists("location_state", $reqArray)) {

      switch ($reqArray['location_state']) {

        case "sailing":
          if ($loc_info->type != LocationConstants::TYPE_SAILING_SHIP) {
            $preventingProgress[] = "project_problem_not_on_sea_or_lake";
          }
          break;

        case "sailing_moving":
          $stm = $this->db->prepare("SELECT speed FROM sailing WHERE vessel = :locationId LIMIT 1");
          $stm->bindInt("locationId", $this->general->location);
          $shipSpeed = $stm->executeScalar();
          $isSailingShip = $loc_info->type == LocationConstants::TYPE_SAILING_SHIP;
          $isMovingShip = ($shipSpeed >= 1);
          if (!($isSailingShip && $isMovingShip)) {
            $preventingProgress[] = "project_problem_not_on_moving_boat";
          }
          break;

        case "sailing_floating":
          $stm = $this->db->prepare("SELECT speed FROM sailing WHERE vessel = :locationId LIMIT 1");
          $stm->bindInt("locationId", $this->general->location);
          $shipSpeed = $stm->executeScalar();
          $isSailingShip = $loc_info->type == LocationConstants::TYPE_SAILING_SHIP;
          $isFloatingShip = ($shipSpeed == 0);
          if (!($isSailingShip && $isFloatingShip)) {
            $preventingProgress[] = "project_problem_not_on_floating_boat";
          }
          break;

        case "docked": // there should be function like "isShip" but I don't see any, maybe todo in the future
          $vehicleInLocation = $loc_info->type == LocationConstants::TYPE_VEHICLE && $loc_info->region != 0;
          $ships = Location::getShipTypeArray();
          if (!($vehicleInLocation && in_array($loc_info->area, $ships))) {
            $preventingProgress[] = "project_problem_not_on_docked_boat";
          }
          break;

        case "parked":
          $vehicleInLocation = $loc_info->type == LocationConstants::TYPE_VEHICLE && $loc_info->region != 0;
          $ships = Location::getShipTypeArray();
          if (!($vehicleInLocation && !(in_array($loc_info->area, $ships)))) {
            $preventingProgress[] = "project_problem_not_in_parked_land_vehicle";
          }
          break;

        case "inside":
          if ($loc_info->type != LocationConstants::TYPE_BUILDING) {
            $preventingProgress[] = "project_problem_not_inside_a_building";
          }
          break;

        case "outside":
          if ($loc_info->type != LocationConstants::TYPE_OUTSIDE) {
            $preventingProgress[] = "project_problem_not_outside";
          }
          break;

        case "travelling":
          if (!($loc_info->type == LocationConstants::TYPE_VEHICLE && $loc_info->region == 0)) {
            $preventingProgress[] = "project_problem_not_travelling_in_land_vehicle";
          }
          break;
      }
    }

    if (array_key_exists('location_areatype', $reqArray)) {
      try {
        $locationPos = Location::loadById($this->general->location);

        $position = Position::getInstance();
        $areatype = $position->check_areatype($locationPos->getX(), $locationPos->getY());
      } catch (InvalidArgumentException $e) {
        $this->logger->error("makeProgress: checking if inexistent location {$this->general->location} is empty", $e);
      }

      if ($areatype != $reqArray['location_areatype']) {
        if ($reqArray['location_areatype'] == "land") {
          $preventingProgress[] = "project_problem_not_on_land";
        } // location type should be land
        elseif ($reqArray['location_areatype'] == "sea") {
          $preventingProgress[] = "project_problem_not_on_sea";
        } // location type should be sea
        elseif ($reqArray['location_areatype'] == "lake") {
          $preventingProgress[] = "project_problem_not_on_lake";
        } // location type should be lake
      }
    }
    if (array_key_exists('tools', $reqArray) && $isManualProgress) {
      if (!$this->hasNeededToolsByName($reqArray['tools'], $char)) {
        $preventingProgress[] = "project_problem_missing_tools";
      }
    }
    return $preventingProgress;
  }

  private function isRawToolRequired()
  {
    $stm = $this->db->prepare("SELECT reqtools FROM rawtypes WHERE id = :id");
    $stm->bindInt("id", $this->type->subtype);
    $reqtools = $stm->executeScalar();

    return ($reqtools == 1);
  }

  private function isLockNear()
  {
    if (!Validation::isPositiveInt($this->output->result)) {
      throw new IllegalStateException("class.Projects.php - lock id (project result) must be a number");
    }

    $searchedId = $this->output->result;

    $stm = $this->db->prepare("SELECT region FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $this->general->location);
    $parent_loc = $stm->executeScalar();

    $stm = $this->db->prepare("SELECT attached FROM objects WHERE id = :objectId");
    $stm->bindInt("objectId", $this->output->result);
    $parentId = $stm->executeScalar();

    if ($parentId > 0) {
      // if lock is inside some storage object, we check if container is in the same location
      $stm = $this->db->prepare("SELECT location FROM objects WHERE id = :objectId");
      $stm->bindInt("objectId", $parentId);
      $lockedObjLoc = $stm->executeScalar();
      return $lockedObjLoc == $this->general->location;
    }

    $stm = $this->db->prepare("SELECT location FROM objects WHERE id = :objectId");
    $stm->bindInt("objectId", $searchedId);
    $lock_location = $stm->executeScalar();

    if ($parent_loc == null) {
      $stm = $this->db->prepare("SELECT id FROM locations WHERE id = :locationId OR region = :regionId ");
      $stm->bindInt("locationId", $this->general->location);
      $stm->bindInt("regionId", $this->general->location);
      $stm->execute();
    } else {
      $stm = $this->db->prepare("SELECT id FROM locations WHERE id = :locationId1 OR id = :locationId2 OR region = :regionId");
      $stm->bindInt("locationId1", $this->general->location);
      $stm->bindInt("locationId2", $parent_loc);
      $stm->bindInt("regionId", $this->general->location);
      $stm->execute();
    }

    return in_array($lock_location, $stm->fetchScalars());
  }

  private function isSignNear()
  {
    $stm = $this->db->prepare("SELECT region FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $this->type->subtype);
    $region_data = $stm->executeScalar();
    return ($region_data && $region_data == $this->general->location);
  }

  private function isObjectNear($object_id)
  {
    try {
      $object = CObject::loadById($object_id);
      if ($object->getLocation() > 0) {
        return $object->getLocation() == $this->general->location;
      }
      if ($object->getPerson() > 0) {
        $holder = Character::loadById($object->getPerson());
        return $holder->getLocation() == $this->general->location;
      }
    } catch (InvalidArgumentException $e) {
    }
    return false;
  }

  private function isLocationNear($location_id)
  {
    $stm = $this->db->prepare("SELECT region FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $location_id);
    $targetRegion = $stm->executeScalar();
    return ($targetRegion && $targetRegion == $this->general->location);
  }

  private function isPersonNear($char_id)
  {
    $stm = $this->db->prepare("SELECT location FROM chars WHERE id = :charId AND status = :active");
    $stm->bindInt("charId", $char_id);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $person_loc = $stm->executeScalar();
    return ($person_loc && $person_loc == $this->general->location);
  }

  private function isLockUnlocked()
  {
    $stm = $this->db->prepare("SELECT specifics FROM objects WHERE id = :objectId");
    $stm->bindInt("objectId", $this->output->result);
    $specifics = $stm->executeScalar();
    return ($specifics == "unlocked");
  }

  private function isLocationEmpty($location_id)
  {
    try {
      $location = Location::loadById($location_id);
      return $location->isEmpty();
    } catch (InvalidArgumentException $e) {
      $this->logger->error("makeProgress: checking if inexistent location $location_id is empty", $e);
    }
    return false;
  }

  private function objectHasNoFixedContents($objectId)
  {
    try {
      $object = CObject::loadById($objectId);
      return $object->hasNoFixedContents();
    } catch (InvalidArgumentException $e) {
      $this->logger->error("makeProgress: checking if inexistent object $objectId is empty", $e);
    }
    return false;
  }

  private function hasNeededToolsByName($tools, Character $char)
  {
    $toolNames = explode(",", $tools);

    $hasAllTools = true;
    foreach ($toolNames as $tool) {
      if (!$this->hasToolByName($tool, $char)) {
        $hasAllTools = false;
      }
    }
    return $hasAllTools;
  }

  private function hasToolByName($toolName, Character $char)
  {

    $stm = $this->db->prepare("SELECT obj.id FROM objecttypes ot
        INNER JOIN objects obj ON obj.type = ot.id AND obj.person = :charId
      WHERE ot.name = :name ORDER BY deterioration DESC LIMIT 1");
    $stm->bindInt("charId", $char->getId());
    $stm->bindStr("name", $toolName);
    $toolId = $stm->executeScalar();

    if ($toolId !== null) {
      $this->toolsUsed[] = $toolId;
      $found = true;
    } else {
      $found = false;
    }
    $this->toolByNameHeldByChar[$toolName] = $found;
    return $found;
  }

  /**
   * Returns stdClass object with properties specifying the best rawtool that can be used by a $char to increase efficiency of the project.
   * Properties are:
   *   "boost" => efficiency increase from this tool,
   *   "id" => object id,
   *   "type" => object type,
   *   "categories" => categories of objects that can be applied for this tool, makes sanse only if $this->traitRawToolRequiresCategory is true
   *
   * @param Character $char owner of the tools
   * @return stdClass with properties: boost, id, categories
   */
  public function getBoostingTool(Character $char)
  {
    $add_query = "";
    if ($this->traitIsRawtypeUsed) {
      $add_query = "AND rawtype = " . intval($this->type->subtype);
    }

    $stm = $this->db->prepare("SELECT rt.perday AS boost, obj.id, obj.type AS type, rt.categories FROM rawtools rt
      INNER JOIN objects obj ON rt.tool = obj.type AND obj.person = :charId
      WHERE projecttype = :projectType $add_query
      ORDER BY boost DESC, obj.deterioration");
    $stm->bindInt("charId", $char->getId());
    $stm->bindInt("projectType", $this->type->type);
    $stm->execute();
    $rawTools = $stm->fetchAll();

    $bestTool = null;
    if ($this->traitRawToolRequiresCategory) { // some tools which boost object repair are applicable only for certain types
      try {
        $subject = CObject::loadById($this->type->subtype);
        $subjectProperties = $subject->getPropertyNames();
        foreach ($rawTools as $rawTool) { // check rawtools ordered from best to worst
          if (!empty($rawTool->categories)) {
            $categories = explode(",", $rawTool->categories);
            $categories = $categories != null ? $categories : [];
            $matchedCategories = array_intersect($categories, $subjectProperties); // find categories of repair tool applicable for subject

            if (!empty($matchedCategories)) { // if does apply for any of the categories
              $bestTool = $rawTool;
              break;
            }
          }
        }
      } catch (InvalidArgumentException $e) {
        $this->logger->info("Object " . $this->type->subtype . " being subject of a project doesn't exist");
      }
    } elseif (count($rawTools) > 0) {
      $bestTool = $rawTools[0];
    }
    return $bestTool;
  }

  private function getRawToolBoost(Character $char)
  {
    $tool = $this->getBoostingTool($char);
    if ($tool !== null) {
      $this->toolsUsed[] = $tool->id;
      return $tool->boost;
    }
    return 0;
  }

  private function validateRequirementsLeft()
  {
    $reqArray = Parser::rulesToArray($this->requirementLeft->reqleft);
    $preventingProgress = [];

    if (array_key_exists("raws", $reqArray) && !$this->isRequiredZero($reqArray['raws'])) {
      $preventingProgress[] = "project_problem_missing_raws";
    }

    if (array_key_exists("objects", $reqArray) && !$this->isRequiredZero($reqArray['objects'])) {
      $preventingProgress[] = "project_problem_missing_objects";
    }

    return $preventingProgress;
  }

  private function isRequiredZero($toParse)
  {
    if ($toParse != null && !empty($toParse)) {
      $memberArray = Parser::rulesToArray($toParse, ",>");
      foreach ($memberArray as $k => $isLeft) {
        if ($isLeft != 0) {
          return false;
        }
      }
    }
    return true;
  }

  /**
   * @param Character|null $char character that makes the progress or null if project is automatic
   * @return bool
   * @throws IllegalStateException
   */
  public function makeProgress($char)
  {
    if (!$this->id) {
      throw new IllegalStateException("can't makeProgress for project not existing in db");
    }

    $isManualProgress = $char instanceof Character;

    $rawToolBoostMultiplier = max(1, $this->rawToolBoost / 100);
    $progress = ProjectConstants::DEFAULT_PROGRESS_PER_TURN * $this->globalConfig->getProjectProgressRatio() * $rawToolBoostMultiplier;

    // significantly speed up "giving" animal to sb else by joining their adoption project
    if ($this->traitOnlyInitiatorOrOwnerCanWork) { // initiator or owner
      if ($this->general->initiator != $char->getId()) { // not initiator => it must be owner
        $progress *= ProjectConstants::GIVING_LOYAL_ANIMAL_SPEED; // so make it much faster
      }
    }
    if ($this->traitIsAffectedByAgriculturalConditions) {
      try {
        $locationPos = Location::loadById($this->general->location);
        $weather = Weather::loadByPos($locationPos->getX(), $locationPos->getY());
        $harvestEfficiency = $weather->getAgriculturalConditions()->getHarvestEfficiency();
        $progress *= AgriculturalConditions::getProgressMultiplier($harvestEfficiency);
      } catch (InvalidArgumentException $e) {
        $this->logger->warn("Project in inexistent location {$this->general->location}", $e);
      }
    }

    if ($isManualProgress) {
      $progress = $this->calculateRealProgress($progress, $char);
    }

    if ($this->traitDecayToolsAndMachines) {
      $this->decayTools($this->requirementLeft->turnsleft, $progress);
      $this->decayMachines($this->requirementLeft->turnsleft, $progress);
    }

    if ($this->traitIncreaseWorkTiredness && $isManualProgress) {
      $this->increaseTiredness($progress, $char);
    }

    if ($this->isSkillUsed() && $isManualProgress) {
      // adjust progress depending on skill
      $progress *= ($char->getState($this->type->skill) / _SCALESIZE_GSS) + 0.45;
      // increase skill of character
      $char->alterState($this->type->skill, rand_round(ProjectConstants::PROJECT_SKILL_GAIN_PER_TURN));
    }

    if ($this->traitActionEveryTurn) {
      $this->performEveryTurn($progress, $char);
    }

    if ($this->traitIsProjectFinishable) {
      $this->requirementLeft->turnsleft = max($this->requirementLeft->turnsleft - $progress, 0);
      if ($this->requirementLeft->turnsleft == 0) {
        if ($this->finishProject()) { // normal project finish
          return true;
        } // else decreases steps for multistep project
      }
    }
    $this->saveInDb();

    return false;
  }

  private function isSkillUsed()
  {
    return ($this->type->skill != 0);
  }

  private function calculateRealProgress($progress, Character $char)
  {
    return $char->getState(_GSS_HEALTH) / _SCALESIZE_GSS
      * (1 - $char->getState(StateConstants::TIREDNESS) / _SCALESIZE_GSS)
      * $progress;
  }

  private function decayTools($progressNeeded, $progress)
  {
    import_lib("func.expireobject.inc.php");

    // if only 10 turnsleft and progress is 100 then 10% of decay is applied
    $multiplier = min(1, $progress > 0 ? $progressNeeded / $progress : 0);

    $toolsNum = count($this->toolsUsed);
    foreach ($this->toolsUsed as $toolId) {
      usage_decay_object($toolId, ProjectConstants::DECAY_PER_TURN / $toolsNum * $multiplier);
    }
  }

  private function decayMachines($progressNeeded, $progress)
  {
    import_lib("func.expireobject.inc.php");

    $multiplier = min(1, $progress > 0 ? $progressNeeded / $progress : 0);
    $stm = $this->db->prepare("SELECT id FROM objects WHERE location = :locationId AND specifics = :specifics");
    $stm->bindInt("locationId", $this->general->location);
    $stm->bindStr("specifics", $this->id);
    $stm->execute();
    foreach ($stm->fetchScalars() as $machineId) {
      usage_decay_object($machineId, ProjectConstants::DECAY_PER_TURN * $multiplier);
    }
  }

  private function increaseTiredness($progress, Character $char)
  {
    $change = ProjectConstants::TIREDNESS_FROM_WORKING_PER_TURN * min($progress / ProjectConstants::DEFAULT_PROGRESS_PER_TURN, 1);
    $char->alterState(StateConstants::TIREDNESS, rand_round($change));
  }

  private function performEveryTurn($progress, $char)
  {
    $isManualProgress = $char instanceof Character;
    if (($this->type->type == ProjectConstants::TYPE_RESTING) && $isManualProgress) {
      $char->alterState(StateConstants::TIREDNESS, rand_round(0 - $this->output->result / ProjectConstants::TURNS_PER_DAY));
    }
    if ($this->type->type == ProjectConstants::TYPE_BOOSTING_VEHICLE) {
      // increase number of people working on speed boosting by 1
      if (is_numeric($this->output->result)) {
        $this->output->result += 1;
      } else {
        $this->output->result = 1;
      }
    }
  }

  public function saveInDb()
  {
    if ($this->id != null) { // already exists in db
      $stm = $this->db->prepare("UPDATE projects SET
      name = :name, initiator = :initiator,
      init_day = :initDay, init_turn = :initHour,
      type = :type, subtype = :subtype, skill = :skill,
      automatic = :automatic, max_participants = :maxParticipants,
      uses_digging_slot = :usesDiggingSlot,
      turnsneeded = :turnsNeeded, reqneeded = :reqNeeded,
      weight = :weight, result = :result, steps = :steps,
      result_description = :resultDescription,
      turnsleft = :turnsLeft, reqleft = :reqLeft
      WHERE id = :projectId");
      $this->bindParams($stm);
      $stm->bindInt("projectId", $this->id);
      $stm->execute();
    } else { // adding new project
      $stm = $this->db->prepare("INSERT INTO projects (
      name, initiator, location, init_day, init_turn,
      type, subtype, skill,
      automatic, max_participants,
      uses_digging_slot,
      turnsneeded, reqneeded, 
      weight, result, steps, result_description,
      turnsleft, reqleft )
      VALUES (:name, :initiator, :locationId, :initDay, :initHour,
      :type, :subtype, :skill, :automatic, :maxParticipants, :usesDiggingSlot,
      :turnsNeeded, :reqNeeded,
      :weight, :result, :steps, :resultDescription, :turnsLeft, :reqLeft)");
      $this->bindParams($stm);
      $stm->bindInt("locationId", $this->general->location);
      $stm->execute();
      $this->id = $this->db->lastInsertId();
    }
  }

  /**
   * @param DbStatement $stm
   */
  private function bindParams(DbStatement $stm)
  {
    $stm->bindStr("name", $this->general->name);
    $stm->bindInt("initiator", $this->general->initiator);
    $stm->bindInt("initDay", $this->general->init_day);
    $stm->bindInt("initHour", $this->general->init_turn);
    $stm->bindInt("type", $this->type->type);
    $stm->bindInt("subtype", $this->type->subtype);
    $stm->bindInt("skill", $this->type->skill);
    $stm->bindInt("automatic", $this->type->automatic);
    $stm->bindInt("maxParticipants", $this->type->max_participants);
    $stm->bindInt("usesDiggingSlot", $this->type->uses_digging_slot);
    $stm->bindFloat("turnsNeeded", $this->requirement->turnsneeded);
    $stm->bindStr("reqNeeded", $this->requirement->reqneeded);
    $stm->bindInt("weight", $this->output->weight);
    $stm->bindStr("result", $this->output->result);
    $stm->bindInt("steps", $this->output->steps);
    $stm->bindStr("resultDescription", $this->output->result_description);
    $stm->bindFloat("turnsLeft", $this->requirementLeft->turnsleft);
    $stm->bindStr("reqLeft", $this->requirementLeft->reqleft);
  }

  private function finishProject()
  {
    import_lib("func.finishproject.inc.php");

    $this->updateFetchInfo();
    try {
      $isDone = finish_project($this->fetch_info, 0); // old function, it will be improved in the future
    } catch (Exception $e) {
      $this->logger->error("Should never happen. Unknown error when finishing project " . $this->getId(), $e);
      return true;
    }

    if ($isDone) { // no repeat
      $this->deleteFromDb();
      return true;
    } else { // project finished, just starting another in a loop
      $this->requirementLeft->turnsleft = $this->requirement->turnsneeded;
      $this->output->steps--;
      return false;
    }
  }

  public function deleteFromDb()
  {
    ($this->id != null) or die("can't delete project which doesn't exist in database");
    $stm = $this->db->prepare("DELETE FROM projects WHERE id = :projectId LIMIT 1");
    $stm->bindInt("projectId", $this->id);
    $stm->execute();
    $this->id = null;
  }

  private function updateFetchInfo()
  {
    $this->fetch_info = new stdClass();
    $this->fetch_info->id = $this->id;
    $this->fetch_info->name = $this->general->name;
    $this->fetch_info->initiator = $this->general->initiator;
    $this->fetch_info->location = $this->general->location;
    $this->fetch_info->init_day = $this->general->init_day;
    $this->fetch_info->init_turn = $this->general->init_turn;

    $this->fetch_info->type = $this->type->type;
    $this->fetch_info->subtype = $this->type->subtype;
    $this->fetch_info->skill = $this->type->skill;
    $this->fetch_info->automatic = $this->type->automatic;
    $this->fetch_info->max_participants = $this->type->max_participants;
    $this->fetch_info->uses_digging_slot = $this->type->uses_digging_slot;

    $this->fetch_info->turnsneeded = $this->requirement->turnsneeded;
    $this->fetch_info->reqneeded = $this->requirement->reqneeded;

    $this->fetch_info->weight = $this->output->weight;
    $this->fetch_info->result = $this->output->result;
    $this->fetch_info->steps = $this->output->steps;
    $this->fetch_info->result_description = $this->output->result_description;

    $this->fetch_info->turnsleft = $this->requirementLeft->turnsleft;
    $this->fetch_info->reqleft = $this->requirementLeft->reqleft;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getType()
  {
    return $this->type->type;
  }

  public function getSubtype()
  {
    return $this->type->subtype;
  }

  public function getLocation()
  {
    return $this->general->location;
  }

  public function getResult()
  {
    return $this->output->result;
  }

  public function getSkill()
  {
    return $this->type->skill;
  }

  public function getWayOfProgression()
  {
    return $this->type->automatic;
  }

  public function getTurnsLeft()
  {
    return $this->requirementLeft->turnsleft;
  }

  public function getTurnsNeeded()
  {
    return $this->requirement->turnsneeded;
  }

  public function getReqLeft()
  {
    return $this->requirementLeft->reqleft;
  }

  public function setReqLeft($reqLeft)
  {
    $this->requirementLeft->reqleft = $reqLeft;
  }

  public function getReqNeeded()
  {
    return $this->requirement->reqneeded;
  }

  public function getWeight()
  {
    return $this->output->weight;
  }

  public function setWeight($weight)
  {
    $this->output->weight = $weight;
  }

  public function getFractionDone()
  {
    if ($this->requirement->turnsneeded == 0) {
      return 0;
    }
    return (1 - ($this->requirementLeft->turnsleft / $this->requirement->turnsneeded));
  }

  public function getPercentDone()
  {
    return $this->getFractionDone() * 100;
  }

  public function getWorkersCount()
  {
    $stm = $this->db->prepare("SELECT COUNT(*) FROM chars WHERE project = :projectId");
    $stm->bindInt("projectId", $this->getId());
    return $stm->executeScalar();
  }

  public function getStartDay()
  {
    return $this->general->init_day;
  }

  public function getStartHour()
  {
    return $this->general->init_turn;
  }

  public function getInitiator()
  {
    return $this->general->initiator;
  }

  public function getName()
  {
    return $this->general->name;
  }

  public function getMaxParticipants()
  {
    return $this->type->max_participants;
  }

  public function isUsingResourceSlots()
  {
    return $this->type->uses_digging_slot == ProjectConstants::DIGGING_SLOTS_USE;
  }

  public static function locatedIn($location)
  {
    if ($location instanceof Location) {
      $locId = $location->getId();
    } elseif (Validation::isPositiveInt($location)) {
      $locId = intval($location);
    }

    return ProjectFinder::locatedIn($locId);
  }
}
