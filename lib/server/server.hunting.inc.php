<?php

$page = "server.hunting";
include "server.header.inc.php";

print "Hunting:\n";
$db = Db::get();

$stm = $db->prepare("DELETE FROM hunting WHERE (turn <= :yesterday1 AND turnpart <= :hour) OR turn < :yesterday2");
$gameDate = GameDate::NOW();
$stm->bindInt("yesterday1", $gameDate->getDay() - 1);
$stm->bindInt("hour", $gameDate->getHour());
$stm->bindInt("yesterday2", $gameDate->getDay() - 1);
$stm->execute();

print "Done hunting.";

include "server/server.footer.inc.php";

?>
  
