<?php

$type = $_REQUEST['type'];

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::VIEW_INTERNAL_STATISTICS)) {

  show_title ("STATISTICAL OVERVIEW CANTR");

  echo "<div class=\"page\">";
  echo "<table BORDER=1>";

  switch ($type) {

  case "" :
    //fall through switch. This is the start page with the menu
    break;

  case "ingamedetail" :
    if (!isset($detail) || empty($detail)) {
      //redirect("statistics");
      include "info.statistics.ingame.inc.php";
    } else {
      include "info.statistics.ingame.$detail.inc.php";
    }
    break;

  case "pcstatistics" :
    include "info.statistics.pcstatistics.inc.php";
    break;

  case "language_groups" :
    include "info.statistics.languages.inc.php";
    break;

  case "trends" :
    include "info.statistics.trends.inc.php";
    break;

  default :
    echo "<tr><td>Link doesn't work:<br /><pre>info.statistics.inc.php<br />";
    echo "</pre><br />Please report this error to the system administrators.</td></tr>";
    break;
  }

  echo "</TABLE></div><BR>";

  echo "<CENTER><a href=\"index.php?page=statistics&type=ingamedetail\">Current in-game statistics</a></CENTER>";
  echo "<CENTER><a href=\"index.php?page=statistics&type=pcstatistics\">Player/Character statistics</a></CENTER>";
  echo "<CENTER><a href=\"index.php?page=statistics&type=language_groups\">Language group statistics</a></CENTER>";
  echo "<CENTER><A HREF=\"index.php?page=statistics&type=trends\">Time trends</A></CENTER>";

  echo "<BR><CENTER><A HREF=\"index.php?page=player\">Back to player page</A></CENTER>";
} else {

  redirect("player");
}
