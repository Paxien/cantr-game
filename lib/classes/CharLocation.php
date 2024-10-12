<?php

class char_location
{
  public $name;
  public $nametag;
  private $character;
  public $id;
  public $region;
  public $istravelling = false;
  public $isbuilding = false;
  public $isvehicle = false;
  public $islocation = false;
  public $issailing = false;
  public $isflying = false;
  public $typeid;
  private $travel_info;
  public $location_info;
  /** @var Db */
  private $db;

  public function __construct($char_id, $db = null)
  {
    if ($db === null) {
      $db = Db::get();
    }
    $this->db = $db;

    if ($char_id != null) {
      $this->character = $char_id;
      $this->update_location();
    }
  }

  function chars_near($distance)
  {
    if ($this->istravelling) {
      return $this->charsNearOnRoad($distance);
    } else {
      $chars = [];
      $stm = $this->db->prepare("SELECT id FROM chars WHERE location = :locationId AND status = :active ORDER BY register"); // all chars in same location
      $stm->bindInt("locationId", $this->id);
      $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
      $stm->execute();
      foreach ($stm->fetchScalars() as $charId) {
        $chars[] = $charId;
      }
      if ($this->location_info->region != 0 && ($this->isvehicle || ($this->isbuilding && Window::hasOpenWindow($this->id)))) { // char is inside of b/v
        $mainid = $this->location_info->region;
        // Get all characters in outer location
        $stm = $this->db->prepare("SELECT id FROM chars WHERE location = :locationId AND status = :active ORDER BY register");
        $stm->bindInt("locationId", $mainid);
        $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
        $stm->execute();
        foreach ($stm->fetchScalars() as $charId) {
          $chars[] = $charId;
        }

        // Get all characters on attached buildings/vehicles
        $stm = $this->db->prepareWithIntList("SELECT loc.id, loc.type, obj.specifics, op.objecttype_id AS property_exists FROM locations loc
          LEFT JOIN objects obj ON obj.location = loc.id
          LEFT JOIN obj_properties op ON op.objecttype_id = obj.type AND op.property_type = 'EnableSeeingOutside'
          WHERE loc.region = :locationId AND loc.type IN (:locationTypes)", [
          "locationTypes" => [LocationConstants::TYPE_BUILDING, LocationConstants::TYPE_VEHICLE],
        ]); // b/v in that region
        $stm->bindInt("locationId", $this->id);
        $stm->execute();
        $loc_list = array();
        foreach ($stm->fetchAll() as $bv) { // all inner locations
          $isVehicle = $bv->type == LocationConstants::TYPE_VEHICLE;
          $isBuildingWithOpenWindow = $bv->type == LocationConstants::TYPE_BUILDING
            && $bv->property_exists && strpos($bv->specifics, "open") !== false;
          if ($isVehicle || $isBuildingWithOpenWindow) {
            $loc_list[] = $bv->id;
          }
        }

        if ($loc_list) {
          $stm = $this->db->prepareWithIntList("SELECT id FROM chars WHERE location IN (:ids) AND status = :active ORDER BY location, register", [
            "ids" => $loc_list,
          ]);
          $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
          $stm->execute();
          foreach ($stm->fetchScalars() as $charId) {
            $chars[] = $charId;
          }
        }
      } else {
        $mainid = $this->id;
      }

      // Get all characters on buildings/vehicles except own vehicle
      $stm = $this->db->prepareWithIntList("SELECT loc.id, loc.type, obj.specifics, op.objecttype_id AS property_exists
        FROM locations loc
        LEFT JOIN objects obj ON obj.location = loc.id
        LEFT JOIN obj_properties op ON op.objecttype_id = obj.type AND op.property_type = 'EnableSeeingOutside'
        WHERE loc.region = :region AND loc.type IN (:locationTypes) AND loc.id != :ownLocationId", [
          "locationTypes" => [LocationConstants::TYPE_BUILDING, LocationConstants::TYPE_VEHICLE],
      ]);
      $stm->bindInt("region", $mainid);
      $stm->bindInt("ownLocationId", $this->id);
      $stm->execute();
      $loc_list = array();
      foreach ($stm->fetchAll() as $bv) { // all inner locations
        $isVehicle = $bv->type == LocationConstants::TYPE_VEHICLE;
        $isBuildingWithOpenWindow = $bv->type == LocationConstants::TYPE_BUILDING
          && $bv->property_exists && strpos($bv->specifics, "open") !== false;
        if ($isVehicle || $isBuildingWithOpenWindow) {
          $loc_list[] = $bv->id;
        }
      }

