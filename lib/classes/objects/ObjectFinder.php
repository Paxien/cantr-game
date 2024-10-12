<?php

/**
 * Responsible for searching in database to get objects ids or Object class instances matching the selected criteria.
 */
class ObjectFinder
{

  private $location;
  private $person;
  private $attached;

  private $names = [];
  private $types = [];
  private $noTypes = [];
  private $typeids = [];
  private $ids = [];
  private $noIds = [];
  private $setting = null;
  private $noSetting = null;
  private $properties = null;
  private $noProperties = null;
  /** @var Db */
  private $db;

  private function __construct()
  {
    $this->db = Db::get();
  }

  /**
   * Static factory method to specify location of objects. Ony one of the arguments can be positive number.
   * It should never be used to find already expired objects (location, person or attached < 0).
   * @param int $location
   * @param int $person
   * @param int $attached
   * @return ObjectFinder
   */
  public static function placedIn($location, $person, $attached)
  {
    $finder = new ObjectFinder();
    $posIds = Pipe::from([$location, $person, $attached])->filter(function($id) {
      return Validation::isPositiveInt($id);
    })->toArray(); // exctly one of these values should be a positive int
    if (count($posIds) != 1) {
      throw new InvalidArgumentException("exactly one of: $location, $person, $attached should be a positive int");
    }
    $finder->location = intval($location);
    $finder->person = intval($person);
    $finder->attached = intval($attached);

    return $finder;
  }

  /**
   * Specify type
   * @param int $type positive value
   * @return ObjectFinder for method chaining
   */
  public function type($type)
  {
    return $this->types([$type]);
  }

  /**
   * Specify types. Overwrite previous selection.
   * @param array $types of positive integers. Empty list = no condition
   * @return ObjectFinder for method chaining
   */
  public function types(array $types)
  {
    if (!Validation::isPositiveIntArray($types)) {
      throw new InvalidArgumentException("array should contain positive integers");
    }
    $this->types = $types;
    return $this;
  }


  /**
   * Specify excluded (ignored) types
   * @param int $noType positive value
   * @return ObjectFinder for method chaining
   */
  public function exceptType($noType)
  {
    return $this->exceptTypes([$noType]);
  }

  /**
   * Specify types excluded from the search. Overwrite previous selection.
   * @param array $noTypes of positive integers. Empty list = no condition
   * @return ObjectFinder for method chaining
   */
  public function exceptTypes(array $noTypes)
  {
    if (!Validation::isPositiveIntArray($noTypes)) {
      throw new InvalidArgumentException("array should contain positive integers");
    }
    $this->noTypes = $noTypes;
    return $this;
  }


  /**
   * Specify typeid.
   * @param int $typeid subtype of object (different meaning for different object type)
   * @return ObjectFinder for method chaining
   */
  public function typeid($typeid)
  {
    return $this->typeids([$typeid]);
  }

  /**
   * Specify typeids. Overwrite previous selection.
   * @param array $typeids subtypes of object (different meaning for different object type)
   * @return ObjectFinder for method chaining
   */
  public function typeids(array $typeids)
  {
    if (!Validation::isNonNegativeIntArray($typeids)) {
      throw new InvalidArgumentException("array should contain positive integers");
    }
    $this->typeids = $typeids;
    return $this;
  }

  /**
   * Specify id.
   * @param int $id id of object (for example if query is done to learn about object's params)
   * @return $this for method chaining
   */
  public function id($id)
  {
    return $this->ids([$id]);
  }

  /**
   * Specify ids. Overwrite previous selection.
   * @param array $ids of objects (for example if query is done to learn about objects' params)
   * @return $this for method chaining
   */
  public function ids(array $ids)
  {
    if (!Validation::isPositiveIntArray($ids)) {
      throw new InvalidArgumentException("array should contain positive integers");
    }
    $this->ids = $ids;
    return $this;
  }

  /**
   * Specify name (NOT UNIQUE NAME!). It's usually used to find tools for project
   * @param string $name
   * @return ObjectFinder for method chaining
   */
  public function name($name)
  {
    return $this->names([$name]);
  }

  /**
   * Specify names (NOT UNIQUE NAMES). Overwrites previous selection
   * @param array $names of objects (for example if query is done to learn about objects' params)
   * @return ObjectFinder for method chaining
   */
  public function names(array $names)
  {

    $this->names = $names;
    return $this;
  }

  /**
   * Specify id which should be omitted.
   * @param $noIds int ids to be excluded from result set
   * @return ObjectFinder for method chaining
   */
  public function exceptId($noIds)
  {
    return $this->exceptIds([$noIds]);
  }

  /**
   * Specify ids which should be omitted. Overwrite previous selection.
   * @param array $noIds ids of objects to be omitted
   * @return ObjectFinder for method chaining
   */
  public function exceptIds(array $noIds)
  {
    if (!Validation::isPositiveIntArray($noIds)) {
      throw new InvalidArgumentException("array should contain positive integers");
    }
    $this->noIds = $noIds;
    return $this;
  }

