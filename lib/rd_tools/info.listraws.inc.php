<?php

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_RAW_MATERIAL_LOCATIONS)) {
  CError::throwRedirect("player", "You are not allowed to read the raw materials list.");
}

//SANITIZE INPUT
$region = HTTPContext::getInteger('region');
$display_empty = $_REQUEST['display_empty'];
$island_lookup = $_REQUEST["island_lookup"];

$db = Db::get();

if ($display_empty) {
  show_title("RAW MATERIALS LIST - NON-POPULATED LOCATIONS");
  $empty_str = " AND id NOT IN (SELECT location FROM char_on_loc_count WHERE root=location) ";
} else {
  show_title("RAW MATERIALS LIST");
  $empty_str = "";
}

if ($island_lookup == 1) {
  $locStm = $db->prepare("SELECT l.*,
          (SELECT COALESCE(NULLIF(usersname, ''), name) FROM  oldlocnames ol WHERE ol.id = l.id) AS usersname
        FROM locations l WHERE island = :island AND type=1 $empty_str ORDER BY name"); // TODO injected query, but it's constant
  $locStm->bindInt("island", $region);
  $locStm->execute();
} else {
  $locStm = $db->prepare("SELECT l.*,
          (SELECT COALESCE(NULLIF(usersname, ''), name) FROM  oldlocnames ol WHERE ol.id = l.id) AS usersname
        FROM locations l WHERE region = :region AND type=1 $empty_str ORDER BY name");
  $locStm->bindInt("region", $region);
  $locStm->execute();
}


echo "<CENTER><TABLE WIDTH=700>";

echo "<TR VALIGN=top><TD WIDTH=700>S = Borders a sea<BR>L = Borders a lake<BR><BR><TABLE>";

foreach ($locStm->fetchAll() as $location_info) {

  echo "<TR VALIGN=top><TD>$location_info->usersname [$location_info->id]";

  if ($location_info->borders_lake) {
    echo " (L)";
  }
  if ($location_info->borders_sea) {
    echo " (S)";
  }

  echo "</TD><TD>";

  $stm = $db->prepare("SELECT * FROM raws WHERE location = :locationId ORDER BY type");
  $stm->bindInt("locationId", $location_info->id);
  $stm->execute();

  foreach ($stm->fetchAll() as $raws_info) {

    $stm = $db->prepare("SELECT * FROM rawtypes WHERE id = :id");
    $stm->bindInt("id", $raws_info->type);
    $stm->execute();
    $rawtype_info = $stm->fetchObject();

    echo "$rawtype_info->name <A HREF=\"index.php?page=delraw&location=$location_info->id&rawtype=$rawtype_info->id&display_empty=$display_empty&island_lookup=$island_lookup\">[delete]</A><BR>";
  }

  $stm = $db->prepare("SELECT unique_name FROM objecttypes WHERE id = :id");
  $stm->bindInt("id", $location_info->area);
  $areaName = $stm->executeScalar();

  echo "</TD><TD>$areaName</TD><TD>";

  $stm = $db->prepare("SELECT * FROM rawtypes WHERE perday>0 AND
          id NOT IN (SELECT type FROM raws WHERE location = :locationId) ORDER BY name");
  $stm->bindInt("locationId", $location_info->id);
  $stm->execute();

  echo "<P><FORM METHOD=post ACTION=\"index.php?page=addraw\">";
  echo "<INPUT TYPE=hidden NAME=location VALUE=$location_info->id>";
  echo "<INPUT TYPE=hidden NAME=display_empty VALUE=$display_empty>";
  echo "<INPUT TYPE=hidden NAME=island_lookup VALUE=$island_lookup>";
  echo "<SELECT NAME=rawtype><OPTION VALUE=0>";

  foreach ($stm->fetchAll() as $rawtype_info) {
    echo "<OPTION VALUE=$rawtype_info->id>$rawtype_info->name";
  }

  echo "</SELECT>";
  echo " <INPUT TYPE=submit VALUE=Add>";
  echo "</FORM></P>";

  echo "</TD></TR>";
}
    
echo "</TABLE></TD></TR><TR><TD COLSPAN=3 ALIGN=center>";
echo "<BR><A HREF=\"index.php?page=player\">Back to player menu</A>";
echo "</TD></TR>";

echo "</TABLE></CENTER>";
