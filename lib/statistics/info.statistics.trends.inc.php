<?php

$trend_type = $_REQUEST['trend_type'];
$code = $_REQUEST['code'];

echo '<script src="//code.highcharts.com/highcharts.js"></script>';
echo '<script src="js/libs/highcharts-theme.js"></script>';

echo "<TR><TD><TABLE>";
echo "<FORM METHOD=post ACTION=\"index.php?page=publicstatistics&type=trends\">";
echo "<TR><TD><TABLE><TR><TD>Type:</TD><TD><SELECT NAME=trend_type>";

$db = Db::get();
$stm = $db->query("SELECT type FROM statistics GROUP BY type ORDER BY type");
foreach ($stm->fetchAll() as $stat_info) {
  echo "<OPTION VALUE=\"$stat_info->type\">$stat_info->type</OPTION>";
}

echo "</SELECT></TD></TR>";
echo "<TR><TD>Code:</TD><TD><INPUT TYPE=text NAME=code SIZE=20 VALUE=0></TD></TR>";
echo "<TR><TD COLSPAN=2 ALIGN=center><INPUT TYPE=submit VALUE=\"Submit\"></TD></TR></TABLE></TD>";
echo "<TD>";

$stm = $db->query("SELECT id,name FROM languages ORDER BY id");
foreach ($stm->fetchAll() as $lang_info) {
  echo "$lang_info->id. <CANTR REPLACE NAME=lang_$lang_info->name><BR>";
}

echo "</TD>";
echo "<TD>";

$stm = $db->query("SELECT id,name FROM state_types ORDER BY id");
foreach ($stm->fetchAll() as $state_info) {
  echo "$state_info->id. $state_info->name<BR>";
}

echo "</TD>";
echo "</TR></TABLE></TD></TR>";

echo "<TR><TD>In most cases the 'code' field can be left at 0. For diseases, it is the number of the disease; at the moment of writing only disease '1' has been implemented. For any language-specific ";
echo "statistics, the code is the number of the language group. For state- or gene-specific statistics, the code is the number of the state type. Note that several states have no connected gene (e.g. there is a 'hunger' state, but not a 'hunger' gene.</TD></TR>";



if (isset($trend_type)) {
  
  $stm = $db->prepare("SELECT MIN(turn), MAX(turn) FROM statistics WHERE type = :type AND code = :code");
  $stm->bindStr("type", $trend_type);
  $stm->bindInt("code", $code);
  $stm->execute();
  list ($turnMin, $turnMax) = $stm->fetch(PDO::FETCH_NUM);

  $stm = $db->prepare("SELECT turn, statistic FROM statistics WHERE type = :type AND code = :code ORDER BY turn");
  $stm->bindStr("type", $trend_type);
  $stm->bindInt("code", $code);
  $stm->execute();

  $assocStats = array();
  foreach ($stm->fetchAll() as $stat) {
    $assocStats[$stat->turn] = $stat->statistic;
  }

  $fullStats = array();
  for ($i = $turnMin; $i < $turnMax; $i++) {
    $value = array_key_exists($i, $assocStats) ? $assocStats[$i] : end($fullStats)[1];
    $fullStats[] = array($i, intval($value));
  } // patching "holes" in our data

  echo "
    <script type='text/javascript'>
      var graphIn = {
        min: {$turnMin},
        max: {$turnMax},
        name: '{$trend_type}',
        points: ". json_encode($fullStats) .",
      };
    </script>
    <script src='/js/graph.js'></script>
  ";
  echo "<TR><TD>";
  echo '<div id="graph"></div>';
  echo "</TD></TR>";
}
