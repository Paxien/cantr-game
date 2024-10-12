<?php

$page = "server.sailing";
include "server.header.inc.php";

print "Sailing:\n\n";

$db = Db::get();
$stm = $db->query("SELECT s.id FROM sailing s INNER JOIN locations l ON l.id = s.vessel ORDER BY docking_target DESC");
foreach ($stm->fetchScalars() as $sailingId) {
  $sailing = Sailing::loadById($sailingId);
  $sailing->makeProgress();
  echo $sailing->getReport() . "<br>";
}

include "server/server.footer.inc.php";
