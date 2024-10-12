<?php

/**
 * Responsible for searching in database to get object type ids or ObjectType
 * class instances matching the selected criteria.
 */
class ObjectTypeFinder
{
  private $ids = [];
  private $noIds = [];
  private $names = [];
  private $uniqueNames = [];
  private $properties = null;
  private $noProperties = null;
  /** @var Db */
  private $db;

  private function __construct()
  {
    $this->db = Db::get();
  }

  /**
   * Static factory method to return any ObjectType (filtered by further set criteria)
   * @return ObjectTypeFinder
   */
  public static function any()
  {
    return new self();
  }

  /**
   * Specify type id.
   * @param int $id id of object type (for example if query is done to learn about object types params)
   * @return $this for method chaining
   */
  public function id($id)
  {
    return $this->ids([$id]);
  }

  /**
   * Specify ids. Overwrite previous selection.
   * @param array $ids ids of object types
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
   * @return $this for method chaining
   */
  public function name($name)
  {
    return $this->names([$name]);
  }

  /**
   * Specify names (NOT UNIQUE NAMES). Overwrites previous selection
   * @param array $names of objects (for example if query is done to learn about objects' params)
   * @return $this for method chaining
   */
  public function names(array $names)
  {
    $this->names = $names;
    return $this;
  }

  /**
   * Specify unique name (the one without spaces). Overwrites previous selection
   * @param string $uniqueName
   * @return $this for method chaining
   */
  public function uniqueName($uniqueName)
  {
    return $this->uniqueNames([$uniqueName]);
  }

  /**
   * Specify unique names (the one without spaces). Overwrites previous selection
   * @param array $uniqueNames of objects (for example if query is done to learn about objects' params)
   * @return $this for method chaining
   */
  public function uniqueNames(array $uniqueNames)
  {
    $this->uniqueNames = $uniqueNames;
    return $this;
  }

  /**
   * Specify id which should be omitted.
   * @param $noIds int ids to be excluded from the result set
   * @return $this for method chaining
   */
  public function exceptId($noIds)
  {
    return $this->exceptIds([$noIds]);
  }

  /**
   * Specify ids which should be omitted. Overwrite previous selection.
   * @param array $noIds ids of objects to be omitted
   * @return $this for method chaining
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
   * Specify required property. Overwrite previous selection.
   * @param string $name being property name in obj_properties
   * @return $this for method chaining
   */
  public function hasProperty($name)
  {
    return $this->hasProperties([$name]);
  }

  /**
   * Specify requirement of ALL required properties. Overwrite previous selection.
   * @param array $names being property name in obj_properties
   * @return $this for method chaining
   */
  public function hasProperties($names)
  {
    $this->properties = $names;
    return $this;
  }


  /**
   * Specify disallowed property. Overwrite previous selection.
   * @param string $name being property name in obj_properties
   * @return $this for method chaining
   */
  public function hasNotProperty($name)
  {
    return $this->hasNotProperties([$name]);
  }

  /**
   * Specify necessity of not having any of specified properties. Overwrite previous selection.
   * @param array $names being property name in obj_properties
   * @return $this for method chaining
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
   * Complete specifying the search conditions and perform search for object types matching the search conditions.
   * @return ObjectType[] of ObjectType class instances matching conditions or empty array if none found.
   */
  public function findAll()
  {
    return ObjectType::bulkLoadByIds($this->findIds());
  }

  /**
   * Complete specifying the search conditions and perform search for object matching the search conditions.
   * @return ObjectType|null random object matching conditions or null if none found
   */
  public function find()
  {
    $ids = $this->findIds();
    if (count($ids) > 0) {
      return ObjectType::loadById($ids[0]);
    }
    return null;
  }

  /**
   * Complete specifying the search conditions and perform search for object type ids matching the search conditions.
   * @return int[] of object ids. Empty if none found.
   */
  public function findIds()
  {
    $propertyCheckStr = "";
    if ($this->properties !== null) {

      foreach ($this->properties as $prop) {
        $propertyCheckStr .= " AND ot.id IN (SELECT op.objecttype_id FROM obj_properties op
        WHERE op.property_type = " . $this->db->quote($prop) . ")";
      }
    }

    $withoutPropertyCheckStr = "";
    if ($this->noProperties !== null) {
      $escapedProps = Pipe::from($this->noProperties)->map(function($prop) {
        return $this->db->quote($prop);
      })->toArray();

      $withoutPropertyCheckStr = " AND ot.id NOT IN (SELECT op.objecttype_id FROM obj_properties op
        WHERE op.property_type IN (" . implode(", ", $escapedProps) . "))";
    }

    $stm = $this->db->query("SELECT ot.id FROM objecttypes ot WHERE 1=1 "
      . FinderUtil::intListOrNothing("ot.id", $this->ids)
      . FinderUtil::exceptIntListOrNothing("ot.id", $this->noIds)
      . FinderUtil::stringListOrNothing("ot.name", $this->names)
      . FinderUtil::stringListOrNothing("ot.unique_name", $this->uniqueNames)
      . $propertyCheckStr
      . $withoutPropertyCheckStr
    );
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