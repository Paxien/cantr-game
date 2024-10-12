<?php
require_once('func.rules.inc.php');

// SANITIZE INPUT
$removeproj = HTTPContext::getInteger('removeproj');
$removeobj = HTTPContext::getInteger('removeobj');
$locid = HTTPContext::getInteger('locid');
$restore = HTTPContext::getRawString('restore');
$raw_name = HTTPContext::getRawString('raw_name');
$create_raw = HTTPContext::getRawString('create_raw');
$amount = HTTPContext::getInteger('amount');
$projects = HTTPContext::getArray("projects");

$db = Db::get();

function restoreRaws($rawsArray, $locid) {
  if (empty($rawsArray)) return;
  foreach ($rawsArray as $raw => $amount) {
    $rawtype_id = ObjectHandler::getRawIdFromName($raw);
    ObjectHandler::rawToLocation($locid, $rawtype_id, $amount);
  }
}

function restoreObjects($objectsArray, $locid, Db $db) {
  if (empty($objectsArray)) return;
  foreach ($objectsArray as $obj => $amount) {
    // Fetch first match. A bit random, but we don't know exactly what kind of wire or button was used.
    $stm = $db->prepare("SELECT id, build_result FROM objecttypes WHERE name = :name LIMIT 1");
    $stm->bindStr("name", $obj);
    $stm->execute();
    $objecttype = $stm->fetchObject();
    if ($objecttype) {
      $object_details = get_object_vars(explodeBuildReq($objecttype->build_result));
      $weight = $object_details["objects.add"]->weight;
      ObjectCreator::inLocation($locid, $objecttype->id, ObjectConstants::SETTING_PORTABLE, $weight)->create();
    }
  }
}

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {

  if ($removeproj) {
    $stm = $db->prepare("UPDATE chars SET project = 0 WHERE project = :projectId");
    $stm->bindInt("projectId", $removeproj);
    $stm->execute();
    $stm = $db->prepare("DELETE FROM projects WHERE id = :projectId");
    $stm->bindInt("projectId", $removeproj);
    $stm->execute();
  }

  if ($removeobj) {
    $stm = $db->prepare("DELETE FROM objects WHERE id = :objectId");
    $stm->bindInt("objectId", $removeobj);
    $stm->execute();
  }

  if ($restore) {
    $raws_to_restore = array();
    $objects_to_restore = array();
    foreach ($projects as $project_id) {
      $stm = $db->prepare("UPDATE chars SET project = 0 WHERE project = :projectId");
      $stm->bindInt("projectId", $project_id);
      $stm->execute();
      $stm = $db->prepare("SELECT * FROM projects WHERE id = :projectId");
      $stm->bindInt("projectId", $project_id);
      $stm->execute();
      $projInfo = $stm->fetchObject();

      $resneeded = get_object_vars(explodeBuildReq($projInfo->reqneeded));
      $resleft = get_object_vars(explodeBuildReq($projInfo->reqleft));

      if (array_key_exists("raws", $resneeded)) {
        if ($resneeded["raws"] != $resleft["raws"]) {
          $res_left_raws = get_object_vars($resleft["raws"]);
          foreach ($resneeded["raws"] as $raw => $val) {
            if ($res_left_raws[$raw] != $val) {
              $raws_to_restore[$raw] += ($val - $res_left_raws[$raw]);
            }
          }
        }
      }

      if (array_key_exists("objects", $resneeded)) {
        if ($resneeded["objects"] != $resleft["objects"]) {
          $res_left_objs = get_object_vars($resleft["objects"]);
          foreach ($resneeded["objects"] as $object => $val) {
            if ($res_left_objs[$object] != $val) {
              $objects_to_restore[$object] += ($val - $res_left_objs[$object]);
            }
          }
        }
      }

      $stm = $db->prepare("DELETE FROM projects WHERE id = :projectId");
      $stm->bindInt("projectId", $project_id);
      $stm->execute();
    }

    if (!empty($raws_to_restore)) {
      restoreRaws($raws_to_restore, $locid);
    }

    if (!empty($objects_to_restore)) {
      restoreObjects($objects_to_restore, $locid, $db);
    }
  }

  if ($create_raw) {
    $raw_to_restore = array($raw_name => $amount);
    restoreRaws($raw_to_restore, $locid);
  }

  show_title("INFO LOCATION");

  echo "<center><table width=700><tr><td align=left>";

  $stm = $db->prepare("SELECT l.*, oln.name AS oldname FROM locations l
    INNER JOIN oldlocnames oln ON oln.id = l.id WHERE l.id = :locationId LIMIT 1");
  $stm->bindInt("locationId", $locid);
  $locInfo = $stm->fetchObject();
  
  if (!$locInfo) {
    $stm = $db->prepare("SELECT * FROM locations WHERE id = :locationId LIMIT 1");
    $stm->bindInt("locationId", $locid);
    $stm->execute();
    $locInfo = $stm->fetchObject();
  }

  echo "Location <b>#{$locInfo->id}</b> {$locInfo->oldname}".($locInfo->name ? " ($locInfo->name)" : "")."</br>";

  show_title ("PROJECTS");
  echo "Clicking the remove-link will remove that project and <strong>not</strong>"
    . " restore raws or objects. Using the checkboxes will remove <strong>all</strong>"
    . " the checked projects <strong>and</strong> restore raws and objects.<br /><br />"
    . " Below projects that have requirements, there are two lines; the requirements,"
    . " and the requirements that are left to fill (req-left).<br /><br />";
  $stm = $db->prepare("SELECT * FROM projects p WHERE location = :locationId");
  $stm->bindInt("locationId", $locid);
  $stm->execute();
  echo "<form method=\"post\" name=\"restore_form\" action=\"index.php?page=infoloc&locid={$locid}\">";
  foreach ($stm->fetchAll() as $projInfo) {
    $progress = $projInfo->turnsneeded ? 100 - 100 * $projInfo->turnsleft / $projInfo->turnsneeded : 0;
    $resneeded = array();
    $reqObject = explodeBuildReq($projInfo->reqneeded);
    if ($reqObject) {
      $resneeded = get_object_vars($reqObject);
    }
    if (array_key_exists("raws", $resneeded) || array_key_exists("objects", $resneeded)) {
      echo "<input type=\"checkbox\" name=\"projects[]\" value=\"$projInfo->id\" />";
    } else {
      echo "<img src=\"graphics/cantr/pictures/checkbox_disabled.png\" style=\"padding:0px 2px;\" />";
    }
    echo "<a href=\"index.php?page=infoloc&locid={$locid}&removeproj=$projInfo->id\">[remove]</a> ".sprintf ("%02d%%", $progress)." {$projInfo->name}";
    echo "<br /> &nbsp; &nbsp; " . $projInfo->reqneeded;
    echo "<br /> &nbsp; &nbsp; " . $projInfo->reqleft;
    echo "<br />";
  }
  echo "<input type=\"submit\" id=\"restore\" name=\"restore\" value=\"Remove and restore checked projects\" />";
  echo "</form>";

  show_title("INFO RAWS");
  $stm = $db->prepare("SELECT o.*, rt.name AS rawname FROM objects o
    INNER JOIN rawtypes rt ON o.typeid = rt.id WHERE o.location = :locationId AND o.type = 2");
  $stm->bindInt("locationId", $locid);
  $stm->execute();

  foreach ($stm->fetchAll() as $objInfo) {
    echo "<a href=\"index.php?page=infoloc&locid={$locid}&removeobj=$objInfo->id\">[remove]</a> {$objInfo->weight}g of {$objInfo->rawname}<br />";
  }

  //Just a dummy character to remove a ton of errors in logs.
  $stm = $db->prepare("SELECT id FROM chars ORDER BY language LIMIT 1");
  $charId = $stm->executeScalar();
  show_title("INFO OBJECTS");

  $objects = CObject::locatedIn($locid)->exceptType(ObjectConstants::TYPE_RAW)->findAll();
  foreach ($objects as $object) {
    $objectView = new ObjectView($object, Character::loadById($charId));
    $datagot = $objectView->show('object');
    echo "<a href=\"index.php?page=infoloc&locid={$locid}&removeobj=" . $object->getId() . "\">[remove]</a> $datagot->text<br />";
  }
  unset($charId);

  show_title("CREATE RAW MATERIALS in <b>#{$locInfo->id}</b> {$locInfo->oldname}" . ($locInfo->name ? " ($locInfo->name)" : ""));
  echo "<form method=\"post\" name=\"create_raw__form\" action=\"index.php?page=infoloc&locid={$locid}\">";
  echo "<select name=\"raw_name\">";
  $stm = $db->query("SELECT name FROM rawtypes ORDER BY name");
  foreach ($stm->fetchAll() as $rawtypes_info) {
    echo "<option value=\"$rawtypes_info->name\">$rawtypes_info->name";
  }
  echo "</select> &nbsp; Amount: <input type=\"text\" size=\"5\" name=\"amount\" />";
  echo " &nbsp; <input type=\"submit\" id=\"create_raw\" name=\"create_raw\" /></form>";

  echo "</td></tr></table>";
  echo "<A HREF=\"index.php?page=ce\">Back to Cantr Explorer</A><br />";
  echo "<A HREF=\"index.php?page=player\">Back to player page</A><br />";
  echo "</center>";
} else {
  CError::throwRedirect("player", "You are not authorized to read the players info");
}
