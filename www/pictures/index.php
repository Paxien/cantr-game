<?php
/*******************************************************
 *
 * Imagething - Uploadform/index page
 * Copyright (C) 2003/2004 Renko <renko@virtual-life.net>
 *
 * This file is part of the Imagething software.
 * Imagething is distributed under specific license,
 * see LICENSE.TXT for details.
 * index.php build 5
 *******************************************************/

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Cantr II Pictures</title>
  <link rel="stylesheet" type="text/css" href="../css/skins/green-orange.css">
</head>
<?php

require_once("../../lib/stddef.inc.php");
require_once("settings.php");
$page = "uploader";

require_once(_LIB_LOC . "/urlencoding.inc.php");
DecodeURIs();
include_once("checks.php");

?>

<body>

<?php

$db = Db::get();
// Load referers
$stm = $db->prepare("SELECT type FROM uls_referers WHERE id = 1");
$result = $stm->executeScalar();
if ($result == "0") {
  $refer = $txt[6];
} else {
  $refer = $txt[102];
}

$referlist = "";
$stm = $db->prepare("SELECT domain FROM uls_referers WHERE id != 1 AND type != :type");
$stm->bindInt("type", $result);
$stm->execute();
foreach ($stm->fetchScalars() as $result) {
  $referlist = "$referlist$result, ";
}
$referlist = substr($referlist, 0, -2);
$referlist = "$referlist.";

// If there are none results
$refchk = $stm->rowCount();
if ($refchk == 0) {
  $referlist = "$txt[60].";
}
// END: load referers


if ($check != "nolimit") {
  // calculate traffic/week
  $trafweek = $trafweek[0] / 1048576;
  if ($trafweek > "1024") {
    $trafweek = $trafweek / 1024;
    $ex = "GB";
  } else {
    $ex = "MB";
  }
  $trafweek = round($trafweek);

  // calc save time of image.
  $saveimg = $saveimg[0] / 86400;
  $saveimg = round($saveimg);

  // calc max kB/file: bytes>kBytes>round.
  $maxkbyte = $maxkb[0];
  $maxkb = $maxkb[0] / 1024;
  $maxkb = round($maxkb);

  // IP-stats
  $stm = $db->prepare("SELECT bytes FROM uls_ip WHERE ip = :ip");
  $stm->bindStr("ip", $ip);
  $result = $stm->executeScalar();
  if ($result != null) {
    $bytes = $result / 1048576;
    if ($bytes > "1024") {
      $bytes = $bytes / 1024;
      $ex2 = "GB";
      $bytes = round($bytes, 2);
    } else {
      $ex2 = "MB";
      $bytes = round($bytes);
    }


    $x = $now2 - $date2U[0];
    $x = 604800 - $x;
    $x = $x / 86400;
    $days = round($x);
  }

  // check lifetime of traffic-stats.
  if ($result == null || $days <= "0") {
    $bytes = $trafweek;
    $days = "7";
    $ex2 = $ex;
  }

  if ($days2 != null) {
    $days = $days2;
  }


} // end no limits now:


// Confirm message from uploaded files (optional)
echo "<div style='padding-left: 20px;'>";
echo "$uploaded";
echo "</div>";
// if checks are passed:
if ($check != "1") {


// Replace %VAR%s in languagefiles:
$txt[2] = str_replace("%VAR%", $maxkb, $txt[2]);
$txt[4] = str_replace("%VAR%", $maxfileday[0], $txt[4]);
$txt[5] = str_replace("%VAR%", "$trafweek$ex", $txt[5]);
$txt[7] = str_replace("%VAR%", $saveimg, $txt[7]);
$txt[9] = str_replace("%VAR%", $count, $txt[9]);
$txt[10] = str_replace("%VAR1%", "$bytes$ex2", $txt[10]);
$txt[10] = str_replace("%VAR2%", $days, $txt[10]);
$txt[11] = str_replace("%VAR2%", $saveimg, $txt[11]);


?>
<p><br>

<form method="post" name="add" action="upload.php" enctype="multipart/form-data">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxkbyte ?>">


  <table border=0 align=center>
    <tr>
      <td align=center><?php echo $txt[0] ?></td>
    </tr>
    <tr>
      <td align=center><input type="file" name="image"></td>
    </tr>
    <tr>
      <td align=center><input type="submit" value="Upload" accesskey="s"></td>
    </tr>
    <tr>
      <td>
        <div class="smalltxt" style='width: 235px'>

          <?php if ($check != "nolimit") { ?>
            <?php echo $txt[1] ?><br>
            * <?php echo $txt[2] ?><br>
            * <?php echo $txt[3] ?><br>
            <!--  * <?php echo $txt[4] ?><br>
  * <?php echo $txt[5] ?><br>
  * <?php echo $txt[7] ?><br>
  * <?php echo "$refer $referlist" ?><p>

  <?php echo $txt[8] ?><br>
  * <?php echo $txt[9] ?><br>
  * <?php echo $txt[10] ?> -->


          <?php } else {   // unlimited
            $stm = $db->query("SELECT value FROM `uls_settings` WHERE id = 7");
            $result = $stm->executeScalar();
            $saveimg = $result / 86400;
            $saveimg = round($saveimg);
            echo "$txt[12]<br>";
            echo " * $saveimg $txt[7]<br>";
            echo " * $refer $referlist<br>";
          }
           ?>


        </div>
      </td>
    </tr>
  </table>
</form>
<p><br>
<?php } ?>
</body>
</html>
