<?php

// SANITIZE INPUT
$machine = HTTPContext::getInteger('machine');

show_title("MACHINES MANAGEMENT PAGE");

/********* CHECKING WHETHER PLAYER HAS ACCESS TO THIS PAGE **************/

$plr = Request::getInstance()->getPlayer();
if (!$plr->hasAccessTo(AccessConstants::MANAGE_MACHINE_PROJECTS)) {
  CError::throwRedirectTag("player", "error_manage_machines_denied");
}

/********* IN CASE MACHINE IS SELECTED: FORM TO MANAGE MACHINE INFO ***********/
$db = Db::get();
if ($machine) {

  $stm = $db->prepare("SELECT * FROM machines WHERE id = :id LIMIT 1");
  $stm->bindInt("id", $machine);
  $stm->execute();
  $machine_info = $stm->fetchObject();

  $machine_info->name = htmlspecialchars($machine_info->name);

  echo "<FORM METHOD=post ACTION=\"index.php?page=managemachines\"><CENTER><TABLE WIDTH=700>";

  echo "<TR><TD WIDTH=200>Machine:</TD>";
  echo "<TD WIDTH=400><SELECT NAME=objecttype_id>";
  echo "<OPTION VALUE=0>unassigned";

  $stm = $db->query("SELECT * FROM objecttypes ORDER BY name");
  foreach ($stm->fetchAll() as $objecttype_info) {
    echo "<OPTION VALUE=$objecttype_info->id";

    if ($machine_info->type == $objecttype_info->id) {
      echo " SELECTED";
    }

    echo ">$objecttype_info->name";
  }

  echo "</SELECT></TD></TR>";

  echo "<TR><TD>Project name:</TD>";
  echo "<TD><INPUT TYPE=text SIZE=60 NAME=project_name VALUE=\"$machine_info->name\"></TD></TR>";

  echo "<TR><TD>Project requirements:</TD>";
  echo "<TD><INPUT TYPE=text SIZE=60 NAME=requirements VALUE=\"$machine_info->requirements\"></TD></TR>";

  echo "<TR><TD>Automatic project:</TD>";
  echo "<TD><SELECT NAME=automatic>";
  echo "<OPTION VALUE=0 " . ($machine_info->automatic == 0 ? "SELECTED" : "") . ">Manual progress</OPTION>";
  echo "<OPTION VALUE=1 " . ($machine_info->automatic == 1 ? "SELECTED" : "") . ">Automatic progress</OPTION>";
  echo "<OPTION VALUE=2 " . ($machine_info->automatic == 2 ? "SELECTED" : "") . ">Both</OPTION>";
  echo "</SELECT></TD></TD>";

  $result = explode(":", $machine_info->result);

  echo "<TR><TD>Result material:</TD>";
  echo "<TD><SELECT NAME=material>";

  $stm = $db->query("SELECT * FROM rawtypes ORDER BY name");
  foreach ($stm->fetchAll() as $rawtype_info) {

    echo "<OPTION VALUE=$rawtype_info->id";

    if ($rawtype_info->id == $result[0]) {
      echo " SELECTED";
    }

    echo ">$rawtype_info->name";
  }
  echo "</SELECT></TD></TR>";

  echo "<TR><TD>Result amount:</TD>";
  echo "<TD><INPUT TYPE=text NAME=amount VALUE=\"$result[1]\"></TD></TR>";

  echo "<TR><TD>Multiplyable?</TD>";
  echo "<TD><SELECT NAME=multiply>";

  if ($machine_info->multiply) {

    echo "<OPTION VALUE=1 SELECTED>Yes<OPTION VALUE=0>No";
  } else {

    echo "<OPTION VALUE=1>Yes<OPTION VALUE=0 SELECTED>No";
  }
  echo "</SELECT></TD></TR>";

  echo "<TR VALIGN=top><TD>Maximum number of participants: (0 is no max)</TD>";
  echo "<TD><INPUT TYPE=text NAME=maxparticipants VALUE=\"$machine_info->max_participants\"></TD></TR>";

  echo "<TR VALIGN=top><TD>Skill type:</TD>";
  echo "<TD><SELECT NAME=skill>";

  echo "<OPTION VALUE=0";
  if ($machine_info->skill == 0) {
    echo " SELECTED";
  }
  echo ">none</OPTION>";

  $stm = $db->query("SELECT id,name FROM state_types ORDER BY name");
  foreach ($stm->fetchAll() as $state_type_info) {
    echo "<OPTION VALUE=$state_type_info->id";
    if ($machine_info->skill == $state_type_info->id) {
      echo " SELECTED";
    }
    echo ">$state_type_info->name</OPTION>";
  }
  echo "</SELECT></TD></TR>";

  echo "<TR VALIGN=top><TD>Description: (will only end up in the email concerning this change, not in the database)</TD>";
  echo "<TD><TEXTAREA NAME=description COLS=50 ROWS=5></TEXTAREA></TD></TR>";

  echo "<INPUT TYPE=hidden NAME=data VALUE=yes>";
  echo "<INPUT TYPE=hidden NAME=machine_id VALUE=$machine_info->id>";

  echo "<TR><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=\"Store\"></TD></TR>";

  echo "</TABLE></CENTER></FORM>";
}

/************* OVERVIEW OF MACHINE FUNCTIONS ******************/

echo "<CENTER><TABLE WIDTH=700><TR><TD>";

echo "<BR>";

$stm = $db->query("SELECT * FROM objecttypes ot WHERE EXISTS (SELECT id FROM machines WHERE type = ot.id)");
foreach ($stm->fetchAll() as $objecttype_info) {
  $stm = $db->prepare("SELECT * FROM machines WHERE type = :id ORDER BY name");
  $stm->bindInt("id", $objecttype_info->id);
  $stm->execute();
  echo "<BR><B>$objecttype_info->name</B><BR>";

  foreach ($stm->fetchAll() as $machine_info) {
    echo "<A HREF=\"index.php?page=managemachines&machine=$machine_info->id\">[edit]</A>";
    echo " <A HREF=\"index.php?page=managemachines&kopy=$machine_info->id\">[copy]</A>";
    echo " $machine_info->name";
    echo "<BR>";
  }
}

echo "<BR><B>machine projects not yet assigned to a machine</B><BR>";

$stm = $db->prepare("SELECT * FROM machines WHERE type=0 ORDER BY name");
foreach ($stm->fetchAll() as $machine_info) {
  echo "<A HREF=\"index.php?page=managemachines&machine=$machine_info->id\">[edit]</A>";
  echo " <A HREF=\"index.php?page=managemachines&kopy=$machine_info->id\">[copy]</A>";
  echo " $machine_info->name";
  echo "<BR>";
}

echo "<BR><CENTER><A HREF=\"index.php?page=player\">Back to player page</A></CENTER>";

echo "</TD></TR></TABLE></CENTER>";
