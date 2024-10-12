<?php

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::ALTER_OBJECTTYPES_AND_VEHICLES)) {

	show_title("Overview objecttypes");

	echo "<CENTER><TABLE WIDTH=700>";
  $db = Db::get();
	$stm = $db->query("SELECT ot.*, oc.name AS category_name FROM objecttypes ot
    INNER JOIN objectcategories oc ON oc.id = ot.objectcategory ORDER BY objectcategory,name");
	foreach ($stm->fetchAll() as $objecttype_info) {

		if ($cat != $objecttype_info->category_name) {

			echo "<TR><TD WIDTH=700><BR><B>$objecttype_info->category_name:</B></TD></TR>";

			$cat = $objecttype_info->category_name;
		}

	    echo "<TR><TD WIDTH=700><A HREF=\"index.php?page=manageobjects&func=form&object=$objecttype_info->id\">";
	    echo "[edit]</A><A HREF=\"index.php?page=manageobjects&func=copy&object=$objecttype_info->id\"> [copy]</A>";
	    echo " $objecttype_info->name ($objecttype_info->id)</TD></TR>";
	}

  $playerInfo = Request::getInstance()->getPlayer();
  if ($playerInfo->hasAccessTo(AccessConstants::ACCESS_VEHICLE_APPLET)) {
  		echo "<TR><TD WIDTH=700 ALIGN=center><BR><BR><A HREF=\"index.php?page=vehicleapplet\">Vehicle Management Applet</A></TD></TR>";
	}

	echo "<TR><TD WIDTH=700 ALIGN=center><BR><BR><A HREF=\"index.php?page=player\">Back to Cantr II</A></TD></TR>";

	echo "</TABLE></CENTER>";
} else {
  CError::throwRedirectTag("player", "error_not_authorized");
}
