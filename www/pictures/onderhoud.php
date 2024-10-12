<?php
/*******************************************************
 *
 * Imagething - Daily maintenance
 * Copyright (C) 2003/2004 Renko <renko@virtual-life.net>
 *
 * This file is part of the Imagething software.
 * Imagething is distributed under specific license,
 * see LICENSE.TXT for details.
 * onderhoud.php build 4
 *
 * This script runs one time every day, triggered by the
 * first image request of the day.
 * This script makes uls_daystatics and deletes old unrequested
 * images to free up disk space.
 *******************************************************/


$db = Db::get();
$db->query("INSERT IGNORE INTO `uls_daystats` ( `day` , `bytes` , `filecount`, `uploads` )
VALUES (NOW( ) , '0', '0', '0')");


// herstart query van getimg.php:
if ($ref == "getimg") {
  $stm = $db->prepare("SELECT uls_ip.bytes, uls_lastreq.size, UNIX_TIMESTAMP(uls_ip.date2) as date2, uls_lastreq.count, uls_settings.value,
    uls_daystats.bytes, uls_daystats.filecount, uls_ip.ip, uls_lastreq.thmb FROM `uls_ip`, `uls_lastreq`, `uls_settings`, `uls_daystats`
    WHERE uls_ip.ip = uls_lastreq.ip AND uls_settings.id = 2 AND uls_daystats.day = :now AND uls_lastreq.file = :file");
  $stm->bindStr("now", $now);
  $stm->bindStr("file", $img);
  $stm->execute();

  $result = $stm->fetch(PDO::FETCH_NUM);
}
