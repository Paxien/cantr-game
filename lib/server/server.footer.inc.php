<?php

$db = Db::get();
if ($page != "") {
  $stm = $db->prepare("DELETE FROM servprocrunning WHERE procname = :name");
  $stm->bindStr("name", $page);
  $stm->execute();
}

// performance measuring
list ($usec, $sec) = explode(" ", microtime());
$time = $usec + $sec % 10000000 - $time;

printf("<p align=center><small> %.3f seconds taken, %d queries executed (SQL took %.3f seconds - %.1f%%).</small></p>",
  $time, $sqlcount, $sqltime, 100 * $sqltime / $time);

$stm = $db->prepare("INSERT INTO timing (pagetype, thecount, sqlcount, totaltime, sqltime, day)
     VALUES (:page, 1, :sqlCount, :time, :sqlTime, (SELECT day FROM turn LIMIT 1))");
$stm->bindStr("page", $page);
$stm->bindInt("sqlCount", $sqlcount);
$stm->bindFloat("time", $time);
$stm->bindFloat("sqlTime", $sqltime);
$stm->execute();
