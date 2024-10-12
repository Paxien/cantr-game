<?php

$page = "server.loginfo";
include "server.header.inc.php";

print "Player/character logging:\n";
$db = Db::get();

$stm = $db->prepareWithIntList("SELECT COUNT(id) AS totalp FROM players WHERE status IN (:statuses)", [
  "statuses" => [PlayerConstants::APPROVED, PlayerConstants::ACTIVE],
]);
$totalPlayers = $stm->executeScalar();
Report::saveInPcStatistics("totalp", $totalPlayers);

$stm = $db->prepare("SELECT COUNT(id) AS totalc FROM chars WHERE status = :active");
$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$totalCharacters = $stm->executeScalar();
Report::saveInPcStatistics("totalc", $totalCharacters);

print "done.\n";

include "server/server.footer.inc.php";
