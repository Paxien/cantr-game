<?php


$page = "server.sendreports";
include "server.header.inc.php";

// reports are automatically sent after 3 days
$reportDay = GameDate::NOW()->getDay() - PlayerConstants::TURN_REPORT_AUTOSEND_DELAY;

print "Sending reports:\n";
$db = Db::get();
$stm = $db->prepareWithIntList("SELECT id FROM players WHERE (profile_options & 1) = 1
 AND status IN (:statuses)", [
   "statuses" => [PlayerConstants::APPROVED, PlayerConstants::ACTIVE],
]);
$stm->execute();
foreach ($stm->fetchScalars() as $playerId) {
  // overwrite global variable
  $playerInfo = Player::loadById($playerId);
  $player = $playerInfo->getId();

  echo "Sending report for " . $playerInfo->getFullName() . " ($playerId) - ";

  $dailyReport = new PlayerDailyReport($playerInfo, $reportDay);
  try {
    $dailyReport->sendMail();
    echo "Sending report for " . $playerInfo->getId() . " done\n";
  } catch (Exception $e) {
    echo "failed for player " . $playerInfo->getId() . ". Reason: " . $e->getMessage() . ";\n" . $e->getTraceAsString() . "\n";
  }
  sleep(10 + mt_rand(1, 10));
}

print "done.\n";

include "server/server.footer.inc.php";
