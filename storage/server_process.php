<?php

/**
 * @author Aleksander Chrabaszcz
 *
 * A script responsible for Cantr clock ticking. It ticks once per 5 real seconds to move game clock forward by one in-game second.
 * It checks current in-game date, increments it by one second and saves the date to the database. Then sleeps for around 5 second.
 * It doesn't amplify the inaccuracies.
 * It asks for a new connection every 5 seconds, so when db crash happens then clock stops and starts working immediately
 * when database is working again.
 *
 * HOWTO
 * There are two ways to use it in CLI:
 * When you run it without arguments, then your current working directory needs to be the main directory of the specific Cantr instance, e.g.
 * root:/home/http/www.cantr.net# php storage/server_process.php
 *
 * It's also possible to specify a single argument being a (possibly relative) path to the main directory of the specific Cantr instance e.g.
 * root:/home/http# #php intro.cantr.net/storage/server_process.php www.cantr.net/
 * OR
 * root:/home/http# #php intro.cantr.net/storage/server_process.php /home/http/www.cantr.net/
 * Both will work.
 * It's necessary for a script to have a read access to instance's config/config.json file and all files in lib/ directory
 *
 */


$serverRootPath = ".";
if (count($argv) == 2) {
  $serverRootPath = $argv[1];
}


require_once($serverRootPath . "/lib/stddef.inc.php");
require_once(_LIB_LOC . "/header.functions.inc.php");

$SECONDS_PER_GAME_SECOND = 5;
$MAX_CATCH_UP_GAME_SECONDS = 180;
// the game clock can be frozen for 15 rl minutes (e.g. db backup) and it will be able to recover from such a short delay

function getDbConnection(Config $config) {

  $pdo = new PDO('mysql:host=' . $config->dbHost() . ';dbname=' . $config->dbName() . ";charset=utf8",
    $config->dbUser(), $config->dbPassword());

  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  return new Db($pdo);
}

function updateTime(Db $db, GameDate $gameDate) {
  print_r($gameDate->getObject());

  $stm = $db->prepare("UPDATE turn SET
    number = :day1, day = :day2,
    part = :hour1, hour = :hour2,
    minute = :minute, second = :second");
  $stm->bindInt("day1", $gameDate->getDay());
  $stm->bindInt("day2", $gameDate->getDay());
  $stm->bindInt("hour1", $gameDate->getHour());
  $stm->bindInt("hour2", $gameDate->getHour());
  $stm->bindInt("minute", $gameDate->getMinute());
  $stm->bindInt("second", $gameDate->getSecond());
  $stm->execute();
}

$now = microtime(true);
$nextTick = $now;

$numberOfSeconds = 1;

$config = Request::getInstance()->getEnvironment()->getConfig();

while (true) { // infinite loop

  try {

    $db = getDbConnection($config); // will throw exception when db is unavailable

    $stm = $db->prepare("SELECT * FROM turn");
    $stm->execute();
    $currentDate = $stm->fetch();

    $gameDate = GameDate::fromDate($currentDate->day, $currentDate->hour, $currentDate->minute, $currentDate->second);
    $newGameDate = $gameDate->plus(GameDate::fromTimestamp($numberOfSeconds)); // add a second

    updateTime($db, $newGameDate);

    $numberOfSeconds = 0; // reset the counter after a successful update

  } catch (Exception $e) {
    echo "Unable to update time. " . $e . "\n";
  }

  $db = null;

  $nextTick += $SECONDS_PER_GAME_SECOND;
  $numberOfSeconds++;
  $now = microtime(true);
  while (true) {
    $msecToSleep = ($nextTick - $now) * 1e6;
    if ($msecToSleep  >= 0) {
      break;
    }
    $nextTick += $SECONDS_PER_GAME_SECOND;
    $numberOfSeconds++;
  }

  $numberOfSeconds = min($numberOfSeconds, $MAX_CATCH_UP_GAME_SECONDS);

  usleep($msecToSleep);
}

