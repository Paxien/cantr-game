<?php
require_once(_LIB_LOC . "/func.getuse.inc.php");

// SANITIZE INPUT
$new = HTTPContext::getRawString('new', null);
$type = HTTPContext::getInteger('type', null);
$toolraw = HTTPContext::getInteger('toolraw');
$tool = $_REQUEST['tool'];
if ($tool != 'new') {
  $tool = intval($tool);
}

$enableTaint = $_REQUEST['enable_taint'];

show_title("MANAGE RAW MATERIAL TYPES");

/********* CHECKING WHETHER PLAYER HAS ACCESS TO THIS PAGE **************/

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_RAW_MATERIALS)) {
  CError::throwRedirect("player", "You do not have access to the raw material types management page.");
}

$db  = Db::get();
  /************* IN CASE ACCESS ALLOWED AND MATERIAL SELECTED: FORM *********/

  if ($type) {

    $stm = $db->prepare("SELECT * FROM rawtypes WHERE id= :id");
    $stm->bindInt("id", $type);
    $stm->execute();
    $rawtype_info = $stm->fetchObject();
  }

  if ($type or $new) {

    echo "<FORM METHOD=post ACTION=\"index.php?page=managerawtypes\"><CENTER><TABLE WIDTH=700>";

    echo "<TR VALIGN=top><TD WIDTH=200><B>Raw type:</B><BR>(must be unique; no capitals)</TD>";
    echo "<TD WIDTH=400><INPUT TYPE=text NAME=name VALUE=\"$rawtype_info->name\" SIZE=40></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Amount to dig per day:</B><BR>(leave blank or zero when you cannot dig this material. If tooled only (below) is checked this still needs a value)</TD>";
    echo "<TD><INPUT TYPE=text NAME=perday VALUE=\"$rawtype_info->perday\" SIZE=10></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Requires tools to dig:</B><BR>(leave unchecked if you cannot dig this or untooled collection is allowed)</TD>";
    if ($rawtype_info->reqtools == 1) {
      echo "<TD><INPUT TYPE=checkbox NAME=\"reqtools\" VALUE=\"1\"  CHECKED></TD></TR>";
    } else {
      echo "<TD><INPUT TYPE=checkbox NAME=\"reqtools\" VALUE=\"1\"  ></TD></TR>";
    }
    echo "<TR VALIGN=top><TD><B>Name of the action when digging:</B></TD>";
    echo "<TD><SELECT NAME=action>";

    if ($rawtype_info->action == '') {
      echo "<OPTION VALUE=\"\" SELECTED>cannot be digged";
    } else {
      echo "<OPTION VALUE=\"\">cannot be digged";
    }
    if ($rawtype_info->action == 'dig') {
      echo "<OPTION VALUE=dig SELECTED>dig";
    } else {
      echo "<OPTION VALUE=dig>dig";
    }
    if ($rawtype_info->action == 'farm') {
      echo "<OPTION VALUE=farm SELECTED>farm";
    } else {
      echo "<OPTION VALUE=farm>farm";
    }
    if ($rawtype_info->action == 'collect') {
      echo "<OPTION VALUE=collect SELECTED>collect";
    } else {
      echo "<OPTION VALUE=collect>collect";
    }
    if ($rawtype_info->action == 'pump') {
      echo "<OPTION VALUE=pump SELECTED>pump";
    } else {
      echo "<OPTION VALUE=pump>pump";
    }
    if ($rawtype_info->action == 'catch') {
      echo "<OPTION VALUE=catch SELECTED>catch";
    } else {
      echo "<OPTION VALUE=catch>catch";
    }

    echo "</TD></TR>";

    echo "<TR VALIGN=top><TD><B><CANTR REPLACE NAME=form_skill_type>:</B></TD>";
    echo "<TD><SELECT NAME=skill>";

    echo "<OPTION VALUE=0";
    if ($rawtype_info->skill == 0) {
      echo " SELECTED";
    }
    echo ">none</OPTION>";


    $stm = $db->query("SELECT id,name FROM state_types ORDER BY name");
    foreach ($stm->fetchAll() as $state_type_info) {

      echo "<OPTION VALUE=$state_type_info->id";
      if ($rawtype_info->skill == $state_type_info->id) {
        echo " SELECTED";
      }
      echo ">$state_type_info->name</OPTION>";
    }
    echo "</SELECT></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Nutrition:</B><BR>(100 = when you eat 100 grams a day, you gain 2 percent strength; nutrition works on the automatic eating, not the manual possibility; leave blank or zero when this material should not count as daily food)</TD>";
    echo "<TD><INPUT TYPE=text NAME=nutrition VALUE=\"$rawtype_info->nutrition\" SIZE=30></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Strengthening:</B><BR>(150 is a lot, 30 not so much; strengthening is about what you eat manually, not the automatic daily version; leave blank or zero when this material should not be eatable for extra strength)</TD>";
    echo "<TD><INPUT TYPE=text NAME=strengthening VALUE=\"$rawtype_info->strengthening\" SIZE=30></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Energy:</B><BR>(150 is a lot, 30 not so much; energy is about what you eat manually, not the automatic daily version; leave blank or zero when this material should not be eatable for reducing tiredness)</TD>";
    echo "<TD><INPUT TYPE=text NAME=energy VALUE=\"$rawtype_info->energy\" SIZE=30></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Drunkenness:</B><BR>(150 is a lot, 30 not so much; drunkenness is about what you drink manually, not the automatic daily version; leave blank or zero when this material doesn't cause drunkenness when consumed)</TD>";
    echo "<TD><INPUT TYPE=text NAME=drunkenness VALUE=\"$rawtype_info->drunkenness\" SIZE=30></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Tainting (outside):</B><BR>(the percentage you loose when you leave it outside on the ground; leave blank or zero when this material does not taint)</TD>";
    echo "<TD><INPUT TYPE=text NAME=tainting VALUE=\"$rawtype_info->tainting\" SIZE=30></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Target weight for tainting (universal):</B><BR>(weight of the pile that gets half of maximum (" . TextFormat::getPercentFromFraction(DeteriorationConstants::MAX_TAINT, 1) . "%) taint, leave zero when this material does not taint)</TD>";
    echo "<TD><INPUT TYPE=text NAME=taint_target_weight VALUE=\"$rawtype_info->taint_target_weight\" SIZE=30></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Agricultural:</B><BR>(affected by weather [0/1])</TD>";
    echo "<TD><INPUT TYPE=text NAME=agricultural VALUE=\"$rawtype_info->agricultural\" SIZE=30></TD></TR>";

    echo "<TR VALIGN=top VALIGN=top><TD><B>Description:</B><BR>(will only end up in the email concerning this change, not in the database)</TD>";
    echo "<TD><TEXTAREA NAME=description COLS=50 ROWS=5></TEXTAREA></TD>";
    echo "</TR>";
    echo "<TR VALIGN=top VALIGN=top><TD><B>Uses for this resource:</B><BR>(This just covers intrinisc uses and build requirements for objects and machine projects)</TD>";
    echo "<TD>";
    $use = getuse($type, "raw", $db);
    if ($use[0] == 0) {
      echo "No known uses<BR>";
    } else {

      echo "$use[0] known use";
      if ($use[0] != 1) {
        echo "s";
      }
      echo "<BR><UL>";
      for ($i = 1; $i < count($use); $i++) {
        echo "<LI>" . reportuse($use[$i]) . " </LI>";
      }
      echo "</UL>";
    }
    echo "</TD></TR>";

    echo "<INPUT TYPE=hidden NAME=data VALUE=yes>";
    echo "<INPUT TYPE=hidden NAME=rawtype_id VALUE=$rawtype_info->id>";
    echo "<INPUT TYPE=hidden NAME=new VALUE=$new>";

    echo "<TR VALIGN=top><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=\"Store\"></TD></TR>";

    echo "</TABLE></CENTER></FORM>";
  }

  /************* FORM TO SELECT A TOOL TO SPEED UP DIGGING ***************************/

  if ($tool) {

    if ($tool == 'new') {
      $stm = $db->prepare("SELECT * FROM rawtypes WHERE id = :id");
      $stm->bindInt("id", $toolraw);
      $stm->execute();
      $rawtype_info = $stm->fetchObject();
    } else {

      $stm = $db->prepare("SELECT * FROM rawtools WHERE id = :id");
      $stm->bindInt("id", $tool);
      $stm->execute();
      $tool_info = $stm->fetchObject();

      $stm = $db->prepare("SELECT * FROM objecttypes WHERE id = :id");
      $stm->bindInt("id", $tool_info->tool);
      $stm->execute();
      $objecttype_info = $stm->fetchObject();

      $stm = $db->prepare("SELECT * FROM rawtypes WHERE id = :id");
      $stm->bindInt("id", $tool_info->rawtype);
      $stm->execute();
      $rawtype_info = $stm->fetchObject();
    }

    echo "<FORM METHOD=post ACTION=\"index.php?page=managerawtypes\"><CENTER><TABLE WIDTH=700>";

    echo "<TR VALIGN=top><TD WIDTH=200><B>Raw type:</B></TD>";
    echo "<TD WIDTH=400>$rawtype_info->name</TD></TR>";

    echo "<TR VALIGN=top><TD><B>Tool:</B></TD>";
    echo "<TD><SELECT NAME=object_id>";

    $stm = $db->query("SELECT * FROM objecttypes ORDER BY name");
    foreach ($stm->fetchAll() as $option_info) {
      if ($objecttype_info->id == $option_info->id) {
        echo "<OPTION VALUE=$option_info->id SELECTED>$option_info->unique_name";
      } else {
        echo "<OPTION VALUE=$option_info->id>$option_info->unique_name";
      }
    }

    echo "</SELECT></TD></TR>";

    echo "<TR VALIGN=top><TD><B>Amount to dig per day:</B><BR>(percentage - thus 200 means twice as much as without this tool)</TD>";
    echo "<TD><INPUT TYPE=text NAME=perday VALUE=\"$tool_info->perday\" SIZE=10></TD></TR>";

    echo "<TR VALIGN=top VALIGN=top><TD><B>Description:</B><BR>(will only end up in the email concerning this change, not in the database)</TD>";
    echo "<TD><TEXTAREA NAME=description COLS=50 ROWS=5></TEXTAREA></TD></TR>";

    echo "<INPUT TYPE=hidden NAME=data2 VALUE=yes>";
    echo "<INPUT TYPE=hidden NAME=rawtype VALUE=$rawtype_info->id>";
    echo "<INPUT TYPE=hidden NAME=tool VALUE=$tool>";

    echo "<TR VALIGN=top><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=\"Store\"></TD></TR>";

    echo "</TABLE></CENTER></FORM>";
  }

  /************* OVERVIEW RAW MATERIAL TYPES ********************************/

  $globalConfig = new GlobalConfig($db);
  if ($enableTaint) {
    $value = $enableTaint == "yes" ? 1 : 0;
    $globalConfig->setUniversalTaintEnabled($value);
  }

  $taintEnabled = $globalConfig->isUniversalTaintEnabled();
  echo "
    <div class='page'>
      Universal taint is ". ($taintEnabled ? "enabled" : "disabled") .".
      <form action='index.php?page=managerawtypes' method='post'>
        <input type='hidden' name='enable_taint' value='". ($taintEnabled ? "no" : "yes") ."'/>
        <input type='submit' value='". ($taintEnabled ? "disable" : "enable") ."' class='button_action'>
      </form>
    </div>";

  echo "<CENTER><TABLE WIDTH=700 border=1 bordercolor='#005500'><TR><TD>";

  echo "<TR><TD></TD>";
  echo "<TD><I>name</I></TD>";
  echo "<TD><I>digging</I></TD>";
  echo "<TD><I>skill</I></TD>";
  echo "<TD><I>nutrition</I></TD>";
  echo "<TD><I>strength.</I></TD>";
  echo "<TD><I>energy.</I></TD>";
  echo "<TD><I>drunkenness.</I></TD>";
  echo "<TD><I>tainting (outside)</I></TD>";
  echo "<TD><I>target weight for tainting (universal)</I></TD>";
  echo "<TD><I>agricultural</I></TD>";
  echo "</TR>";

  $stm = $db->query("SELECT * FROM rawtypes ORDER BY name");
  foreach ($stm->fetchAll() as $rawtype_info) {

    echo "<TR>";

    echo "<TD><A HREF=\"index.php?page=managerawtypes&type=$rawtype_info->id\">[edit]</A></TD>";

    echo "<TD><B>$rawtype_info->name</B></TD>";

    if ($rawtype_info->perday) {
      if ($rawtype_info->reqtools == 1) {
        echo "<TD>$rawtype_info->action 0 grams per day (tooled only)</TD>";
      } else {
        echo "<TD>$rawtype_info->action $rawtype_info->perday grams per day</TD>";
      }
    } else {
      echo "<TD></TD>";
    }

    if ($rawtype_info->skill) {
      $stm = $db->prepare("SELECT name FROM state_types WHERE id = :id LIMIT 1");
      $stm->bindInt("id", $rawtype_info->skill);
      $stm->execute();
      $state_type_info = $stm->fetchObject();

      echo "<TD>" . $state_type_info->name . "</TD>";
    } else {
      echo "<TD></TD>";
    }

    if ($rawtype_info->nutrition) {
      echo "<TD>$rawtype_info->nutrition</TD>";
    } else {
      echo "<TD></TD>";
    }

    if ($rawtype_info->strengthening) {
      echo "<TD>$rawtype_info->strengthening</TD>";
    } else {
      echo "<TD></TD>";
    }

    if ($rawtype_info->energy) {
      echo "<TD>$rawtype_info->energy</TD>";
    } else {
      echo "<TD></TD>";
    }

    if ($rawtype_info->drunkenness) {
      echo "<TD>$rawtype_info->drunkenness</TD>";
    } else {
      echo "<TD></TD>";
    }

    if ($rawtype_info->tainting) {
      echo "<TD>$rawtype_info->tainting</TD>";
    } else {
      echo "<TD></TD>";
    }

    if ($rawtype_info->taint_target_weight) {
      echo "<TD>$rawtype_info->taint_target_weight</TD>";
    } else {
      echo "<TD></TD>";
    }

      echo "<TD>$rawtype_info->agricultural</TD>";

    echo "</TR>";

    $stm = $db->prepare("SELECT * FROM rawtools WHERE rawtype = :rawtype AND projecttype=1");
    $stm->bindInt("rawtype", $rawtype_info->id);
    $stm->execute();
    foreach ($stm->fetchAll() as $tool_info) {

      $stm = $db->prepare("SELECT * FROM objecttypes WHERE id = :id");
      $stm->bindInt("id", $tool_info->tool);
      $stm->execute();
      $objecttype_info = $stm->fetchObject();

      echo "<TR><TD></TD><TD COLSPAN=10>";
      echo "<A HREF=\"index.php?page=managerawtypes&tool=$tool_info->id\">[edit]</A> ";

      $perday = floor($tool_info->perday / 100 * $rawtype_info->perday);

      echo "<FONT COLOR=\"#AAAAAA\">With $objecttype_info->unique_name you can $rawtype_info->action $perday grams per day.</TD></TR>";
    }

    echo "<TR><TD></TD><TD COLSPAN=10><A HREF=\"index.php?page=managerawtypes&tool=new&toolraw=$rawtype_info->id\">[add tool]</A></TD></TR>";
  }

  echo "<TR><TD COLSPAN=11><BR><CENTER><A HREF=\"index.php?page=managerawtypes&new=yes\">Add new raw material</A></CENTER></TD></TR>";
  echo "<TR><TD COLSPAN=11><BR><CENTER><A HREF=\"index.php?page=player\">Back to player page</A></CENTER></TD></TR>";

  echo "</TD></TR></TABLE></CENTER>";
