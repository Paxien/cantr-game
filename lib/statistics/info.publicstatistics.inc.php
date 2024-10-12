<?php

$type = $_REQUEST['type'];

show_title("<CANTR REPLACE NAME=statistics_public_title>");

echo "<div class='page'>";
echo "<table BORDER=1>";

switch ($type) {

 case "language_groups" :
   include "info.statistics.languages.inc.php";
   break;

 case "trends" :
   include "info.statistics.trends.inc.php";
   break;
}

echo "</TABLE></div><BR>";

echo "<CENTER><a href=\"index.php?page=publicstatistics&type=language_groups\"><CANTR REPLACE NAME=statistics_public_language_group></a></CENTER>";
echo "<CENTER><a href=\"index.php?page=publicstatistics&type=trends\"><CANTR REPLACE NAME=statistics_public_time_trends></a></CENTER>";

echo "<BR><CENTER><A HREF=\"index.php?page=player\"><CANTR REPLACE NAME=back_to_player></A></CENTER>";
