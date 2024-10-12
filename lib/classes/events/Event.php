<?php

class Event
{

  const RANGE_SAME_LOCATION = 0;
  const RANGE_NEAR_LOCATIONS = 1; // like chars in vehicle when event is in central area or other vehicle

  private $eventId; // type
  private $eventVars;
  private $nearLocations = false;


// temporary implementation
  private $type; // string indicating which loctype is it
// optional, only one of these
  private $location;
  private $x, $y, $radius, $allowCenter;
  private $width, $height;
  private $rectangles;
  private $char;
  private $chars;
  private $highlight = 0;

  private $excluded = array();

  public static function create($eventId, $eventVars)
  {
    return new Event($eventId, $eventVars);
  }

  private function __construct($eventId, $eventVars)
  {
    $this->eventId = $eventId;
    $this->eventVars = $eventVars;
  }

  public function inLocation($location)
  {
    $this->type = "location";
    $this->location = $location;
    return $this;
  }

  public function inRadius($x, $y, $radius, $allowCenter = true)
  {
    $this->type = "radius";
    $this->x = $x;
    $this->y = $y;
    $this->radius = $radius;
    $this->allowCenter = $allowCenter;
    return $this;
  }

  public function inRectangle($x, $y, $width, $height)
  {
    $this->type = "rectangle";
    $this->x = $x;
    $this->y = $y;
    $this->width = $width;
    $this->height = $height;
    return $this;
  }

  public function inRectangles(array $rectangles)
  {
    $this->type = "rectangles";
    $this->rectangles = $rectangles;
    return $this;
  }

  public function nearCharacter($char)
  {
    $this->type = "character";
    $this->char = $char;
    return $this;
  }

  public function forCharacter($char)
  {
    $this->type = "personal";
    $this->char = $char;
    return $this;
  }

  public function forCharacters(array $chars)
  {
    $this->type = "group";
    $this->chars = $chars;
    return $this;
  }

  public function andAdjacentLocations($too = true)
  {
    $this->nearLocations = !!$too;
    return $this;
  }

  /**
   * @param $excluded Character[]|Character a character of array of characters to be excluded
   * @return $this
   */
  public function except($excluded)
  {
    if ($excluded instanceof Character) {
      $excluded = array($excluded);
    }
    $this->excluded = $excluded;
    return $this;
  }

  public function highlight($highlight)
  {
    if ($highlight) {
      $this->highlight = 0;
    } else {
      $this->highlight = 1;
    }
    return $this;
  }

  private function isNearToConst()
  {
    return $this->nearLocations ? self::RANGE_NEAR_LOCATIONS : self::RANGE_SAME_LOCATION;
  }

  public function show()
  {
    $excluded = array_map(function($char) {
      return $char->getId();
    }, $this->excluded);
    switch ($this->type) {
      case "location":
        if ($this->location instanceof Location) {
          $this->location = $this->location->getId();
        }
        self::createEventInLocation($this->eventId, $this->eventVars,
          $this->location, $this->isNearToConst(), $excluded, $this->highlight);
        break;
      case "radius":
        self::createEventInRadius($this->eventId, $this->eventVars,
          $this->x, $this->y, $this->radius, $this->allowCenter, $this->highlight);
        break;
      case "rectangle":
        self::createEventInRectangle($this->eventId, $this->eventVars,
          $this->x, $this->y, $this->width, $this->height, $this->highlight);
        break;
      case "ractangles":
        self::createEventInRectangles($this->eventId, $this->eventVars,
          $this->rectangles, $this->highlight);
        break;
      case "character":
        $charId = $this->char;
        if ($this->char instanceof Character) {
          $charId = $this->char->getId();
        }
        self::createPublicEvent($this->eventId, $this->eventVars,
          $charId, $this->isNearToConst(), $excluded, $this->highlight);
        break;
      case "group":
        $chars = array_map(function($ch) {
          if ($ch instanceof Character) {
            return $ch->getId();
          } else {
            return $ch;
          }
        }, $this->chars);
        self::createPersonalEvent($this->eventId, $this->eventVars, $chars, $this->highlight);
        break;
      case "personal":
        $charId = $this->char;
        if ($this->char instanceof Character) {
          $charId = $this->char->getId();
        }
        self::createPersonalEvent($this->eventId, $this->eventVars, $charId, $this->highlight);
        break;
    }
  }

