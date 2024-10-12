#!/usr/bin/php

<?php

// start time measuring
list($usec, $sec) = explode(" ", microtime());
$time = $usec + $sec % 10000000;

$sqlcount = 0;
$sqltime = 0;


// If server scripts are called from crontab, we need these inclusions
// that otherwise are already done in index.php
if (!defined("_LIB_LOC")) {
  chdir('lib/');
  include "stddef.inc.php";
  require_once "header.functions.inc.php"; // do not try to use cantr_redirect
  require_once "func.getrandom.inc.php";
  require_once "urlencoding.inc.php";
}

$db = Db::get();
$stm = $db->prepare("SELECT locked FROM gamelock");
$gameLocked = $stm->executeScalar();

if ($gameLocked) {
  echo "\n\nGAME IS LOCKED!";
  exit();
}

$stm = $db->query("SELECT number,day,part, hour FROM turn");
$turn = $stm->fetchObject();

if ($page != "") {
  $stm = $db->prepare("DELETE FROM servprocrunning WHERE procname = :name");
  $stm->bindStr("name", $page);
  $stm->execute();

  $stm = $db->prepare("INSERT INTO servprocrunning (procname) VALUES (:name)");
  $stm->bindStr("name", $page);
  $stm->execute();
}
