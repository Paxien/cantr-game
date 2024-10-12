<?php
/*******************************************************
 *
 * Imagething - Uploadprocessingpage
 * Copyright (C) 2003/2004 Renko <renko@virtual-life.net>
 *
 * This file is part of the Imagething software.
 * Imagething is distributed under specific license,
 * see LICENSE.TXT for details.
 * upload.php build 4
 *******************************************************/
$page = "uploader";

include_once("settings.php");
include_once("checks.php");

if ($check == "1") {
  die();
}


//echo " Player ID = $player_id";
//echo " Session ID = $s ";
$filename = $_FILES["image"]["name"];
$filename = preg_replace('/[^a-z0-9A-Z.]/', '_', $filename);

if ($filename == null) {
  include("index.php");
  die();
}

// Some fileinfo.
$filename = str_replace("+", "_", "$filename");
$filename = str_replace("&", "_", "$filename");
$extensie = substr($filename, -3);
$ext2 = strtoupper($extensie);
$size = $_FILES["image"]["size"];
$sizeb = $size;

$db = Db::get();
$query = $db->query("SELECT value FROM `uls_settings` WHERE id = 8 OR id = 13 OR id = 16 ORDER BY `id` ASC");
$allowedsize = $query->fetch(PDO::FETCH_NUM);


if ($check != "nolimit") {
  // sizecheck
  if ($size > $allowedsize[0] || $size == "0") {
    echo $txt[104];
    die();
  }
}
$result = $query->fetch(PDO::FETCH_NUM);
$tmpimg = $_FILES['image']['tmp_name'];
$size = GetImageSize($tmpimg);


// check imagewidth, give a warning if width is too width.
if ($size[0] >= $result[0]) {
  $txt[91] = str_replace("%VAR%", $result[0], $txt[91]);
  $thumbwarn = $txt[91];
  $thmb = "1";
}


//renames file.JPEG to file.JPG
if ($ext2 == "PEG") {
  $extensie = "jpg";
}

// Check if the file is an image.
if ($ext2 != "JPG" && $ext2 != "GIF" && $ext2 != "PNG" && $ext2 != "PEG") {
  echo $txt[92];
  die();
} else {

  $length = strlen($filename);
  if ($ext2 == "PEG") {
    $name2 = substr($filename, 0, $length - 5);
  } else {
    $name2 = substr($filename, 0, $length - 4);
  }
  $i = "";
  $tempname = date('Ymd') . "_$name2";


  // Check if file al exist, yes? make filename 'higher'.
  while (file_exists("$uploaddir/$tempname$i.$extensie")
    || file_exists(_ROOT_LOC . "/user_assets/refused_images/$tempname$i.$extensie")) {
    $i = intval($i) + 1;
  }
  $filename = "$tempname$i.$extensie";

  $img = "$uploaddir/$filename";
  move_uploaded_file($_FILES['image']['tmp_name'], "$img");
  chmod($img, 0755);
}


//** UPLOADING DONE, NOW THE 'ADMINISTRATION'-PART **//
$ip = $_SERVER['REMOTE_ADDR'];

$sql = $db->prepare("SELECT filecount FROM uls_ip WHERE ip = :ip");
$sql->bindStr("ip", $ip);
$sql->execute();
$result = $sql->fetch(PDO::FETCH_NUM);

if ($result[0] != null) {
  // check date
  $now2 = date("U");
  $sql = $db->prepare("SELECT UNIX_TIMESTAMP(date2) as epoch_time FROM uls_ip WHERE ip = :ip");
  $sql->bindStr("ip", $ip);
  $sql->execute();
  $result2 = $sql->fetch(PDO::FETCH_NUM);
}
if ($result[0] == null) {

  // make new IP table.
  $sql = $db->query("SELECT value FROM uls_settings WHERE id = 2");
  $result = $sql->fetch(PDO::FETCH_NUM);

  $stm = $db->prepare("INSERT INTO `uls_ip` ( `ip` , `bytes` , `filecount` , `date2` )
 VALUES (
 :ip, :bytes, '1', NOW( )
 )");
  $stm->bindStr("ip", $ip);
  $stm->bindInt("bytes", $result[0]);
  $stm->execute();
}

// maak nieuw record voor deze file (in elk geval).
$stm = $db->prepare("INSERT INTO `uls_lastreq` ( `file` , `count` , `date` , `ip` , `size` , `thmb` ,`player`)
  VALUES (
  :filename, '0', NOW( ) , :ip, :size, :thmb, :playerId
  )");
$stm->bindStr("filename", $filename);
$stm->bindStr("ip", $ip);
$stm->bindStr("size", $sizeb);
$stm->bindInt("thmb", 0);
$stm->bindInt("playerId", $player_id);
$stm->execute();

// upload review system
$stm = $db->prepare("INSERT INTO `player_images` ( `name` , `uploader_id` , `date` , `accepted` , `accepted_by` )
  VALUES (:filename, :playerId, NOW( ), 0, NULL ) ");
$stm->bindStr("filename", $filename);
$stm->bindInt("playerId", $player_id);
$stm->execute();

// update stats:
$now = date("Y-m-d");
$sql = $db->prepare("SELECT uploads FROM `uls_daystats` WHERE day = :day");
$sql->bindStr("day", $now);
$sql->execute();
$result = $sql->fetch(PDO::FETCH_NUM);


// Dagelijks onderhoud:
if ($result[0] == null) {
  include("onderhoud.php");
  $result[0] = 0;
}


$result[0]++;
$stm = $db->prepare("UPDATE `uls_daystats` SET `uploads` = :uploads WHERE `day` = :date LIMIT 1");
$stm->bindInt("uploads", $result[0]);
$stm->bindStr("date", $now);
$stm->execute();

$uploaded = "$txt[93] \"$filename\".<br><br><a href=\"/pictures/getimg.php?img=$filename\">/pictures/getimg.php?img=$filename</a><br><br>Copy and paste code below into note:<br><br>&lt;img src=\"/pictures/getimg.php?img=$filename\" alt=\"\" /&gt;<br><br>";

include("index.php");