  /**
   * Function which creates an event of certain type and show it for all observers () except the chars listed in array
   * @arg event_id id of an event type (see "event_[ID]" in texts table)
   * @arg event_vars is string of values in format "NAME=value" which will be stored as additional data for event
   * @arg actor_char is id of character which is in location where action happened
   * @arg range one of RANGE const above. Event can be created for people in the same location or same and near locations
   * @arg excludedChars array of people who shouldn't get that event (i.e. actor shouldn't get observer's event). Can be null
   */
  public static function createPublicEvent($event_id, $event_vars, $actor_char, $range, array $excludedChars, $highlight = 0)
  {
    $db = Db::get();

    $id_watcher = self::createEvent($event_id, $event_vars);

    if ($range == self::RANGE_SAME_LOCATION) {
      $stm = $db->prepare("SELECT location FROM chars WHERE id = :charId");
      $stm->bindInt("charId", $actor_char);
      $char_location = $stm->executeScalar();

      $stm = $db->prepare("SELECT id FROM chars WHERE location = :locationId AND location != 0 AND status <= :active");
      $stm->bindInt("locationId", $char_location);
      $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
      $stm->execute();
      $chars = $stm->fetchScalars();
    } elseif ($range == self::RANGE_NEAR_LOCATIONS) {
      $actor_loc = new char_location($actor_char);
      $chars = $actor_loc->chars_near(_PEOPLE_NEAR);
    }

    if ($chars != null && $excludedChars != null) {
      $chars = array_values(array_diff($chars, $excludedChars));
    }

    self::createObservers($id_watcher, $chars, $highlight);
  }

  /**
   * Function which creates an event in a specified location
   * @arg event_id id of an event type (see "event_[ID]" in `texts` table)
   * @arg event_vars is string of values in format "NAME=value" which will be stored as additional data for event
   * @arg location chars in this location will see the event
   * @arg range one of RANGE const above. Currently only SAME_LOCATION can be used.
   * @arg excludedChars array of people who shouldn't get that event (i.e. actor shouldn't get observer's event). Can be null
   */
  public static function createEventInLocation($event_id, $event_vars, $location, $range, array $excludedChars = null, $highlight = 0)
  {
    if (!$location) throw new Exception("no location specified");
    $db = Db::get();
    if ($range == self::RANGE_SAME_LOCATION) {
      $stm = $db->prepare("SELECT id FROM chars WHERE location = :locationId AND status <= :active");
      $stm->bindInt("locationId", $location);
      $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
      $stm->execute();
      $chars = $stm->fetchScalars();
    } else {
      throw new Exception("bad range specification - only SAME_LOCATION allowed");
    }

    if ($chars != null && $excludedChars != null) {
      $chars = array_values(array_diff($chars, $excludedChars));
    }

    $id_watcher = self::createEvent($event_id, $event_vars);
    self::createObservers($id_watcher, $chars, $highlight);
  }

  /**
   * Function which creates an event for character or characters. DO NOT USE IT in a loop for all characters near, use createPublicEvent instead.
   */
  public static function createPersonalEvent($event_id, $event_vars, $char_ids, $highlight = 0)
  {
    if (!is_array($char_ids)) {
      $char_ids = array($char_ids);
    }
    $char_ids = array_values($char_ids);

    $id_actor = self::createEvent($event_id, $event_vars);
    self::createObservers($id_actor, $char_ids, $highlight);
  }

