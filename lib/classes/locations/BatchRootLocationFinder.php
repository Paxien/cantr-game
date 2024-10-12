<?php

class BatchRootLocationFinder
{
  /** @var Db */
  private $db;

  private $parentByLocation = null;

  public function __construct(Db $db)
  {
    $this->db = $db;
  }

  private function precompute()
  {
    $parentByLocation = [];
    $stm = $this->db->query("SELECT id, type, region FROM locations");

    foreach ($stm->fetchAll() as $loc) {
      if ($loc->type == LocationConstants::TYPE_OUTSIDE || $loc->region < 0) {
        $parentByLocation[$loc->id] = 0;
      } else {
        $parentByLocation[$loc->id] = $loc->region;
      }
    }
    return $parentByLocation;
  }

  /**
   * @param Location|int $location or id of location
   * @return int id of location being the root
   */
  public function getRoot($location)
  {
    if ($location instanceof Location) {
      $location = $location->getId();
    }

    if ($this->parentByLocation === null) {
      $this->parentByLocation = $this->precompute();
    }

    $root = $location;
    while ($this->parentByLocation[$root] > 0) {
      $root = $this->parentByLocation[$root];
    }
    return $root;
  }
}
