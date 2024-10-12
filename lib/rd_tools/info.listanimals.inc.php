<?php

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::ALTER_ANIMAL_PLACEMENT)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$db = Db::get();
// SANITIZE INPUT
$region = HTTPContext::getInteger('region');
$language_lookup = HTTPContext::getRawString("language_lookup");
$island_lookup = HTTPContext::getRawString("island_lookup");

if ($language_lookup == 1) {
  $stm = $db->prepare("SELECT name FROM languages WHERE id = :id LIMIT 1");
  $stm->bindInt("id", $region);
  $lang = $stm->executeScalar();

  $locStm = $db->prepare("SELECT * FROM locations
        WHERE id IN (SELECT location FROM char_on_loc_count WHERE language = :language AND location != 0 AND root = location) AND type=1 ORDER BY name");
  $locStm->bindInt("language", $region);
  $locStm->execute();
  show_title("ANIMALS PLACEMENT MANAGER - $lang LANGUAGE AREA");

} elseif ($island_lookup == 1) {
  $locStm = $db->prepare("SELECT * FROM locations WHERE island = :island AND type=1 ORDER BY name");
  $locStm->bindInt("island", $region);
  $locStm->execute();
  show_title("ANIMALS PLACEMENT MANAGER - ISLAND #$region");
} else {
  $stm = $db->prepare("SELECT name FROM regions WHERE id = :id LIMIT 1");
  $stm->bindInt("id", $region);
  $reg = $stm->executeScalar();
  $locStm = $db->prepare("SELECT * FROM locations WHERE locations.region = :region AND type=1 ORDER BY name");
  $locStm->bindInt("region", $region);
  $locStm->execute();
  show_title("ANIMALS PLACEMENT MANAGER - $reg REGION");
}

$stm = $db->prepare("SELECT id, unique_name FROM objecttypes
    WHERE objectcategory = :category");
$stm->bindInt("category", ObjectConstants::OBJCAT_TERRAIN_AREAS);
$stm->execute();
while (list ($id, $name) = $stm->fetch(PDO::FETCH_NUM)) {
  $loctype [$id] = $name;
}

echo "<CENTER><TABLE WIDTH=700>";
echo "<TR VALIGN=top><TD WIDTH=700>";
echo "<TABLE>";

foreach ($locStm->fetchAll() as $location_info) {

  echo "<TR VALIGN=top><TD>$location_info->name [$location_info->id]";

  if ($location_info->borders_lake) echo " (L)";
  if ($location_info->borders_sea) echo " (S)";
  echo "<br /><small>" . $loctype [$location_info->area] . "</small>";

  echo "</TD><TD>";

  $stm = $db->prepare("SELECT type, number FROM animals WHERE location = :locationId GROUP by type");
  $stm->bindInt("locationId", $location_info->id);
  $stm->execute();

  foreach ($stm->fetchAll() as $animal_info) {

    $stm = $db->prepare("SELECT name FROM animal_types WHERE id = :id");
    $stm->bindInt("id", $animal_info->type);
    $animalTypeName = $stm->executeScalar();

    echo "<FORM METHOD=post ACTION=\"index.php?page=alteranimals\">";
    echo "<INPUT TYPE=hidden NAME=location VALUE=$location_info->id>";
    echo "<INPUT TYPE=hidden NAME=typeid VALUE=$animal_info->type>";
    echo "<INPUT TYPE=hidden NAME=oldnumber VALUE=$animal_info->number>";
    echo "<INPUT TYPE=hidden NAME=language_lookup VALUE=$language_lookup>";
    echo "<INPUT TYPE=hidden NAME=region VALUE=$region>";
    echo "<INPUT TYPE=text NAME=number VALUE=$animal_info->number SIZE=3> " . $animalTypeName .
      " <INPUT TYPE=image SRC=\"" . _IMAGES . "/button_small_turnaround.gif\" WIDTH=20>";
    echo "</FORM><br>";
  }

  echo "</TD><TD>";

  echo "<FORM METHOD=post ACTION=\"index.php?page=alteranimals\">";
  echo "<INPUT TYPE=hidden NAME=location VALUE=$location_info->id>";
  echo "<INPUT TYPE=hidden NAME=oldnumber VALUE=0>";
  echo "<INPUT TYPE=hidden NAME=language_lookup VALUE=$language_lookup>";
  echo "<INPUT TYPE=hidden NAME=region VALUE=$region>";
  echo "<SELECT NAME=new_animal><OPTION VALUE=0>";
  $stm = $db->prepare("SELECT unique_name FROM objecttypes WHERE id = :id");
  $stm->bindInt("id", $location_info->area);
  $areaName = $stm->executeScalar();

  $stm = $db->prepare("SELECT id,name FROM animal_types WHERE area_types LIKE :areaType
        AND id NOT IN (SELECT type FROM animals WHERE location = :locationId GROUP by type) ");
  $stm->bindStr("areaType", "%$areaName%");
  $stm->bindInt("locationId", $location_info->id);
  $stm->execute();
  foreach ($stm->fetchAll() as $animal_type) {
    echo "<OPTION VALUE=$animal_type->id>$animal_type->name";
  }

  echo "</SELECT>";
  echo " <INPUT TYPE=text NAME=number VALUE=1 SIZE=2>";
  echo " <INPUT TYPE=image SRC=\"" . _IMAGES . "/button_small_turnaround.gif\" WIDTH=20>";
  echo "</FORM>";

  echo "</TD><TD>";
}

echo "</TABLE></TD></TR><TR><TD COLSPAN=3 ALIGN=center>";
echo "<BR><A HREF=\"index.php?page=player\">Back to player menu</A>";
echo "</TD></TR>";

echo "</TABLE></CENTER>";
