<?php


class Position
{

  var $corner;
  var $count_corners;

  private static $instance;
  /** @var Db */
  private $db;

  /**
   * Get instance - position is a singleton
   */
  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
      self::$instance->update();
    }
    return self::$instance;
  }

  private function __construct()
  {
    $this->db = Db::get();
  }

  private function update()
  {

    // READING ALL CORNER INFO

    $this->count_corners = 0;

    $stm = $this->db->query("SELECT * FROM corners");
    foreach ($stm->fetchAll() as $corner_info) {
      $this->corner[$corner_info->id][0] = $corner_info->x;
      $this->corner[$corner_info->id][1] = $corner_info->y;
      $this->corner[$corner_info->id][2] = $corner_info->next;
      $this->corner[$corner_info->id][3] = $corner_info->type;
      $this->corner[$corner_info->id][4] = $corner_info->changedir;

      $this->count_corners++;
    }
  }

  function check_areatype($x, $y)
  {
    $count_crossings_sea = 0;
    $count_crossings_lake = 0;

    for ($teller = 1; $teller <= $this->count_corners; $teller++) {

      $x1 = $this->corner[$teller][0];
      $y1 = $this->corner[$teller][1];
      $next = $this->corner[$teller][2];
      $type = $this->corner[$teller][3];
      $changedir = $this->corner[$teller][4];
      $x2 = $this->corner[$next][0];
      $y2 = $this->corner[$next][1];

      if ((($x1 < $x) and ($x2 > $x)) or (($x1 > $x) and ($x2 < $x))) {

        $cut = ((($x - $x2) * ($y1 - $y2)) / ($x1 - $x2)) + $y2;

        if ($cut < $y) {

          if ($type == 1) {

            $count_crossings_sea++;
          } else {

            $count_crossings_lake++;
          }
        }
      }

      if (($x1 == $x) and ($y1 < $y) and (!$changedir)) {

        if ($type == 1) {

          $count_crossings_sea++;
        } else {

          $count_crossings_lake++;
        }
      }
    }

    $result = "land";

    if (($count_crossings_sea / 2) == floor($count_crossings_sea / 2)) {
      $result = "sea";
    }

    if (($count_crossings_lake / 2) != floor($count_crossings_lake / 2)) {
      $result = "lake";
    }

    return $result;
  }

  public function find_dockable(Location $vessel, $x, $y, $watertype)
  {

    $vesselRules = $vessel->getObjectType()->getRules();

    $rules = Parser::rulesToArray($vesselRules);

    $result = [];
    if ($rules['dock']) {

      $dockArray = explode(",", $rules['dock']);
      $dockTypeIds = ObjectTypeFinder::any()->names($dockArray)->findIds();

      //Get all available targets

      $range = 30;
      $where = "d.x >= $x-$range AND d.x <= $x+$range AND d.y >= $y-$range AND d.y <= $y+$range";

      if (in_array('land', $dockArray)) {
        $landSelect = "
          UNION SELECT d.id, d.x, d.y, d.type, 20 AS dist
          FROM locations d
          WHERE type = 1 AND $where";

        if ($watertype == "sea" || $watertype == "lake") {
          $landSelect .= " AND borders_$watertype = 1";
        } else {
          if ($watertype == "sea and lake") {
            $landSelect .= " AND borders_sea = 1 AND borders_lake = 1";
          }
        } // todo - it's an obvious bug, but i'm not allowed to touch it
      }

      // Don't let dock to coastal harbor from lake
      if ($watertype == "lake") {
        $denied = " AND NOT (d.type = 2 AND d.area = 22)";
      }

      // Don't let dock to landing stage from sea
      if ($watertype == "sea") {
        $denied = " AND NOT (d.type = 2 AND d.area = 21)";
      }

      $stm = $this->db->prepareWithIntList("
        SELECT d.id, d.x, d.y, d.type, 30 AS dist
        FROM locations d
        WHERE type = 2 AND area IN (:dockTypeIds1) AND $where $denied
    
        UNION SELECT l.id, d.x, d.y, l.type, 30 AS dist
        FROM locations l
          INNER JOIN sailing d ON d.vessel = l.id
        WHERE type = 5 AND area IN (:dockTypeIds2) AND l.id != :ownLocationId AND $where
        
        $landSelect", [
        "dockTypeIds1" => $dockTypeIds,
        "dockTypeIds2" => $dockTypeIds,
      ]);
      $stm->bindInt("ownLocationId", $vessel->getId());
      $stm->execute();

      foreach ($stm->fetchAll() as $dock) {

        $dist = sqrt(pow($x - $dock->x, 2) + pow($y - $dock->y, 2));

        if ($dist <= $dock->dist) {
          $result[] = $dock->id;
        }
      }
    }

    return $result;
  }
}