      if ($loc_list) {
        $stm = $this->db->prepareWithIntList("SELECT id FROM chars WHERE location IN (:ids) AND status = :active ORDER BY location, register", [
          "ids" => $loc_list,
        ]);
        $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
        $stm->execute();
        foreach ($stm->fetchScalars() as $charId) {
          $chars[] = $charId;
        }
      }
      return $chars;
    }
  }


  function char_isnear($character2, $distance = _PEOPLE_NEAR, $strict = false)
  {
    $stm = $this->db->prepare("SELECT status FROM chars WHERE id = :charId LIMIT 1");
    $stm->bindInt("charId", $character2);
    $char_alive = $stm->executeScalar();
    if ($char_alive != CharacterConstants::CHAR_ACTIVE) {
      return false;
    }
    if ($this->istravelling) {
      $charsNearOnRoad = $this->charsNearOnRoad($distance);
      return in_array($character2, $charsNearOnRoad);
    } else { // is not travelling

      $stm = $this->db->prepare("SELECT location FROM chars WHERE id = :charId");
      $stm->bindInt("charId", $character2);
      $loc = $stm->executeScalar();

      if ($loc == $this->id) {
        return true;
      }

      $ochar_loc = new char_location($character2);

      $this_building_with_window = !$strict && $this->isbuilding && Window::hasOpenWindow($this->location_info->id);
      $ochar_building_with_window = !$strict && $ochar_loc->isbuilding && Window::hasOpenWindow($ochar_loc->location_info->id);
      // people are not in the same location
      if ($this->isvehicle || $ochar_loc->isvehicle // at least one in vehicle
        || ($this_building_with_window) // char1's building has open window
        || ($ochar_building_with_window) // char2's building has open window
      ) {

        // char2 is outside of this
        if (($this->location_info->region == $loc && $this->location_info->region != 0 && ($this->isvehicle || $this_building_with_window)) || // char1 is inside of b/v and char2 outside
          ($ochar_loc->location_info->region == $this->id && $ochar_loc->location_info->region != 0 && ($ochar_loc->isvehicle || $ochar_building_with_window))
        ) { // char2 is inside of b/v and char1 outside
          return true;
        }

        if (($ochar_loc->isvehicle || $ochar_building_with_window) && // char1 in b/v
          ($this->isvehicle || $this_building_with_window) && // char2 in b/v
          ($ochar_loc->location_info->region == $this->location_info->region && $this->location_info->region != 0) // char1's and char2's parent location is the same and isn't 0 (it would mean separate ships)
        ) {
          return true;
        }
      }

      return false;
    }
  }

  private function update_location()
  {
    $stm = $this->db->prepare("SELECT location FROM chars WHERE id = :charId");
    $stm->bindInt("charId", $this->character);
    $this->id = $stm->executeScalar();

    if ($this->id) {
      $this->update_location_given_id();
    } else {
      $this->update_location_travelling();
    }
  }

  private function update_location_given_id()
  {
    $this->nametag = "<CANTR LOCNAME ID=$this->id>";

    $stm = $this->db->prepare("SELECT * FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $this->id);
    $stm->execute();
    $location_info = $stm->fetchObject();
    $this->location_info = clone $location_info;

    if ($location_info->type == 1) {
      $this->islocation = true;
    }
    if ($location_info->type == 2) {
      $this->isbuilding = true;
    }
    if ($location_info->type == 3) {
      $this->isvehicle = true;
    }
    if ($location_info->type == 5) {
      $this->isvehicle = true;
      $this->issailing = true;
    }
    if ($location_info->type == 6) {
      $this->isvehicle = true;
    }

    $this->typeid = $location_info->area;
    $this->region = $location_info->region;

    if ($location_info->type != 1) {

      $root_location_info = clone $location_info;

      while (($root_location_info->type != 1) and ($root_location_info->region != 0)) {
        $stm = $this->db->prepare("SELECT * FROM locations WHERE id = :id");
        $stm->bindInt("id", $root_location_info->region);
        $stm->execute();
        $root_location_info = $stm->fetchObject();
      }

      if ($root_location_info->region) {

        $this->name = "<CANTR LOCNAME ID=$root_location_info->id> (<CANTR LOCNAME ID=$location_info->id>)";
      } else {

        if ((!$this->issailing) and ($root_location_info->type != 5)) {
          $stm = $this->db->prepare("SELECT * FROM travels WHERE person = :person AND type != 0");
          $stm->bindInt("person", $root_location_info->id);
          $stm->execute();

          $this->travel_info = $stm->fetchObject();

          $temp_locname = "<CANTR LOCNAME ID=$root_location_info->id>";

          $this->name = "$temp_locname<span style='color:#888;'>, <CANTR REPLACE NAME=char_travel_description FROM={$this->travel_info->locfrom} TO={$this->travel_info->locdest}></span>";

          $this->istravelling = true;
        } else {
          if ($root_location_info->id == $location_info->id) {
            $this->name = "<CANTR LOCNAME ID=$root_location_info->id>";
          } else {
            $this->name = "<CANTR LOCNAME ID=$root_location_info->id> (<CANTR LOCNAME ID=$location_info->id>)";
          }
        }
      }
    } else {
      $this->name = "<CANTR LOCNAME ID=$location_info->id>";
    }
  }

  function update_location_travelling()
  {
    $stm = $this->db->prepare("SELECT * FROM travels WHERE person = :person AND type = 0");
    $stm->bindInt("person", $this->character);
    $stm->execute();

    $this->travel_info = $stm->fetchObject();

    $this->name = "<span style='color:#888;'><CANTR REPLACE NAME=char_travel_description FROM={$this->travel_info->locfrom} TO={$this->travel_info->locdest}></span>";

    $this->istravelling = true;
  }

  /**
   * @param $distance
   * @return array
   */
  private function charsNearOnRoad($distance)
  {
    $trav_info = $this->travel_info;

    $stm = $this->db->prepare("SELECT `start`,`end`,`length` FROM connections WHERE id = :connectionId");
    $stm->bindInt("connectionId", $trav_info->connection);
    $stm->execute();
    $conn_info = $stm->fetchObject();

    if ($trav_info->locfrom != $conn_info->start) {
      $pos = $conn_info->length - ($trav_info->travleft / $trav_info->travneeded * $conn_info->length);
    } else {
      $pos = $trav_info->travleft / $trav_info->travneeded * $conn_info->length;
    }

    $start = $pos - $distance;
    if ($start < 0) {
      $start = 0;
    }
    $end = $pos + $distance;
    if ($end > $conn_info->length) {
      $end = $conn_info->length;
    }
    $start = round($start, 6);
    $end = round($end, 6);

    $chars = [];

    $stm = $this->db->prepare("SELECT person, locfrom, locdest, travleft, travneeded, type FROM travels WHERE `connection` = :connectionId AND person != 0");
    $stm->bindInt("connectionId", $trav_info->connection);
    $stm->execute();
    foreach ($stm->fetchAll() as $char_trav) {
      if ($char_trav->locfrom != $conn_info->start) {
        $pos = $conn_info->length - ($char_trav->travleft / $char_trav->travneeded * $conn_info->length);
      } else {
        $pos = $char_trav->travleft / $char_trav->travneeded * $conn_info->length;
      }

      $pos = round($pos, 6);
      if (($pos >= $start) and ($pos <= $end)) {

        if ($char_trav->type == 0) { // Character is walking
          $chars[] = $char_trav->person;
        } else { // Character is using a vehicle
          $stm = $this->db->prepare("SELECT id FROM chars WHERE location = :locationId AND status = :active");
          $stm->bindInt("locationId", $char_trav->person);
          $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
          $stm->execute();
          foreach ($stm->fetchScalars() as $charId) {
            $chars[] = $charId;
          }
        }
      }
    }
    return $chars;
  }
}
