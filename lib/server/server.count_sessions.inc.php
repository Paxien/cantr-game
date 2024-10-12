<?php

$page = "server.statistics";
include "server.header.inc.php";

$db = Db::get();
$db->query("INSERT INTO count_sessions VALUES (NOW(), (SELECT COUNT(*) FROM sessions))");

include "server/server.footer.inc.php";
