<?php

$page = "server.statistics";
include "server.header.inc.php";

$db = Db::get();

$gameDate = GameDate::NOW();

function insertStatistics($type, $codeToNumber = [])
{
  $db = Db::get();
  $gameDate = GameDate::NOW();
  $stm = $db->prepare("INSERT INTO statistics (turn, date, type, code, statistic)
    VALUES (:day, NOW(), :type, :code, :number)");
  foreach ($codeToNumber as $code => $number) {
    $stm->bindInt("day", $gameDate->getDay());
    $stm->bindStr("type", $type);
    $stm->bindInt("code", $code);
    $stm->bindInt("number", $number);
    $stm->execute();
  }
}

function arrayToKeyValueAssoc(array $pairs, $keyName, $valueName)
{
  $keyValueAssoc = [];
  foreach ($pairs as $pair) {
    $keyValue = $pair->$keyName;
    $value = $pair->$valueName;
    $keyValueAssoc[$keyValue] = $value;
  }
  return $keyValueAssoc;
}


// number of players by language
$stm = $db->query("SELECT language, COUNT(*) AS number FROM players
  WHERE status IN (" . PlayerConstants::ACTIVE . ", " . PlayerConstants::APPROVED . ") GROUP BY language");
$codeToNumber = arrayToKeyValueAssoc($stm->fetchAll(), "language", "number");
insertStatistics("players_by_language", $codeToNumber);


// number of characters by language
$stm = $db->query("SELECT language, COUNT(*) AS number FROM chars WHERE status = " . CharacterConstants::CHAR_ACTIVE . " GROUP BY language");
$codeToNumber = arrayToKeyValueAssoc($stm->fetchAll(), "language", "number");
insertStatistics("chars_by_language", $codeToNumber);


// number of players count
$stm = $db->query("SELECT COUNT(*) AS number FROM players WHERE status IN (" . PlayerConstants::APPROVED . ", " . PlayerConstants::ACTIVE . ")");
$number = $stm->fetchColumn();
insertStatistics("nr_of_players", [0 => $number]);


// number of characters count
$stm = $db->query("SELECT COUNT(*) AS number FROM chars WHERE status = " . CharacterConstants::CHAR_ACTIVE);
$number = $stm->fetchColumn();
insertStatistics("nr_of_chars", [0 => $number]);


// number of players playing for over a year by language
$stm = $db->prepare("SELECT language, COUNT(*) AS number FROM players
  WHERE status IN (" . PlayerConstants::APPROVED . ", " . PlayerConstants::ACTIVE . ") AND register < :day - 365 GROUP BY language");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();

$codeToNumber = arrayToKeyValueAssoc($stm->fetchAll(), "language", "number");
insertStatistics("old_players_by_language", $codeToNumber);


// number of characters played for over a year by language
$stm = $db->prepare("SELECT language, count(*) AS number FROM chars WHERE status = 1 AND register < :day - 365 GROUP BY language");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();

$codeToNumber = arrayToKeyValueAssoc($stm->fetchAll(), "language", "number");
insertStatistics("old_chars_by_language", $codeToNumber);


// the number of characters played by a player playing for over a year by language
$stm = $db->prepare("SELECT c.language, COUNT(*) AS number FROM chars c INNER JOIN players p ON p.id = c.player
  WHERE c.status = " . CharacterConstants::CHAR_ACTIVE . " AND p.register < :day - 365 GROUP BY c.language");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();

$codeToNumber = arrayToKeyValueAssoc($stm->fetchAll(), "language", "number");
insertStatistics("chars_by_old_pl_by_language", $codeToNumber);


// the average value of genes for alive characters by skill type
$stm = $db->query("SELECT g.type AS skill, ROUND(AVG(g.value)) AS value FROM genes g
  INNER JOIN chars c ON c.id = g.person AND c.status = " . CharacterConstants::CHAR_ACTIVE . "  GROUP BY g.type");

$codeToNumber = arrayToKeyValueAssoc($stm->fetchAll(), "skill", "value");
insertStatistics("genes_by_type", $codeToNumber);


// the average value of states of alive characters by skill type
$stm = $db->query("SELECT s.type AS state, ROUND(AVG(s.value)) AS value FROM states s
  INNER JOIN chars c ON c.id = s.person AND c.status = " . CharacterConstants::CHAR_ACTIVE . " GROUP BY s.type");

$codeToNumber = arrayToKeyValueAssoc($stm->fetchAll(), "state", "value");
insertStatistics("states_by_type", $codeToNumber);


// number of buildings
$stm = $db->query("SELECT COUNT(*) FROM locations WHERE type = " . LocationConstants::TYPE_BUILDING . " AND expired_date = 0");
$number = $stm->fetchColumn();
insertStatistics("nr_of_buildings", [0 => $number]);


// number of engine-based vehicles
$stm = $db->query("SELECT COUNT(l.id) FROM locations l
  INNER JOIN objecttypes ot ON l.area = ot.id AND ot.rules LIKE '%engine%'
  WHERE l.type = " . LocationConstants::TYPE_VEHICLE . " AND l.expired_date = 0");
$number = $stm->fetchColumn();
insertStatistics("nr_of_engined_vehicles", [0 => $number]);


// total amount of steel in the game (unused, in piles)
$stm = $db->query("SELECT SUM(weight) FROM objects WHERE type = 2 AND typeid = 14
  AND (location > 0 OR person > 0 OR attached > 0)");
$number = $stm->fetchColumn();
insertStatistics("amount_of_steel", [0 => $number]);


// total amount of iron in the game (unused, in piles)
$stm = $db->query("SELECT SUM(weight) FROM objects WHERE type = 2 AND typeid = 10
  AND (location > 0 OR person > 0 OR attached > 0)");
$number = $stm->fetchColumn();
insertStatistics("amount_of_iron", [0 => $number]);


// total amount of bronze in the game (unused)
$stm = $db->query("SELECT SUM(weight) FROM objects WHERE type = 2 AND typeid = 267
  AND (location > 0 OR person > 0 OR attached > 0)");
$number = $stm->fetchColumn();
insertStatistics("amount_of_bronze", [0 => $number]);


// total amount of wood in the game (unused, in piles)
$stm = $db->query("SELECT SUM(weight) FROM objects WHERE type = 2 AND typeid = 8
  AND (location > 0 OR person > 0 OR attached > 0)");
$number = $stm->fetchColumn();
insertStatistics("amount_of_wood", [0 => $number]);


// total number of eating days for food in the game (how many characters could be fed in one day)
$stm = $db->query("SELECT SUM(weight / (10000 / rt.nutrition)) FROM objects o
    INNER JOIN rawtypes rt ON rt.id = o.typeid AND rt.nutrition > 0
  WHERE type = 2 AND (location > 0 OR person > 0 OR attached > 0)");
$number = $stm->fetchColumn();
insertStatistics("number_of_food_days", [0 => round($number)]);


// total number of sailing or floating vessels
$stm = $db->query("SELECT COUNT(id) FROM locations WHERE type = " . LocationConstants::TYPE_SAILING_SHIP);
$number = $stm->fetchColumn();
insertStatistics("nr_of_sailing_ships", [0 => $number]);


// total number of talking events in private
$stm = $db->prepare("SELECT COUNT(id) FROM events WHERE type = 3 AND day = :day - 1");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();
$number = $stm->fetchColumn();
insertStatistics("nr_of_priv_talking_events", [0 => $number]);


// total number of talking events in public
$stm = $db->prepare("SELECT COUNT(id) FROM events WHERE type = 5 AND day = :day - 1");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();
$number = $stm->fetchColumn();
insertStatistics("nr_of_publ_talking_events", [0 => $number]);


// total number of animal kills
$stm = $db->prepare("SELECT COUNT(id) FROM events WHERE type = 30 AND day = :day - 1");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();
$number = $stm->fetchColumn();
insertStatistics("nr_of_anim_kills", [0 => $number]);


// total number of kills by animals
$stm = $db->prepare("SELECT COUNT(id) FROM events WHERE type = 36 AND day = :day - 1");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();
$number = $stm->fetchColumn();
insertStatistics("nr_of_kill_by_anim", [0 => $number]);


// total number of animals
$stm = $db->query("SELECT SUM(number) FROM animals");
$number = $stm->fetchColumn();
insertStatistics("nr_of_animals", [0 => $number]);


// total number of kills by people
$stm = $db->prepare("SELECT COUNT(id) FROM events WHERE type = 49 AND day = :day - 1");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();
$number = $stm->fetchColumn();
insertStatistics("nr_of_kill_by_char", [0 => $number]);


// total number of successful lockpicks
$stm = $db->prepare("SELECT COUNT(id) FROM events WHERE type = 68 AND day = :day - 1");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();
$number = $stm->fetchColumn();
insertStatistics("nr_of_lockpick_success", [0 => $number]);


// total number of successful draggings of humans
$stm = $db->prepare("SELECT COUNT(id) FROM events WHERE type IN (148, 151) AND day = :day - 1");
$stm->bindInt("day", $gameDate->getDay());
$stm->execute();
$number = $stm->fetchColumn();
insertStatistics("nr_of_drag_success", [0 => $number]);

// Number of diseased by disease
$stm = $db->query("SELECT disease, COUNT(*) AS number FROM diseases GROUP BY disease");
$codeToNumber = arrayToKeyValueAssoc($stm->fetchAll(), "disease", "number");
insertStatistics("sick_chars", $codeToNumber);


// last visit in outside locations (type: 1)
$locs = array();
$stm = $db->query("SELECT DISTINCT root FROM char_on_loc_count WHERE number >= 1");
$locs = $stm->fetchScalars();

// floating ships without anybody on deck (people on such could be for example fishermen)
$stm = $db->query("SELECT s.vessel FROM sailing s WHERE s.speed > 0 OR
  ((SELECT COUNT(*) FROM chars c WHERE c.location = s.vessel AND c.status = 1) > 0)");
$locs = array_merge($locs, $stm->fetchScalars());

$stm = $db->prepare("INSERT INTO location_visits (location, amortized, last) VALUES (:loc, :day1, :day2)
  ON DUPLICATE KEY UPDATE amortized = LEAST(:day3, amortized + 20), last = :day4");
foreach ($locs as $locId) {
  $stm->bindInt("loc", $locId);
  $stm->bindInt("day1", $gameDate->getDay());
  $stm->bindInt("day2", $gameDate->getDay());
  $stm->bindInt("day3", $gameDate->getDay());
  $stm->bindInt("day4", $gameDate->getDay());
  $stm->execute();
}


// update last visit of buildings and ships to last visit of parent building. It can also affect ships in harbours
$stm = $db->prepare("UPDATE location_visits lh
    INNER JOIN locations bv ON bv.id = lh.location
      AND bv.type IN (" . LocationConstants::TYPE_BUILDING . ", " . LocationConstants::TYPE_VEHICLE . ")
    INNER JOIN location_visits lhp ON lhp.location = bv.region
  SET lh.amortized = GREATEST(lh.amortized, lhp.amortized), lh.last = GREATEST(lh.last, lhp.last)");

foreach (range(0, 3) as $i) {
  $stm->execute();
}


include "server/server.footer.inc.php";