  /**
   * Creates event for characters in central areas and vehicles in distance <= radius.
   * Event message variables are enhanced by additional values of DISTANCE and (descriptive) ANGLE
   * Important! It can be slow
   */
  public static function createEventInRadius($event_id, $event_vars, $posX, $posY, $radius, $allowInCenter = true, $highlight = 0)
  {
    $posX = intval($posX);
    $posY = intval($posY);
    $radius = intval($radius);

    $locationsInRadius = array();

    $db = Db::get();
    // 1 - central area, 3 - ships & vehicles, 5 - sailing ships
    $stm = $db->prepare("SELECT GROUP_CONCAT(id) AS ids, x, y FROM locations WHERE type IN (1, 3, 5)
      AND x BETWEEN :minX AND :maxX
      AND y BETWEEN :minY AND :maxY GROUP BY x, y");
    $stm->bindInt("minX", $posX - $radius);
    $stm->bindInt("maxX", $posX + $radius);
    $stm->bindInt("minY", $posY - $radius);
    $stm->bindInt("maxY", $posY + $radius);
    $stm->execute();

    foreach ($stm->fetchAll() as $canBeNear) { // create list of locations <= distance in maximum metrics
      $distance = Measure::distance($posX, $posY, $canBeNear->x, $canBeNear->y);
      if ($distance <= $radius && ($allowInCenter || $distance != 0)) { // group of locations is in radius, optionally disallow center
        $locationsInRadius[] = array(
          "ids" => explode(",", $canBeNear->ids),
          "distance" => round($distance),
          "angle" => MapUtil::getDirectionTagName(getdirection($canBeNear->x, $canBeNear->y, $posX, $posY))
        );
      }
    }

    foreach ($locationsInRadius as $location) {
      $stm = $db->prepareWithIntList("SELECT id FROM chars WHERE location IN (:locationIds) AND status = :active", [
        "locationIds" => $location["ids"],
      ]);
      $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
      $stm->execute();
      $charsInLocation = $stm->fetchScalars();

      if (count($charsInLocation) > 0) { // if there's any observer
        $id_actor = self::createEvent($event_id, $event_vars . " DISTANCE=" . $location["distance"]
          . " DISTDESC=" . MapUtil::getDistanceTagName($location["distance"]) . " ANGLE=" . $location["angle"]);
        self::createObservers($id_actor, $charsInLocation, $highlight);
      }
    }
  }

  public static function createEventInRectangle($event_id, $event_vars, $posX, $posY, $width, $height, $highlight = 0)
  {
    $posX = intval($posX);
    $posY = intval($posY);
    $width = intval($width);
    $height = intval($height);

    self::createEventInRectangles($event_id, $event_vars, array(
      array(
        "x" => $posX, "y" => $posY, "width" => $width, "height" => $height
      )
    ), $highlight);
  }

  /**
   * @param array rectangles array of arrays with keys: x, y, width, height
   */
  public static function createEventInRectangles($event_id, $event_vars, $rectangles, $highlight = 0)
  {
    $db = Db::get();
    $rangeStrings = array();
    foreach ($rectangles as $rect) {

      $xRange = "(x BETWEEN " . $rect["x"] . " AND " . ($rect["x"] + $rect["width"]) . ")";
      if ($rect["x"] + $rect["width"] >= MapConstants::MAP_WIDTH) {
        $xRange .= " OR (x BETWEEN 0 AND " . (($rect["x"] + $rect["width"]) % MapConstants::MAP_WIDTH) . ")";
      }
      $yRange = "(y BETWEEN " . $rect["y"] . " AND " . ($rect["y"] + $rect["height"]) . ")";
      if ($rect["y"] + $rect["height"] >= MapConstants::MAP_HEIGHT) {
        $yRange .= " OR (y BETWEEN 0 AND " . (($rect["y"] + $rect["height"]) % MapConstants::MAP_HEIGHT) . ")";
      }
      $rangeStrings[] = "(($xRange) AND ($yRange))";
    }
    // 1 - central area, 3 - ships & vehicles, 5 - sailing ships
    $db = Db::get();
    $stm = $db->prepare("SELECT c.id FROM locations l
      INNER JOIN chars c ON c.location = l.id AND c.status = :active
      WHERE l.type IN (1, 3, 5) AND (" . implode(" OR ", $rangeStrings) . ")
      ");
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();

    $charsInLocations = $stm->fetchScalars();

    if (count($charsInLocations) > 0) {
      $id_actor = self::createEvent($event_id, $event_vars);
      self::createObservers($id_actor, $charsInLocations, $highlight);
    }
  }

  private static function createEvent($type, $parameters)
  {
    $gameDate = GameDate::NOW();
    $db = Db::get();
    $stm = $db->prepare("INSERT INTO events (day,hour,minute,type,parameters) VALUES (:day, :hour, :minute, :type, :parameters)");
    $stm->bindInt("day", $gameDate->getDay());
    $stm->bindInt("hour", $gameDate->getHour());
    $stm->bindInt("minute", $gameDate->getMinute());
    $stm->bindInt("type", $type);
    $stm->bindStr("parameters", $parameters);
    $stm->execute();
    return $db->lastInsertId();
  }


  private static function createObservers($eventid, $observers, $showtime = 0)
  {
    if (count($observers)) {
      $values = '';
      $obsCount = count($observers);
      for ($i = 0; $i < $obsCount; $i++) {
        $values .= " (" . $observers[$i] . ",$eventid)";

      }

      $values = str_replace(") (", "),(", $values);
      $db = Db::get();
      $db->query("INSERT INTO events_obs (observer, event) VALUES $values");
      $stm = $db->prepareWithIntList("SELECT person, sett.data, e.type FROM settings_chars sett, events e
        WHERE e.id = :eventId AND sett.person in (:charIds) AND sett.type = :type", [
        "charIds" => $observers,
      ]);
      $stm->bindInt("eventId", $eventid);
      $stm->bindInt("type", CharacterSettings::ACTIVITY_EVENT_FILTER);
      $stm->execute();

      if ($stm->rowCount() > 0) {
        $observers = array_flip($observers);
        while (list($pers, $sett_data, $event_type) = $stm->fetch(PDO::FETCH_NUM)) {
          $sett_data = explode(",", $sett_data);
          if (in_array($event_type, $sett_data)) {
            unset($observers[$pers]);
          }
        }
        $observers = array_flip($observers);
      }
      if (count($observers) > 0) {
        $stm = $db->prepareWithIntList("UPDATE newevents SET new = LEAST(new, :new) WHERE person IN (:observers)", [
          "observers" => $observers,
        ]);
        $stm->bindInt("new", $showtime);
        $stm->execute();
      }
    }
  }
}

