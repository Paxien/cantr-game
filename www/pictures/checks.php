<?php
/*******************************************************
 *
 * Imagething - Checks before upload
 * Copyright (C) 2003/2004 Renko <renko@virtual-life.net>
 *
 * This file is part of the Imagething software.
 * Imagething is distributed under specific license,
 * see LICENSE.TXT for details.
 * checks.php build 2
 *******************************************************/

require_once("../../lib/stddef.inc.php");
require_once(_LIB_LOC . "/header.functions.inc.php");

$ip = $_SERVER['REMOTE_ADDR'];
$now = date("Ymd");
$now2 = date("U");

// check session
$sess_ok = false;

$s = session::getSessionFromCookie();

$db = Db::get();
if (isset($s)) {
  $stm = $db->prepare("SELECT player FROM `sessions` WHERE id = :sessionId LIMIT 1");
  $stm->bindInt("sessionId", $s);
  $result = $stm->executeScalar();

  if ($result) {
    $player_id = $result;
    $sess_ok = true;
  }
}

if (!$sess_ok) {
  echo "Session Not Recognised";
  die();
}

if ($check != "nolimit") {

  // check 1: Limits/day
  $stm = $db->prepare("SELECT * FROM `uls_ip` WHERE ip = :ip");
  $stm->bindStr("ip", $ip);
  $stm->execute();
  $result = $stm->fetch(PDO::FETCH_NUM);
  $stmQuery = $db->query("SELECT value FROM `uls_settings` WHERE id = 1 OR id = 2 OR id = 7 OR id = 8 OR id = 9 OR id = 10 OR id = 11 OR id = 12 OR id = 14 ORDER BY `id` ASC");
  $byteslft = "$result[1]";

  // Max files/day!
  $maxfileday = $stm->fetch(PDO::FETCH_NUM);
  $count = 0;

  if ($count > $maxfileday[0]) {
    $check = "1";
    $txt[15] = str_replace("%VAR%", $maxfileday[0], $txt[15]);
    echo "$txt[15]<p>";
  }

  // fetch some rows
  $trafweek = $stmQuery->fetch(PDO::FETCH_NUM); // weektraffic per IP
  $saveimg = $stmQuery->fetch(PDO::FETCH_NUM); // del image na x days
  $maxkb = [1048576];

  // check if a custom text is bla.
  $result1 = $stmQuery->fetch(PDO::FETCH_NUM);
  $result = $stmQuery->fetch(PDO::FETCH_NUM);
  if ($result1[0] == "on") {
    $ctext = $result[0];
  }


  // Check fee space on folder:
  $result = $stmQuery->fetch(PDO::FETCH_NUM);

  $sql = $db->query("SELECT sum(size) FROM uls_lastreq");
  $dirsize = $sql->fetch(PDO::FETCH_NUM);
  $result = $stmQuery->fetch(PDO::FETCH_NUM);
  $result2 = $stmQuery->fetch(PDO::FETCH_NUM);

  if ($result[0] && $result[0] != "0") {
    $proc = 100 * $dirsize[0] / $result[0];

    if ($proc >= "80") {
      $days2 = $result2[0];
    }

    if ($proc >= "95" & $proc < "100") {
      $days2 = 1;
      echo "$txt[18] ($dirsize[0]/$result[0])<p>";
    }

    if ($proc >= "100") {
      echo "$txt[19]<p>";
      $check = "1";
    }
  }

} // end if $check != pass.
