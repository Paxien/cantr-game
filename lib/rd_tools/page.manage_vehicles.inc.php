<?php

show_title ("MANAGE CONNECTION TYPES");

/********* CHECKING WHETHER PLAYER HAS ACCESS TO THIS PAGE **************/

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::ALTER_OBJECTTYPES_AND_VEHICLES)) {
  CError::throwRedirect("player", "You do not have access to the raw material types management page.");
}
  echo "<CENTER><TABLE WIDTH=700><TR><TD>";

  $db = Db::get();
  $stm = $db->query("SELECT * FROM connecttypes ORDER BY name");
  foreach ($stm->fetchAll() as $connection_info) {
    echo "<B>$connection_info->name</B><BR>$connection_info->description<BR><BR>";
    echo "<FORM METHOD=post ACTION=\"index.php?page=managevehicles&connection=$connection_info->id\">";
    echo "Speedlimit: <INPUT TYPE=text SIZE=5 NAME=speedlimit VALUE=\"$connection_info->speedlimit\"><BR><BR>";

    $vehicles = explode(",", $connection_info->vehicles);

    echo "<table width='700'>";
    echo "<tr><td> <label style=\"cursor:pointer\"><INPUT TYPE=checkbox NAME=vehicle0";

    for ($teller = 0; $teller <= count ($vehicles); $teller++)
      if ($vehicles[$teller] == 'walking') { echo " CHECKED"; }

    echo "> walking </label></td>";

    $stm = $db->query("SELECT * FROM objecttypes WHERE category='vehicles'");
    $i = 1;
    foreach ($stm->fetchAll() as $vehicle_info) {
      if ($i % 5 == 0) {
        echo "<tr>";
      }
      $i++;
      echo "<td> <label style=\"cursor:pointer\"><INPUT TYPE=checkbox NAME=vehicle" . $vehicle_info->id;
		       
      if (in_array($vehicle_info->id, $vehicles)) {
        echo " CHECKED";
      }
			
      echo "> $vehicle_info->name</label></td>";
      if ($i % 5 == 0) {
        echo "</tr>";
      }
    }
    echo "</table>";
		
    echo "<INPUT TYPE=hidden NAME=data VALUE=yes>";
    echo "<BR><INPUT TYPE=submit VALUE=Update></FORM><BR>";
  }
	
  echo "<BR><CENTER><A HREF=\"index.php?page=player\">Back to player page</A></CENTER>";
	
  echo "</TD></TR></TABLE></CENTER>";

