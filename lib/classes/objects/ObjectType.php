<?php

class ObjectType
{
  /** @var DbObjectRegistry */
  private static $registry; // registry for cached object type instances

  // Private members mapping to database 'objecttypes' table.
  private $id = 0;
  private $name;
  private $unique_name;
  /** @var  ObjectAction[] */
  private $instructions_outside;
  /** @var  ObjectAction[] */
  private $instructions_inventory;

  private $build_conditions;
  private $build_description;
  private $build_requirements;
  private $build_result;
  private $skill;

  private $subtable; // should be remove after replace_strings method is fixed

  private $category;
  private $rules;
  private $visible;
  // no report
  private $deter_rate_turn;
  private $deter_rate_use;
  private $repair_rate;
  private $deter_visible;
  private $project_weight;
  private $image_file_name;

  /** @var Category */
  private $objectcategory;


  public static function staticInit()
  {
    self::$registry = new DbObjectRegistry();
  }

  // Return a ObjectType object defined by its ID in the database
  public static function loadById($typeId)
  {
    if (self::$registry->contains($typeId)) {
      return self::$registry->get($typeId);
    }

    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM objecttypes WHERE id = :id");
    $stm->bindInt("id", $typeId);
    $stm->execute();
    if ($objectTypeInfo = $stm->fetchObject()) {
      $obj = self::loadFromFetchObject($objectTypeInfo);
      self::$registry->put($typeId, $obj);
      return $obj;
    }
    throw new InvalidArgumentException("Character $typeId doesn't exist");
  }

  /**
   * Efficiently loads a list of object types. It does use ObjectType cache
   * @param int[] $typeIds list of object type ids
   * @return ObjectType[] array of types for specified ids
   */
  public static function bulkLoadByIds(array $typeIds)
  {
    $db = Db::get();
    $objectTypes = [];
    list($alreadyLoaded, $toBeLoaded) = Pipe::from($typeIds)->partition(function($typeId) {
      return self::$registry->contains($typeId);
    });

    foreach ($alreadyLoaded as $typeId) {
      $objectTypes[$typeId] = self::$registry->get($typeId);
    }

    if (!empty($toBeLoaded)) {
      $stm = $db->prepareWithIntList("SELECT * FROM objecttypes WHERE id IN (:ids)", [
        "ids" => $toBeLoaded,
      ]);
      $stm->execute();
      foreach ($stm->fetchAll() as $typeRow) {
        $type = self::loadFromFetchObject($typeRow);
        self::$registry->put($type->getId(), $type);
        $objectTypes[$type->getId()] = $type;
      }
    }
    return $objectTypes;
  }

  public static function loadFromFetchObject($mysqlRow)
  {
    $objectType = new ObjectType();
    $objectType->id = $mysqlRow->id;
    $objectType->name = $mysqlRow->name;
    $objectType->unique_name = $mysqlRow->unique_name;
    $objectType->instructions_outside = self::getActionsFromInstructionsString($mysqlRow->show_instructions_outside);
    $objectType->instructions_inventory = self::getActionsFromInstructionsString($mysqlRow->show_instructions_inventory);
    $objectType->build_conditions = $mysqlRow->build_conditions;
    $objectType->build_description = $mysqlRow->build_description;
    $objectType->build_requirements = $mysqlRow->build_requirements;
    $objectType->build_result = $mysqlRow->build_result;
    $objectType->skill = $mysqlRow->skill;

    $objectType->subtable = $mysqlRow->subtable; // subtable should be removed immediately after fixing Object::replace_strings method
    $objectType->rules = $mysqlRow->rules;
    $objectType->visible = $mysqlRow->visible;
    // no report
    $objectType->deter_rate_turn = $mysqlRow->deter_rate_turn;
    $objectType->deter_rate_use = $mysqlRow->deter_rate_use;
    $objectType->repair_rate = $mysqlRow->repair_rate;
    $objectType->deter_visible = !!$mysqlRow->deter_visible;
    $objectType->project_weight = $mysqlRow->project_weight;
    $objectType->category = $mysqlRow->category;
    $objectType->objectcategory = new Category($mysqlRow->objectcategory);

    $objectType->image_file_name = $mysqlRow->image_file_name;

    return $objectType;
  }

  private static function getActionsFromInstructionsString($instructionsString)
  {
    $instructions = Parser::rulesToArray($instructionsString, ";:");
    $actions = [];
    if (array_key_exists("buttons", $instructions)) {
      $buttons = explode(",", $instructions["buttons"]);
      foreach ($buttons as $buttonRule) {
        $button = explode(">", $buttonRule);
        $actions[] = new ObjectAction($button[1], $button[0], $button[2]);
      }
    }
    return $actions;
  }

  /**
   * @return int id of an object type
   */
  public function getId()
  {
    return $this->id;
  }


  /**
   * @return string conditions that must be fulfilled to start the manufacturing project. e.g. machines needed
   */
  public function getBuildConditions()
  {
    return $this->build_conditions;
  }

  /**
   * @return string name of manufacturing project
   */
  public function getBuildProjectName()
  {
    return $this->build_description;
  }

  /**
   * @return string rule-like syntax for specifying requirements like raws, objects, days needed
   */
  public function getBuildRequirements()
  {
    return $this->build_requirements;
  }

