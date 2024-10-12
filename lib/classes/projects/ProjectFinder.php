<?php

class ProjectFinder
{

  private $location;
  private $types = [];
  private $subtypes = [];
  private $ids = [];
  /** @var Db */
  private $db;
  private $result;  

  private function __construct()
  {
    $this->db = Db::get();
  }

  /**
   * Search for projects in specified location
   * @param  int $locId id of location, must be > 0
   * @return ProjectFinder finder
   */
  public static function locatedIn($locId)
  {
    if (!Validation::isPositiveInt($locId)) {
      throw new InvalidArgumentException("$locId is not a positive integer!");
    }

    $finder = new ProjectFinder();
    $finder->location = intval($locId);
    return $finder;
  }

  /**
   * Specify type
   * @param  int $type positive value
   * @return ProjectFinder for method chaining
   */
  public function type($type)
  {
    return $this->types([$type]);
  }

  /**
   * Specify types. Overwrite previous selection.
   * @param  array $types of positive integers. Empty list = no condition
   * @return ProjectFinder for method chaining
   */
  public function types(array $types)
  {
    foreach ($types as $type) { // check
      if (!Validation::isPositiveInt($type)) {
        throw new InvalidArgumentException("type $type should be a positive int");
      }
    }
    $this->types = $types;
    return $this;
  }

  /**
   * Specify subtype.
   * @param  int $subtype subtype of project (different meaning for different project type)
   * @return ProjectFinder for method chaining
   */
  public function subtype($subtype)
  {
    return $this->subtypes([$subtype]);
  }

  /**
   * Specify subtypes. Overwrite previous selection.
   * @param int[] $subtypes array of subtypes of project (different meaning for different project type)
   * @return ProjectFinder for method chaining
   */
  public function subtypes(array $subtypes)
  {
    foreach ($subtypes as $subtype) { // check
      if (!Validation::isPositiveInt($subtype)) {
        throw new InvalidArgumentException("subtype $subtype should be a positive int");
      }
    }
    $this->subtypes = $subtypes;
    return $this;
  }

  /**
   * Specify id.
   * @param  int $id id of project (for example if query is done to learn about projects params)
   * @return $this for method chaining
   */
  public function id($id)
  {
    return $this->ids([$id]);
  }

  /**
   * Specify ids. Overwrite previous selection.
   * @param  array $ids of projects (for example if query is done to learn about projects params)
   * @return $this for method chaining
   */
  public function ids(array $ids)
  {
    foreach ($ids as $id) { // check
      if (!Validation::isPositiveInt($id)) {
        throw new InvalidArgumentException("id $id should be a positive int");
      }
    }
    $this->ids = $ids;
    return $this;
  }

  public function result($result)
  {
    $this->result = $result;
    return $this;
  }

  public function resultLike($result)
  {
    $this->result = "%" . $result . "%";
    return $this;
  }

  /**
   * Complete specifying of the search conditions and perform search for projects matching the search conditions.
   * @return Project[] instances matching conditions or empty if none found.
   */
  public function findAll()
  {
    return Pipe::from($this->findIds())->map(function($projectId) {
      return Project::loadById($projectId);
    })->toArray();
  }

  /**
   * Complete specifying of the search conditions and perform search for project matching the search conditions.
   * @return Project random project matching conditions or null if none found.
   */
  public function find()
  {
    $ids = $this->findIds();
    if (count($ids) > 0) {
      return Project::loadById($ids[0]);
    }
    return null;
  }

  /**
   * This function requires of the attribute $this->itemHasProperty to be previously set.
   * Searches for the project ids which resulting items have a property defined at the aformentioned attribute, returning
   * their ids.
   * @return array of project ids. Empty if none.
   */
  public function findProperties($propertyToSeek = null)
  {
    if(is_array($propertyToSeek))
    {
      $properties = implode("', '", $propertyToSeek);
    }
    else
    {
      $properties = $propertyToSeek;
    }

    $stm = $this->db->prepare
    (
      "SELECT id 
      FROM projects 
      WHERE location = :locationId
          AND subtype IN 
          ( SELECT objecttype_id 
          FROM obj_properties 
          WHERE property_type IN ('" . $properties . "')) "
          . (!empty($this->result) ? " AND result LIKE " . $this->db->quote($this->result) : "")
    );
    $stm->bindInt("locationId", $this->location);
    $stm->execute();
    return $stm->fetchScalars();
  }

  /**
   * Complete specifying of the search conditions and perform search for project ids matching the search conditions.
   * @return array of project ids. Empty if none found.
   */
  public function findIds()
  {
    $stm = $this->db->prepare("SELECT id FROM projects
      WHERE location = :locationId "
      . FinderUtil::intListOrNothing("type", $this->types)
      . FinderUtil::intListOrNothing("subtype", $this->subtypes)
      . FinderUtil::intListOrNothing("id", $this->ids)
      . (!empty($this->result) ? " AND result LIKE " . $this->db->quote($this->result) : "")
    );
    $stm->bindInt("locationId", $this->location);
    $stm->execute();
    return $stm->fetchScalars();
  }

  /**
   * Complete specifying the search conditions and get number of project matching the search conditions.
   * @return int number of projects fulfilling the conditions
   */
  public function count()
  {
    return count($this->findIds());
  }

  /**
   * Complete specifying the search conditions and get number of project matching the search conditions.
   * @return int number of projects with the property set for the search.
   */
  public function countProperties($propertyToSeek)
  {
    return count($this->findProperties($propertyToSeek));
  }

  /**
   * Complete specifying of the search conditions and check if any project matching the search conditions exists.
   * @return bool if any of such projects exists.
   */
  public function exists()
  {
    return $this->count() > 0;
  }
}

/**
 * NOTES:
 * 
 * 13/06/23 - Coderlotl
 * 
 * Added the attribute 'itemHasProperty' for searching for items with a specific property.
 * Added the functions 'hasProperty', 'hasProperties', 'findProperties', and 'countWithProperties'.
 */