  /**
   * Specify allowed object setting
   * @param int $setting of object (@see ObjectConstants::SETTING_PORTABLE etc.)
   * @return ObjectFinder for method chaining
   */
  public function setting($setting)
  {
    if (!Validation::isPositiveInt($setting)) {
      throw new InvalidArgumentException("$setting is not a postivie int");
    }
    $this->setting = $setting;
    return $this;
  }

  /**
   * Specify disallowed object setting
   * @param int $noSetting disallowed for objects (@see ObjectConstants::SETTING_PORTABLE etc.)
   * @return ObjectFinder for method chaining
   */
  public function exceptSetting($noSetting)
  {
    if (!Validation::isPositiveInt($noSetting)) {
      throw new InvalidArgumentException("$noSetting is not a postivie int");
    }
    $this->noSetting = $noSetting;
    return $this;
  }

  /**
   * Specify required property. Overwrite previous selection.
   * @param string $name being type name in obj_properties
   * @return ObjectFinder for method chaining
   */
  public function hasProperty($name)
  {
    return $this->hasProperties([$name]);
  }

  /**
   * Specify requirement of ALL of properties. Overwrite previous selection.
   * @param array $names being type name in obj_properties
   * @return ObjectFinder for method chaining
   */
  public function hasProperties($names)
  {
    $this->properties = $names;
    return $this;
  }


  /**
   * Specify disallowed property. Overwrite previous selection.
   * @param string $name being type name in obj_properties
   * @return ObjectFinder for method chaining
   */
  public function hasNotProperty($name)
  {
    return $this->hasNotProperties([$name]);
  }

  /**
   * Specify necessity of not having any of these properties. Overwrite previous selection.
   * @param string[] $names being type name in obj_properties
   * @return ObjectFinder for method chaining
   */
  public function hasNotProperties($names)
  {
    $this->noProperties = $names;
    return $this;
  }

  //
  // Execute search - makes a query or queries to the database
  //

  /**
   * Complete specifying of the search conditions and perform search for objects matching the search conditions.
   * @return CObject[] of Object class instances matching conditions or empty if none found.
   */
  public function findAll()
  {
    return CObject::bulkLoadByIds($this->findIds());
  }

  /**
   * Complete specifying of the search conditions and perform search for object matching the search conditions.
   * @return CObject|null random object matching conditions or null if none found.
   */
  public function find()
  {
    $ids = $this->findIds();
    if (count($ids) > 0) {
      return CObject::loadById($ids[0]);
    }
    return null;
  }

  /**
   * Complete specifying of the search conditions and perform search for object ids matching the search conditions.
   * @return array of object ids. Empty if none found.
   */
  public function findIds()
  {
    $propertyCheckStr = "";
    if ($this->properties !== null) {

      foreach ($this->properties as $prop) {
        $propertyCheckStr .= " AND o.type IN (SELECT op.objecttype_id FROM obj_properties op
        WHERE op.property_type = " . $this->db->quote($prop) . ")";
      }
    }

    $withoutPropertyCheckStr = "";
    if ($this->noProperties !== null) {
      $escapedProps = Pipe::from($this->noProperties)->map(function($prop) {
        return $this->db->quote($prop);
      })->toArray();

      $withoutPropertyCheckStr = " AND o.type NOT IN (SELECT op.objecttype_id FROM obj_properties op
        WHERE op.property_type IN (" . implode(", ", $escapedProps) . "))";
    }

    $stm = $this->db->prepare("SELECT o.id FROM objects o
      INNER JOIN objecttypes ot ON ot.id = o.type
      WHERE o.location = :locationId AND o.person = :charId AND o.attached = :storageId "
      . FinderUtil::intListOrNothing("o.type", $this->types)
      . FinderUtil::exceptIntListOrNothing("o.type", $this->noTypes)
      . FinderUtil::intListOrNothing("o.id", $this->ids)
      . FinderUtil::exceptIntListOrNothing("o.id", $this->noIds)
      . FinderUtil::intListOrNothing("o.typeid", $this->typeids)
      . FinderUtil::equalsIntOrNothing("o.setting", $this->setting)
      . FinderUtil::notEqualsIntOrNothing("o.setting", $this->noSetting)
      . FinderUtil::stringListOrNothing("ot.name", $this->names)
      . $propertyCheckStr
      . $withoutPropertyCheckStr
    );
    $stm->bindInt("locationId", $this->location);
    $stm->bindInt("charId", $this->person);
    $stm->bindInt("storageId", $this->attached);
    $stm->execute();
    return $stm->fetchScalars();
  }

  /**
   * Complete specifying the search conditions and get number of objects matching the search conditions.
   * @return int number of objects fulfilling the conditions
   */
  public function count()
  {
    return count($this->findIds());
  }

  /**
   * Complete specifying of the search conditions and check if any object matching the search conditions exists.
   * @return bool if any of such objects exists.
   */
  public function exists()
  {
    return $this->count() > 0;
  }
}
