<?php

$page = "server.animals.new";
include_once "server.header.inc.php";

set_time_limit(240);
$manager = new GlobalAnimalsManager();
$report = $manager->processAll();

echo "<br />\n";

$currentDate = GameDate::NOW();
echo "Animals script run at " . $currentDate->getDay() . "-" . $currentDate->getHour() . "<br />\n";
foreach ($report as $repEntry => $value) {
  echo "$repEntry: $value<br />";
}

include "server/server.footer.inc.php";
