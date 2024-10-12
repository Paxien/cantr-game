<?php
// This script creates a replica of the main cantr production database for
// intro purposes. It also fills in some structural data from the main server.
error_reporting(E_ALL);
function run_query($query) {
  echo "Query: $query<BR>";
  $db = Db::get();
  return $db->query($query);
}

$spawnPoint = 9514; // all languages should start in the same location

$env = Request::getInstance()->getEnvironment();

$sourceServer = $env->getDbNameFor("main");
$targetServer  = $env->getDbNameFor("intro");

$temporaryDbName = "cantr_intro_temp_holder";

$dbSynchronizer = new DbSynchronizer($sourceServer, $targetServer, $temporaryDbName);

$db = Db::get();

// preserve settings and player accounts if intro db already exists
$dbExists =  $db->query("SHOW DATABASES LIKE '$targetServer'")->executeScalar();
if (!empty($dbExists)) {
  $success = $dbSynchronizer->createTemporaryDb();
  if (!$success) {
    die("temp database $temporaryDbName already exists");
  }

  $dbSynchronizer->preserveTables(["players", "chars", "newevents", "languages", "spawninglocations"]);
  $dbSynchronizer->perform();
  $dbSynchronizer->loadTables(["players", "chars", "newevents", "languages", "spawninglocations"]);
  $dbSynchronizer->destroyTemporaryDb();

  // projects are not preserved, so participation should be canceled
  run_query("UPDATE `$targetServer`.`chars` SET project = 0");

  // move mentors to spawn locations of new players, because locations (like buildings) are not preserved
  // or spawnpoint data can change over time
  foreach ($langcode as $code => $name ) {
    run_query("UPDATE `$targetServer`.`chars` c SET location =
        (SELECT sl.id FROM `$targetServer`.`spawninglocations` sl WHERE sl.language = c.language)
    ");
  }
} else {
  $dbSynchronizer->perform();
}

run_query("CREATE EVENT `$targetServer`.`time_progression` 
  ON SCHEDULE EVERY 5 SECOND DO
  UPDATE turn SET
    number = IF (hour = 7 AND minute = 35 AND second = 59, number + 1, number),
    day    = IF (hour = 7 AND minute = 35 AND second = 59, number + 1, number),
    hour    = IF (minute = 35 AND second = 59, MOD(hour + 1, 8), hour),
    part    = IF (minute = 35 AND second = 59, MOD(part + 1, 8), part),
    minute    = IF (second = 59, MOD(minute + 1, 36), minute),
    second    = MOD(second + 1, 60)
");

// Copy main locations and graphical maps
run_query("INSERT INTO `$targetServer`.`locations` SELECT * FROM `$sourceServer`.`locations` WHERE `type` = 1");
run_query("INSERT INTO `$targetServer`.`maps` SELECT * FROM `$sourceServer`.`maps`");

// make 'Ships' category unaccessible (sailing is impossible anyway)
run_query("UPDATE `$targetServer`.`objectcategories` SET `status` = 1 WHERE `id` = 34");


// define spawnpoints if not yet defined
$spawningPoints = $db->query("SELECT COUNT(*) FROM `$targetServer`.`spawninglocations`")->executeScalar();
if ($spawningPoints == 0) {

  foreach ($langcode as $code => $name ) {
    run_query("INSERT INTO `$targetServer`.`spawninglocations` VALUES ( ". $spawnPoint .", $code )");
  }
}

// all paths have to be impassable
run_query("UPDATE `$targetServer`.`connections` SET `type` = 6");

run_query("DELETE FROM `$targetServer`.`raws` WHERE location IN (SELECT sl.id FROM `$targetServer`.`spawninglocations` sl)");

// add wood, stone, po-ta-toes and wheat in every location
foreach ([4, 8, 25, 39] as $rawType) {
  run_query("INSERT IGNORE INTO `$targetServer`.`raws` (`location`, `type`)
    SELECT sl.id, $rawType FROM `$targetServer`.`spawninglocations` sl");
}

// add some sheep in every location
$sheepType = 27;
$packSize = 23;
run_query("INSERT INTO `$targetServer`.`animals` (location, type, number, damage)
      SELECT sl.id, $sheepType, $packSize, 0 FROM `$targetServer`.`spawninglocations` sl");
