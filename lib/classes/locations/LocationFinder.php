<?php

class LocationFinder
{

  private $x = null;
  private $y = null;
  private $types = [];
  private $subtypes = [];
  private $regions = [];
  private $properties = null;
  private $noProperties = null;
  private $maxRange = null;

  private $noIds = [];

  private $bordersSea = null;
  private $bordersLake = null;
  private $bordersWater = null;
  /** @var Db */
  private $db;

  public static function nearPosition($x, $y)
  {
    if (!Validation::inRange($x, [0, MapConstants::MAP_WIDTH])
      || !Validation::inRange($y, [0, MapConstants::MAP_HEIGHT])
    ) {
      throw new InvalidArgumentException("position [$x, $y] doesn't exist on Cantr map");
    }

    $finder = new self();
    $finder->x = intval($x);
    $finder->y = intval($y);
    return $finder;
  }

  public static function any()
  {
    return new self();
  }

  public function __construct()
  {
    $this->db = Db::get();
  }

  /**
   * Specify type
   * @param  int $type positive value
   * @return $this for method chaining
   */
  public function type($type)
  {
    return $this->types([$type]);
  }

  /**
   * Specify types. Overwrite previous selection.
   * @param  int[] $types of positive integers. Empty list = no condition
   * @return $this for method chaining
   */
  public function types(array $types)
  {
    if (!Validation::isPositiveIntArray($types)) {
      throw new InvalidArgumentException("one of types in [" . implode(", ", $types) . "] is not a positive int");
    }
    $this->types = $types;
    return $this;
  }

  /**
   * Specify subtype (a.k.a. "area")
   * @param  int $subtype positive value
   * @return $this for method chaining
   */
  public function subtype($subtype)
  {
    return $this->subtypes([$subtype]);
  }

  /**
   * Specify subtypes (a.k.a. "area"). Overwrite previous selection.
   * @param  int[] $subtypes of positive integers. Empty list = no condition
   * @return $this for method chaining
   */
  public function subtypes(array $subtypes)
  {
    if (!Validation::isPositiveIntArray($subtypes)) {
      throw new InvalidArgumentException("one of types in [" . implode(", ", $subtypes) . "] is not a positive int");
    }
    $this->subtypes = $subtypes;
    return $this;
  }

  /**
   * Specify region (parent location). Remember it has different meaning for outside locations, so always specify location type.
   * @param  int $region positive value
   * @return $this for method chaining
   */
  public function region($region)
  {
    return $this->regions([$region]);
  }

  /**
   * Specify regions (parent locations). Overwrite previous selection. Remember it has different meaning for outside locations, so always specify location type.
   * @param  int[] $regions of positive integers. Empty list = no condition
   * @return $this for method chaining
   */
  public function regions(array $regions)
  {
    if (!Validation::isPositiveIntArray($regions)) {
      throw new InvalidArgumentException("one of regions in [" . implode(", ", $regions) . "] is not a positive int");
    }
    $this->regions = $regions;
    return $this;
  }

  /**
   * Specify required property. Overwrite previous selection.
   * When you use it, remember to disable it for locations of type = 1 (outside), because it results in undefined behaviour.
   * @param  string $name being type name in obj_properties
   * @return LocationFinder for method chaining
   */
  public function hasProperty($name)
  {
    return $this->hasProperties([$name]);
  }

  /**
   * Specify requirement of ALL of properties. Overwrite previous selection.
   * When you use it, remember to disable it for locations of type = 1 (outside), because it results in undefined behaviour.
   * @param  string[] $names being type name in obj_properties
   * @return LocationFinder for method chaining
   */
  public function hasProperties($names)
  {
    $this->properties = $names;
    return $this;
  }


  /**
   * Specify a disallowed property. Overwrite previous selection.
   * @param  string $name being type name in obj_properties
   * @return LocationFinder for method chaining
   */
  public function hasNotProperty($name)
  {
    return $this->hasNotProperties([$name]);
  }

  /**
   * Specify necessity of having none of these properties. Overwrite previous selection.
   * @param  string[] $names being type name in obj_properties
   * @return LocationFinder for method chaining
   */
  public function hasNotProperties($names)
  {
    $this->noProperties = $names;
    return $this;
  }

  public function bordersSea($borders = true)
  {
    $this->bordersSea = !!$borders;
    return $this;
  }

  public function bordersLake($borders = true)
  {
    $this->bordersLake = !!$borders;
    return $this;
  }

  public function bordersWater($borders = true)
  {
    $this->bordersWater = !!$borders;
    return $this;
  }

  public function inRange($range)
  {
    if (!is_numeric($range) || $range < 0) {
      throw new InvalidArgumentException("range for LocationFinder should be non-negative float, is: $range");
    }
    $this->maxRange = $range;
    return $this;
  }

  /**
   * Specify id which should be omitted.
   * @param $noIds int ids to be excluded from result set
   * @return LocationFinder for method chaining
   */
  public function exceptId($noIds)
  {
    return $this->exceptIds([$noIds]);
  }

