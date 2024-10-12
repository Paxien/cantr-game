<?php
/*******************************************************
 *
 * Imagething - Getimage page
 * Copyright (C) 2003/2004 Renko <renko@virtual-life.net>
 *
 * This file is part of the Imagething software.
 * Imagething is distributed under specific license,
 * see LICENSE.TXT for details.
 * getimg.php build 4
 *
 * Usage: getimg.php?img=blabla.jpg
 * Hints:
 * Using MySQL 4.0.4 or higher? Scroll down to line
 * 110 and make from this three queries one.
 * Don't useing IP-bans? Disable $query2 (line 55)
 * to spare a query.
 *******************************************************/


include_once("settings.php");

$page = "getimg";
$sqltime = 0;
$sqlcount = 0;
$sqllog = "";

chdir("..");
chdir("pictures");

$img = $_GET['img'];
$img = preg_replace('/[^a-z0-9A-Z.]/', '_', $img);


$exist = file_exists("$uploaddir/$img");
if ($exist == false || $check == "1") {
  $img = "404.png";
  $check = "1";
  include("imgerror.php");
  die();
}

$db = Db::get();

$now = date("Y-m-d");
$epoch = date("U");
$stm = $db->prepare("SELECT uls_ip.bytes, uls_lastreq.size, UNIX_TIMESTAMP(uls_ip.date2) as date2, uls_lastreq.count, uls_settings.value,
    uls_daystats.bytes, uls_daystats.filecount, uls_ip.ip, uls_lastreq.thmb, uls_lastreq.date, uls_daystats.day
  FROM `uls_ip`, `uls_lastreq`, `uls_settings`, `uls_daystats`
  WHERE uls_ip.ip = uls_lastreq.ip AND uls_settings.id = 2
    AND uls_daystats.day = '$now' AND uls_lastreq.file = :file");
$stm->bindStr("file", $img);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_NUM);

// Dagelijks onderhoud:
if ($result[9] == null) {
  $ref = "getimg";
  include("onderhoud.php");
}

$timedif = $epoch - $result[2];


if ($timedif > "604800") {
  $stm = $db->prepare("UPDATE `uls_ip` SET `date2` = NOW( ), `bytes` = '1337' WHERE `ip` = :ip");
  $stm->bindStr("ip", $result[7]);
  $stm->execute();
  $tmp = "1";
  $result[0] = $result[4];
}

if ($result[9] == "1") {
  $result[1] = $result[1] + $result[1];
}
$newcredit = $result[0] - $result[1];
$result[3]++;
$result[6]++;
$trafficcntsite = $result[5] + $result[1];


if ($check != "1") {
  $stm = $db->prepare("UPDATE `uls_lastreq` SET `count` = :count WHERE `file` = :file");
  $stm->bindInt("count", $result[3]);
  $stm->bindStr("file", $img);
  $stm->execute();

  $stm = $db->prepare("UPDATE `uls_daystats` SET `filecount` = :count, `bytes` = :bytes WHERE day = :day");
  $stm->bindInt("count", $result[6]);
  $stm->bindInt("bytes", $trafficcntsite);
  $stm->bindStr("day", $now);

  if ($check[0] != "3") {
    $stm = $db->prepare("UPDATE `uls_ip` SET `bytes` = :bytes WHERE ip = :ip");
    $stm->bindInt("bytes", $newcredit);
    $stm->bindStr("ip", $result[7]);
    $stm->execute();
  }

  // Queries done, now printing.

  $ext = substr($img, -3);
  $ext = strtolower($ext);
  if ($ext = "jpg") {
    $ext = "jpeg";
  }
  Header("Content-type: image/$ext");
  Header("Cache-Control: max-age=31536000, must-revalidate");
  $fn = fopen("$uploaddir/$img", "r");
  fpassthru($fn);
}
