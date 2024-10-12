<?php

class VisibleMarkersFinder
{

  /** @var int */
  private $x;

  /** @var int */
  private $y;

  /** @var Character */
  private $observer;

  const MAX_VISIBLE_DISTANCE = 200;
  const MAX_LIGHTHOUSE_DISTANCE = 200;
  const LIGHTHOUSE_SHOW_NAME_DISTANCE = 60;
  /** @var Db */
  private $db;

  public function __construct($x, $y, Character $observer)
  {
    $this->x = intval($x);
    $this->y = intval($y);
    $this->observer = $observer;
    $this->db = Db::get();
  }

  // Display all visible Lighthouses
  private function getLighthousesNear($isOnSea)
  {
    if (!$isOnSea) {
      return [];
    }
    $near = self::MAX_LIGHTHOUSE_DISTANCE;

    $lighthouses = [];
    $stm = $this->db->prepare("SELECT l.id, l.x, l.y, l.name, IF (p.id IS NOT NULL, 1, 0) AS is_fueled FROM locations l
    LEFT JOIN projects p ON p.location = l.id
      AND p.type = :type
        AND p.subtype = 120 AND p.automatic = 1 AND p.turnsleft != p.turnsneeded
    WHERE area IN (SELECT op.objecttype_id FROM obj_properties op WHERE op.property_type = 'Lighthouse')
      AND x BETWEEN :x1 - :near1 AND :x2 + :near2
      AND y BETWEEN :y1 - :near3 AND :y2 + :near4 GROUP BY l.id"); // rawtype 120 = charcoal
    $stm->bindInt("type", ProjectConstants::TYPE_GATHERING);
    $stm->bindInt("x1", $this->x);
    $stm->bindInt("x2", $this->x);
    $stm->bindInt("y1", $this->y);
    $stm->bindInt("y2", $this->y);
    $stm->bindInt("near1", $near);
    $stm->bindInt("near2", $near);
    $stm->bindInt("near3", $near);
    $stm->bindInt("near4", $near);
    $stm->execute();
    foreach ($stm->fetchAll() as $lighthouse_info) {
      $distance = Measure::distance($this->x, $this->y, $lighthouse_info->x, $lighthouse_info->y);
      $degree = Measure::direction($this->x, $this->y, $lighthouse_info->x, $lighthouse_info->y);
      if ($distance <= self::LIGHTHOUSE_SHOW_NAME_DISTANCE) {
        $tag = TagBuilder::forLocation($lighthouse_info->id)->observedBy($this->observer)->allowHtml(true)->build();
        $lighthouse_info->name = $tag->interpret();
        $lighthouses[] = new VisibleMarker(Location::loadById($lighthouse_info->id),
          $degree, true, $distance, null, true
        );
      } elseif ($distance <= $near && $lighthouse_info->is_fueled) {
        $lighthouses[] = new VisibleMarker(Location::loadById($lighthouse_info->id),
          $degree, true, $distance, null, false
        );
      }
    }

    return $lighthouses;
  }

  public function getThingsVisibleFromDistance($fovMultiplier, $locationId, Character $observer, $isOnSea)
  {
    return array_merge(
      $this->getObjectsVisibleFromDistance($fovMultiplier, $isOnSea),
      $this->getLocationsVisibleFromDistance($fovMultiplier, $locationId, $isOnSea)
    );
  }

  public function getObjectsVisibleFromDistance($fovMultiplier, $isOnSea)
  {
    $MAX_RANGE = self::MAX_VISIBLE_DISTANCE * $fovMultiplier;

    $stm = $this->db->prepare("SELECT o.id AS obj_id, l.x, l.y
      FROM locations l
      INNER JOIN objects o ON o.location = l.id
       AND o.type IN (SELECT op.objecttype_id
         FROM obj_properties op
         WHERE op.property_type = 'VisibleFromDistance')
       AND (:isOnSea OR o.type NOT IN (SELECT op.objecttype_id
         FROM obj_properties op
         WHERE op.property_type = 'VisibleOnlyFromWater')) 
      WHERE l.type = :outside
        AND l.x BETWEEN :x1 - :maxRange1 AND :x2 + :maxRange2
        AND l.y BETWEEN :y1 - :maxRange3 AND :y2 + :maxRange4");
    $stm->bindBool("isOnSea", $isOnSea);
    $stm->bindInt("outside", LocationConstants::TYPE_OUTSIDE);
    $stm->bindInt("x1", $this->x);
    $stm->bindInt("x2", $this->x);
    $stm->bindInt("y1", $this->y);
    $stm->bindInt("y2", $this->y);
    $stm->bindFloat("maxRange1", $MAX_RANGE);
    $stm->bindFloat("maxRange2", $MAX_RANGE);
    $stm->bindFloat("maxRange3", $MAX_RANGE);
    $stm->bindFloat("maxRange4", $MAX_RANGE);
    $stm->execute();

    $objectsVisibleFromDistance = [];
    foreach ($stm->fetchAll() as $visibleMarker) {
      $visibleObject = CObject::loadById($visibleMarker->obj_id);
      $visibilityProp = $visibleObject->getProperty("VisibleFromDistance");
      $distance = Measure::distance($this->x, $this->y, $visibleMarker->x, $visibleMarker->y);
      if ($distance * $fovMultiplier <= $visibilityProp["distance"]) {
        $direction = Measure::direction($this->x, $this->y, $visibleMarker->x, $visibleMarker->y);
        $showDetailedName = array_key_exists("maxDistanceToSeeDetails", $visibilityProp) ?
          $distance <= $visibilityProp["maxDistanceToSeeDetails"] : true;
        $showExactDirection = array_key_exists("exactDirection", $visibilityProp) ? $visibilityProp["exactDirection"] : false;
        $objectsVisibleFromDistance[] = new VisibleMarker($visibleObject, $direction,
          $showExactDirection, $distance, null, $showDetailedName);
      }
    }

    return $objectsVisibleFromDistance;
  }

  private function getLocationsVisibleFromDistance($fovMultiplier, $locationId, $isOnSea)
  {
    $locationsVisibleFromDistance = $this->getLighthousesNear($isOnSea);

    if ($isOnSea) {
      foreach ($this->getCoastalLocations() as $coastalLocation) {
        $direction = Measure::direction($this->x, $this->y, $coastalLocation->getX(), $coastalLocation->getY());
        $distance = Measure::distance($this->x, $this->y, $coastalLocation->getX(), $coastalLocation->getY());
        $locationsVisibleFromDistance[] = new VisibleMarker($coastalLocation, $direction, false,
          $distance, null, true);
      }
    }

    foreach ($this->getVisibleLocationsBasedOnProperty($fovMultiplier, $locationId, $isOnSea) as $marker) {
      $locationsVisibleFromDistance[] = $marker;
    }

    return $locationsVisibleFromDistance;
  }

  /**
   * @param $fovMultiplier float
   * @param $locationId int
   * @param $isOnSea bool
   * @return VisibleMarker[]
   */
  private function getVisibleLocationsBasedOnProperty($fovMultiplier, $locationId, $isOnSea)
  {
    $MAX_RANGE = self::MAX_VISIBLE_DISTANCE * $fovMultiplier;

    $visibleLocationsFinder = LocationFinder::nearPosition($this->x, $this->y)
      ->inRange($MAX_RANGE)
      ->types([LocationConstants::TYPE_BUILDING, LocationConstants::TYPE_SAILING_SHIP])
      ->exceptId($locationId)
      ->hasProperty("VisibleFromDistance");

    if (!$isOnSea) {
      $visibleLocationsFinder->hasNotProperty("VisibleOnlyFromWater");
    }

    $foundLocations = [];
    /** @var Location $location */
    foreach ($visibleLocationsFinder->findAll() as $location) {
      $visibilityProp = $location->getProperty("VisibleFromDistance");
      $distance = Measure::distance($this->x, $this->y, $location->getX(), $location->getY());

      if ($distance <= $visibilityProp["distance"] * $fovMultiplier) {
        $direction = Measure::direction($this->x, $this->y, $location->getX(), $location->getY());
        $showDetailedName = array_key_exists("maxDistanceToSeeDetails", $visibilityProp) ?
          $distance <= $visibilityProp["maxDistanceToSeeDetails"] : true;

        $showExactDirection = array_key_exists("exactDirection", $visibilityProp) ? $visibilityProp["exactDirection"] : false;
        $foundLocations[] = new VisibleMarker($location, $direction,
          $showExactDirection, $distance, null, $showDetailedName);
      }
    }
    return $foundLocations;
  }

  private function getCoastalLocations()
  {
    $COASTAL_LOCATION_VISIBILITY_RANGE = 20;
    return LocationFinder::nearPosition($this->x, $this->y)
      ->type(LocationConstants::TYPE_OUTSIDE)
      ->inRange($COASTAL_LOCATION_VISIBILITY_RANGE)
      ->bordersWater(true)
      ->findAll();
  }
}