  /**
   * Specify ids which should be omitted. Overwrite previous selection.
   * @param  array $noIds ids of objects to be omitted
   * @return LocationFinder for method chaining
   */
  public function exceptIds(array $noIds)
  {
    if (!Validation::isNonNegativeIntArray($noIds)) {
      throw new InvalidArgumentException("array should contain non-negative integers");
    }
    $this->noIds = $noIds;
    return $this;
  }

  //
  // Execute search - makes a query or queries to the database
  //

  /**
   * @return Location the nearest location on the map
   * @throws IllegalStateException when x or y is unspecified, so it's impossible to find it
   */
  public function findNearest()
  {
    if ($this->x === null || $this->y === null) {
      throw new IllegalStateException("It's impossible to find the nearest location" .
        " when origin is not defined");
    }

    $conditions = FinderUtil::intListOrNothing("type", $this->types);
    $conditions .= FinderUtil::boolOrNothing("borders_sea", $this->bordersSea);
    $conditions .= FinderUtil::boolOrNothing("borders_lake", $this->bordersLake);
    if ($this->bordersWater !== null) {
      $optionalNot = $this->bordersWater ? "" : "NOT";
      $conditions .= "AND " . $optionalNot . " (borders_lake = 1 OR borders_sea = 1)";
    }

    // find the smallest distance considering wrapping around edges of the map
    $stm = $this->db->prepare("SELECT id,
      SQRT(
        POW(LEAST(:worldWidth - x + :x1, CAST(x AS SIGNED) - :x2), 2)
          +
        POW(LEAST(:worldHeight - y + :y1, CAST(y AS SIGNED) - :y2), 2)
      )
        AS distance
      FROM locations WHERE expired_date = 0 $conditions ORDER BY distance ASC LIMIT 1
    ");
    $stm->bindInt("worldWidth", MapConstants::MAP_WIDTH);
    $stm->bindInt("worldHeight", MapConstants::MAP_HEIGHT);
    $stm->bindInt("x1", $this->x);
    $stm->bindInt("x2", $this->x);
    $stm->bindInt("y1", $this->y);
    $stm->bindInt("y2", $this->y);
    $nearestId = $stm->executeScalar();

    return Location::loadById($nearestId);
  }


  /**
   * Complete specifying of the search conditions and perform search for locations matching the search conditions.
   * @return Location[] instances matching conditions or empty if none found.
   */
  public function findAll()
  {
    return Pipe::from($this->findIds())->map(function($locId) {
      return Location::loadById($locId);
    })->toArray();
  }

  /**
   * Complete specifying of the search conditions and perform search for locations matching the search conditions.
   * @return Location random location matching conditions or null if none found.
   */
  public function find()
  {
    $ids = $this->findIds();
    if (count($ids) > 0) {
      return Location::loadById($ids[0]);
    }
    return null;
  }

  /**
   * Complete specifying of the search conditions and perform search for location ids matching the search conditions.
   * @return int[] Empty if none found.
   */
  public function findIds()
  {
    $waterConditions = FinderUtil::boolOrNothing("borders_sea", $this->bordersSea);
    $waterConditions .= FinderUtil::boolOrNothing("borders_lake", $this->bordersLake);
    if ($this->bordersWater !== null) {
      $optionalNot = $this->bordersWater ? "" : "NOT";
      $waterConditions .= "AND " . $optionalNot . " (borders_lake = 1 OR borders_sea = 1)";
    }

    $rangeCondition = "";
    if ($this->maxRange !== null) {
      $rangeCondition = " AND
      SQRT(POW(LEAST(" . MapConstants::MAP_WIDTH . " - x + $this->x, CAST(x AS SIGNED) - $this->x), 2))
        +
      SQRT(POW(LEAST(" . MapConstants::MAP_HEIGHT . " - y + $this->y, CAST(y AS SIGNED) - $this->y), 2))
        <= " . $this->maxRange;
    }

    $propertyCheckStr = "";
    if ($this->properties !== null) {

      foreach ($this->properties as $prop) {
        $propertyCheckStr .= " AND l.area IN (SELECT op.objecttype_id FROM obj_properties op
        WHERE op.property_type = " . $this->db->quote($prop) . ")";
      }
    }

    $withoutPropertyCheckStr = "";
    if ($this->noProperties !== null) {
      $escapedProps = Pipe::from($this->noProperties)->map(function($prop) {
        return $this->db->quote($prop);
      })->toArray();

      $withoutPropertyCheckStr = " AND l.area NOT IN (SELECT op.objecttype_id FROM obj_properties op
        WHERE op.property_type IN (" . implode(", ", $escapedProps) . "))";
    }

    $stm = $this->db->query("SELECT l.id FROM locations l WHERE 1=1 "
      . FinderUtil::intListOrNothing("l.type", $this->types)
      . FinderUtil::exceptIntListOrNothing("l.id", $this->noIds)
      . FinderUtil::intListOrNothing("l.area", $this->subtypes)
      . FinderUtil::intListOrNothing("l.region", $this->regions)
      . $propertyCheckStr
      . $withoutPropertyCheckStr
      . $waterConditions
      . $rangeCondition
    );

    return $stm->fetchScalars();
  }

  /**
   * Complete specifying the search conditions and get number of locations matching the search conditions.
   * @return int number of locations fulfilling the conditions
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
