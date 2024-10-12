<?php


error_reporting(E_ALL);
$page = "convert_custom_event_actions";
include("../../lib/stddef.inc.php");


$db = Db::get();

$stm = $db->query("SELECT id FROM locations WHERE type = " . LocationConstants::TYPE_OUTSIDE);
$stm->execute();
$locationsOutside = $stm->fetchScalars();

$visitedLocations = [];

$toVisit = $locationsOutside;

$i = 0;

while (count($toVisit) > 0) {

  $toVisit = array_filter($toVisit, function($locId) use ($visitedLocations) {
    return !isset($visitedLocations[$locId]); // remove locations which were already handled
  });

  foreach ($toVisit as $locId) {
    $visitedLocations[$locId] = true;
  }

  $stm = $db->prepareWithIntList("SELECT id FROM locations WHERE region IN (:locs)", ["locs" => $toVisit]);
  $stm->execute();
  $toVisit = $stm->fetchScalars();

  $i++;
}

echo "THE LONGEST CORRIDOR IS: " . $i . "\n";
