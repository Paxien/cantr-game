<?php

$page = "server.travel";
include "server.header.inc.php";

print "Travelling: \n ";

$db = Db::get();
$left = 0; $done = 0;

$stm = $db->query("SELECT * FROM travels");
foreach ($stm->fetchAll() as $trav_info) {
  try {
    echo "\nTravel $trav_info->id: ";
    $travel = Travel::loadFromFetchObject($trav_info, $db);
    $travel->makeProgress();
    echo $travel->isFinished() ? "finished" : "processing";
    $travel->isFinished() ? $done++ : $left++;
  } catch (DisallowedActionException $e) {
    print "Impossible to continue travel for $trav_info->id. Reason: ". $e->getMessage() ."\n";
  } catch (Exception $e) {
    print "FATAL: Impossible to make progress for travel $trav_info->id, because of corrupted data: ". $e->getMessage() ."\n";
  } // if data corrupted or can't move according to the rules then travel is omitted
}

print "\ndone: $done and left: $left\n";

include "server/server.footer.inc.php";

?>