  /**
   * @return string result to be used upon finishing a manufacturing project
   */
  public function getBuildResult()
  {
    return $this->build_result;
  }

  /**
   * @return int deterioration inflicted every day
   */
  public function getDeterRatePerDay()
  {
    return $this->deter_rate_turn;
  }

  /**
   * @return int deterioration inflicted per day of use
   */
  public function getDeterRatePerUse()
  {
    return $this->deter_rate_use;
  }

  public function isDeteriorationVisible()
  {
    return $this->deter_visible;
  }

  /**
   * @return ObjectAction[] actions that can be performed when object is in character's inventory
   */
  public function getActionsInInventory()
  {
    return $this->instructions_inventory;
  }

  /**
   * @return ObjectAction[] actions that can be performed when object is on the ground
   */
  public function getActionsOnGround()
  {
    return $this->instructions_outside;
  }

  /**
   * @return string name of object type. Same value of 'name' for tools means they are interchangeable in projects requiring them
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return Category object category of this object. Build menu is based on this category
   */
  public function getObjectCategory()
  {
    return $this->objectcategory;
  }

  /**
   * @return string category string. Should not be relied upon, if possible. Use objectcategory instead
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * @return int weight of a single unit of this object. For non-quantity objects it's the same as weight of the object
   */
  public function getUnitWeight()
  {
    return $this->project_weight;
  }

  /**
   * @return int informs how much of deterioration is repaired in one hour
   */
  public function getRepairRate()
  {
    return $this->repair_rate;
  }

  /**
   * Rules are old (deprecated) style of setting objecttype specific behaviour.
   * Use object properties instead. See docs or forum to know what's that.
   * @return string rules of this object type
   */
  public function getRules()
  {
    return $this->rules;
  }

  /**
   * @return int skill used to manufacture this object. {@see StateConstants} for skill constants
   */
  public function getProductionSkill()
  {
    return $this->skill;
  }

  /**
   * @return string name used to handle translations. THERE'S NO GUARANTEE THAT IT'S REALLY UNIQUE!!!
   */
  public function getUniqueName()
  {
    return $this->unique_name;
  }

  /**
   * @return mixed
   * @deprecated it should be removed immediately after fixing {@link ObjectView::replace_strings}
   */
  public function getSubtable()
  {
    return $this->subtable;
  }

  /**
   * Returns build requirements normalized to the raw materials used. If type requires object to build,
   * then its raw materials are added to this summary. For example log cabin (made of 25 large logs, each 500 grams of timber)
   * becomes normalized to 25 * 500 = 12500 grams of timber.
   * This fails and throws exception if it's possible to produce any intermediate object using more than one material type,
   * because it's impossible to unambiguously decide which raw material should be taken into account.
   *
   * @retun array all raw materials required, each key being raw material name and value being amount
   * @throws AmbiguousBuildRequirementsException when any object in build requirements can be manufactured using more than a single resource
   */
  public function getBuildRequirementsNormalizedToRaws()
  {
    $buildRequirements = $this->getBuildRequirementsArray();
    $normalizedRawsNeeded = Parser::rulesToArray($buildRequirements["raws"], ",>");

    if (!empty($buildRequirements["objects"])) {
      $objectsNeeded = Parser::rulesToArray($buildRequirements["objects"], ",>");
      foreach ($objectsNeeded as $objectName => $number) {
        $rawsNeededForObject = $this->getUnambiguouslyRawsForBuildingObject($objectName);
        foreach ($rawsNeededForObject as $rawName => $rawAmount) {
          $normalizedRawsNeeded[$rawName] = ($normalizedRawsNeeded[$rawName] ?: 0) + $rawAmount * $number;
        }
      }
    }

    return $normalizedRawsNeeded;
  }

  /**
   * @param $objectName string name (not unique name) of the object
   * @return array where key is name of raw and value is amount
   * @throws AmbiguousBuildRequirementsException
   */
  private function getUnambiguouslyRawsForBuildingObject($objectName)
  {
    $allPotentialObjects = ObjectTypeFinder::any()->name($objectName)->findAll();
    $possibleRawRequirements = Pipe::from($allPotentialObjects)->map(function(ObjectType $objectType) {
      return $objectType->getBuildRequirementsNormalizedToRaws();
    })->toArray();
    if (!$this->allWaysToProduceHaveSameRawRequirements($possibleRawRequirements)) {
      throw new AmbiguousBuildRequirementsException($this->getUniqueName() . " can be built using an object produced from different raw materials");
    }
    return $possibleRawRequirements[0];
  }

  /**
   * Visible for testing.
   * @param array $rawsNeededForWaysToProduce array of assoc arrays of requirements
   * @return bool true if all arrays in the array are the same
   */
  public static function allWaysToProduceHaveSameRawRequirements(array $rawsNeededForWaysToProduce)
  {
    foreach ($rawsNeededForWaysToProduce as $rawsNeededForWayToProduce) {
      if (!empty(array_diff_assoc($rawsNeededForWaysToProduce[0], $rawsNeededForWayToProduce))) {
        return false;
      }
    }
    return true;
  }

  private function getBuildRequirementsArray()
  {
    return Parser::rulesToArray($this->getBuildRequirements(), ";:");
  }

  /**
   * @return string
   */
  public function getImageFileName()
  {
    return $this->image_file_name;
  }
}

ObjectType::staticInit();
