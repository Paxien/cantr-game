<?php

include "../lib/stddef.inc.php";
include_once "../lib/header.functions.inc.php";
include_once "../lib/urlencoding.inc.php";

$remaddr = $GLOBALS['REMOTE_ADDR'];

$db = Db::get();
$stm = $db->prepare("INSERT INTO track_referrals (ip, reference) VALUES (:ip,'Google AdWords')");
$stm->bindStr("ip", $remaddr);
$stm->execute();

redirect("intro");
