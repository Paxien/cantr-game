<?php

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

echo '<table class="ceRow" cellSpacing="0" cellPadding="0" width="100%">';

require_once '../lib/stddef.inc.php';
require_once '../lib/header.functions.inc.php';

$db = Db::get();

$s = session::getSessionFromCookie();

$stm = $db->prepare("SELECT * FROM sessions WHERE id = :id");
$stm->bindInt("id", $s);
$stm->execute();
$session = $stm->fetchObject();

require 'ce.func.inc.php';

$parent = $_REQUEST['parent'];
$Logged = false;
if ($parent) {
  $parent = substr($parent, 2);
}

function TC($text)
{
  $in = array("�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�");
  $out = array(
    "\xc4\x84", // �
    "\xc4\x85", // �
    "\xc4\x86", // �
    "\xc4\x87", // �
    "\xc4\x98", // �
    "\xc4\x99", // �
    "\xc5\x81", // �
    "\xc5\x82", // �
    "\xc5\x83", // �
    "\xc5\x84", // �
    "\xc3\xb2", // �
    "\xc3\xb3", // �
    "\xc5\x9a", // �
    "\xc5\x9b", // �
    "\xc5\xb9", // �
    "\xc5\xba", // �
    "\xc5\xbb", // �
    "\xc5\xbc"  // �
  );
  $text = str_replace($in, $out, $text);
  return $text;
}

function DoID($id, $digits = 5)
{
  return "<span class=cex_id>" . sprintf("%0{$digits}d", $id) . "</span>";
}

function LocationName($name, $type)
{
  return "<i><font color=#" . ($type == 1 ? "ffff99" : "d0ffd0") . ">" . htmlspecialchars(($name ? TC($name) : "Unnamed location")) . "</font></i>";
}

function DoCharacter($id, $name)
{

  DoLine("char", "char=$id", TC("<i><font color=#ffc0c0>$name</font></i>"),
    "", DoID($id));
}

function DoPlayer($id, $firstname, $lastname, $pretext = "", $posttext = "", $addparams = "")
{
  if ($firstname !== null || $lastname !== null) {
    DoLine("player", "plyr=$id" . $addparams, TC("<i><font color=#ffc0c0>$firstname $lastname</font></i>"),
      $pretext, DoID($id) . " <a href=\"index.php?page=infoplayer&player_id=$id\">" .
      GetImg("cepage") . " " . $posttext);
  } else {
    DoLine("unplayer", "plyr=$id" . $addparams, "<i><font color=#ffc0c0>Retired player #$id</font></i>",
      $pretext, $posttext);
  }
}

function DoMessage($message)
{
  DoLine("", "", "", "<font color=#a8a8a8>$message</font>", "");
}

function DoError($message)
{
  DoLine("", "", "", "<i><font color=#ff8080>Error: $message</font></i>", "");
}

function DoLine($request, $data, $link, $pre, $post)
{
  global $treeLines;
  $T = new stdClass();
  $T->request = $request;
  $T->data = $data;
  $T->link = $link;
  $T->pre = $pre;
  $T->post = $post;
  $treeLines [] = clone $T;
}

function DoCheckbox($request, $data, $link, $pre, $post, $value)
{
  global $treeLines;
  $T = new stdClass();
  $T->request = $request;
  $T->data = $data;
  $T->link = $link;
  $T->pre = $pre;
  $T->post = $post;
  $T->checkbox = $value;
  $treeLines [] = clone $T;
}

function WriteLine(/*$request, $data, $link, $pre, $post*/
  $T, $last = 0)
{
  global $lineCount, $indent, $ceiconwidth;
  $itemid = "ce" . $GLOBALS ['parent'] . sprintf("%03x", $lineCount);
  $checkbox = $T->checkbox !== null;
  $CalculateLastIcon = $indent [strlen($indent) - 1] < 2;
  $IconsWidth = (strlen($indent) + ($CalculateLastIcon ? 2 : 1)) * $ceiconwidth;
  $R = "
    <tr vAlign=top height=$ceiconwidth>
    <td align=left vAlign=top class=cex height=$ceiconwidth id=\"$itemid\">
    <table class=\"ceRow\" cellSpacing=0 cellPadding=0><tr><td vAlign=top width=$IconsWidth>";

  $indentPics = array(0 => "cetem", "cetve", "cetco", "cetcr");
  for ($i = 0; $i < strlen($indent); $i++) {
    $R .= GetImg($indentPics [$indent [$i]]);
  }
  if ($CalculateLastIcon) {
    $R .= GetImg("cet" . ($last ? "co" : "cr"));
  }
  // '+' or '-' icon
  if ($T->link != "" && !$checkbox) {
    $R .= GetImg("cetpl"/*.($lineCount ? "u" : "").($last ? "" : "d").*/);
  } else {
    $R .= GetImg("cetsi");
  }
  $R .= "</td><td align=left>";
  if ($checkbox) {
    $R .= GetImg($T->checkbox ? "cecby" : "cecbn") . " ";
  }
  $R .= "$T->pre<a href='JavaScript:;' onclick='"
    . (!$checkbox ? "AjaxAdd" : "this.innerHTML = \"wait...\"; AjaxReplace")
    . " (\"$itemid\", \"$T->request\", \"$T->data\", \"" . $indent;

  if (!$checkbox) {
    $R .= $last ? "0" : "1";
  } elseif ($CalculateLastIcon) {
    $R .= $last ? "2" : "3";
  }

  $R .= "\")'>$T->link</a> $T->post</td></tr></table></td></tr>";
  $lineCount++;
  return $R;
}

$req = $_REQUEST['req'];
$id = $_REQUEST['id'];
$loc = $_REQUEST['loc'];
$loctype = $_REQUEST['loctype'];
$type = $_REQUEST['type'];
$byid = $_REQUEST['byid'];
$usecustom = $_REQUEST['usecustom'];
$name = $_REQUEST['name'];
$plyr = $_REQUEST['plyr'];
$prvl = $_REQUEST['prvl'];
$conn = $_REQUEST['conn'];
$start = $_REQUEST['start'];
$end = $_REQUEST['end'];
$veh = $_REQUEST['veh'];
$st = $_REQUEST['st'];
$cid = $_REQUEST['cid'];
$cname = $_REQUEST['cname'];
$pid = $_REQUEST['pid'];
$locid = $_REQUEST['locid'];
$agefrom = $_REQUEST['agefrom'];
$ageto = $_REQUEST['ageto'];
$bornfrom = $_REQUEST['bornfrom'];
$bornto = $_REQUEST['bornto'];
$lang = $_REQUEST['lang'];
$knowas = $_REQUEST['knowas'];
$status = $_REQUEST['status'];
$first = $_REQUEST['first'];
$last = $_REQUEST['last'];
$email = $_REQUEST['email'];
$ip = $_REQUEST['ip'];
$indent = $_REQUEST['indent'];
$char = $_REQUEST['char'];
$val = $_REQUEST['val'];


switch ($req) {

  // =================================
  case "mode":
    include 'ce.panes.inc.php';
    break;

  // =================================
  case "loc":
  case "veh":
  case "bld":

    if ($req == "loc") {
      if (!HasPrivillege('maLocs')) {
        break;
      }
    }
    if ($req == "bld") {
      if (!HasPrivillege('maBuilds')) {
        break;
      }
    }
    if ($req == "veh") {
      if (!HasPrivillege('maVehs')) {
        break;
      }
    }

    switch ($req) {
      case "loc":
        $loccond = "type = 1";
        ceLog("List of locations \"$name\"");
        break;
      case "bld":
        $loccond = "type = 2";
        ceLog("List of buildings \"$name\"");
        break;
      default:
        $loccond = "type > 2";
        ceLog("List of vehicles \"$name\"");
    }

    $countA = 0;
    $countB = 0;

    // If $byid is set we want to search for exact ID only.

    if (!$byid) {
      $stm = $db->prepare("
        SELECT count(*) FROM " . ($req == "loc" ? "oldlocnames" : "locations") .
        " WHERE name LIKE :name" . ($req == "loc" ? "" : " AND $loccond"));
      $stm->bindStr("name", "%$name%");
      $countA = $stm->executeScalar();
      echo "SELECT count(*) FROM " . ($req == "loc" ? "oldlocnames" : "locations") .
        " WHERE name LIKE '%$name%'" . ($req == "loc" ? "" : " AND $loccond");
      if ($usecustom) {
        $stm = $db->prepare("
          SELECT COUNT(DISTINCT observed) FROM charnaming chn INNER JOIN locations l ON observed = l.id AND l.$loccond
          WHERE chn.type = 2 AND chn.name LIKE :name");
        $stm->bindStr("name", "%$name%");
        $countB = $stm->executeScalar();
      }
    }

    $max = 50;

    if ($countA + $countB <= $max && $countA + $countB > 0) {
      $stm = $db->prepare("SELECT id, name, " . ($req == "loc" ? "1 AS type" : "type") . " FROM " . ($req == "loc" ? "oldlocnames" : "locations") .
        " WHERE name LIKE :name " . ($req == "loc" ? " " : " AND $loccond"));
      $stm->bindStr("name", "%$name%");
      $stm->execute();
      foreach ($stm->fetchAll() as $X) {
        $locs [$X->id] = $X;
        $locids [$X->id] = $locs[$X->name];
      }

      // search in custom names
      if ($usecustom) {
        $stm = $db->prepare("
        SELECT COUNT(*) AS num, chn.observed AS id, oln.name, l.type, l.name AS locname
        FROM charnaming chn
        INNER JOIN locations l ON (chn.type = 2 AND l.$loccond AND chn.observed = l.ID)
        LEFT OUTER JOIN oldlocnames oln ON oln.id = l.id
        WHERE chn.name LIKE :name
        GROUP BY observed");
        $stm->bindStr("name", "%$name%");
        $stm->execute();
        foreach ($stm->fetchAll() as $X) {
          if ($locids [$X->id]) {
            $temp = $locids [$X->id];
            $temp->num = $X->num;
          } else {
            $X->custom = true;
            if ($X->name == "") {
              $X->name = $X->locname;
            }
            $locs [$X->id] = $X;
          }
        }
      }

      usort($locs, function($a, $b) {
        return strcmp($a->name, $b->name);
      });

      foreach ($locs as $X) {
        DoLine("openloc", "loc=$X->id"
          . ($X->custom ? "&cname=" . urlencode($name) : ""), LocationName($X->name, $X->type),
          "", DoID($X->id) .
          "<a href=\"index.php?page=infoloc&locid=$X->id\">" . GetImg("celoctools", "png") . "</a>" .
          ($X->custom ? " (" . $X->num . " match" . ($X->num != 1 ? "es" : "") . ")" : ""));
      }
    } elseif ($countA + $countB == 0) {
      // If both countA and countB are 0 and $byid is set, search is for exact ID
      if ($byid && is_numeric($name)) {
        $stm = $db->prepare("SELECT id, name, " . ($req == "loc" ? "1 AS type" : "type")
          . " FROM " . ($req == "loc" ? "oldlocnames" : "locations") . " WHERE id = :id " . ($req == "loc" ? " " : " AND $loccond") . " LIMIT 1");
        $stm->bindInt("id", $name);
        $stm->execute();
        $X = $stm->fetchObject();
        DoLine("openloc", "loc=$X->id", LocationName($X->name, $X->type),
          "", DoID($X->id) .
          "<a href=\"index.php?page=infoloc&locid=$X->id\">" . GetImg("celoctools", "png") . "</a>");
      } else {
        DoLine("", "", "", "No items follow your search criteria.", "");
      }
    } else {
      DoLine("", "", "", ($countA + $countB) . " locations follow your search criteria. Please be more specific.", "");
    }

    break;

  // =================================
  case "openloc":

    if (!HasPrivillege('openLoc')) {
      break;
    }

    ceLog("Options for location " . gLocation($loc));
    $stm = $db->prepare("SELECT * FROM locations WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $loc);
    $stm->execute();
    $locdata = $stm->fetchObject();
    $outside = $locdata->type == 1;

    if ($cname) {
      DoLine("loccnames", "loc=$loc&cname=$cname", "matched custom names", "", "");
    }
    if ($outside) {
      DoLine("locraws", "loc=$loc", "raw resources", "", "");
      DoLine("locanims", "loc=$loc", "animals", "", "");
    }
    // location type name
    if ($locdata->type > 1) {
      $stm = $db->prepare("SELECT name FROM objecttypes WHERE id = :objectTypeId");
      $stm->bindInt("objectTypeId", $locdata->area);
      $ltname = $stm->executeScalar();
      DoLine("", "", "", "type: " . $ltname, "");
    }
    DoLine("charinloc", "loc=$loc&loctype=$locdata->type", "characters", "", "");
    DoLine("plyrinloc", "loc=$loc", "players", "", "");
    //
    if ($outside) {
      DoLine("loctravs", "loc=$loc", "travellers", "", "");
      DoLine("subloc", "loc=$loc&veh=1", "vehicles", "", "");
    }
    DoLine("subloc", "loc=$loc", $outside ? "buildings" : "rooms", "", "");
    if ($locdata->type > 1 && $locdata->type < 5 && $locdata->region > 0) {
      $stm = $db->prepare("SELECT l.name, type, oln.name AS oldname FROM locations l
      LEFT OUTER JOIN oldlocnames oln ON l.id = oln.id WHERE l.id = :locationId LIMIT 1");
      $stm->bindInt("locationId", $locdata->region);
      $parentloc = $stm->executeScalar();
      DoLine("openloc", "loc=$locdata->region", LocationName($parentloc->name ? $parentloc->name : $parentloc->oldname, $parentloc->type),
        $locdata->type == 2 ? "parent location/builidng: " : "in/docked to: ", DoID($locdata->region));
    }
    if ($outside) {
      DoLine("conns", "loc=$loc", "connections", "", "");
      $coasts = array(0 => array(0 => "no", 1 => "lake"), 1 => array(0 => "sea", 1 => "booth sea and lake"));
      DoLine("", "", "", "coastal: " . $coasts [$locdata->borders_sea][$locdata->borders_lake], "");
      DoLine("", "", "", "digging slots: " . $locdata->digging_slots, "");
    }
    if (($locdata->type == 1 || $locdata->type == 5) && $locdata->x) {
      DoLine("", "", "", "position: x = " . $locdata->x . ", y = " . $locdata->y, "");
    }

    break;

  // =================================
  case "conns":

    if (!HasPrivillege('locConn')) {
      break;
    }
    $loc = intval($loc);
    ceLog("Connections of location " . gLocation($loc));
    $stm = $db->prepare("
    SELECT c.start, c.end, c.id, c.direction, n1.name AS name1, n2.name AS name2, ct.name AS ctname FROM connections c
    INNER JOIN connecttypes ct ON ct.id = c.type
    INNER JOIN oldlocnames n1 ON n1.id = c.start
    INNER JOIN oldlocnames n2 ON n2.id = c.end
    WHERE (start = :locationId1 OR end = :locationId2)");
    $stm->bindInt("locationId1", $loc);
    $stm->bindInt("locationId2", $loc);
    $stm->execute();
    foreach ($stm->fetchAll() as $X) {
      if ($X->start == $loc) {
        DoLine("conn", "conn=$X->id&loc=$loc", $X->ctname . " to " . LocationName($X->name2, 1), "",
          DoID($X->id));
      } else {
        DoLine("conn", "conn=$X->id&loc=$loc", $X->ctname . " to " . LocationName($X->name1, 1), "",
          DoID($X->id));
      }
    }
    if ($stm->rowCount() == 0) {
      DoMessage("no connections found");
    }
    break;

  // =================================
  case "conn":
    if (!HasPrivillege('openConn')) {
      break;
    }

    $stm = $db->prepare("
    SELECT c.start, length, c.end, c.id, c.direction,
      n1.name AS name1, n2.name AS name2, ct.name AS ctname FROM connections c
    INNER JOIN connecttypes ct ON ct.id = c.type
    INNER JOIN oldlocnames n1 ON n1.id = c.start
    INNER JOIN oldlocnames n2 ON n2.id = c.end
    WHERE c.id = :id");
    $stm->bindInt("id", $conn);
    $stm->execute();
    $X = $stm->fetchObject();
    if ($X->start == $loc) {
      DoLine("openloc", "loc=$X->end", LocationName($X->name2, 1), "destination: ", DoID($X->end));
    } else {
      DoLine("openloc", "loc=$X->start", LocationName($X->name1, 1), "destination: ", DoID($X->start));
    }
    DoLine("conntrav", "conn=$conn&loc=$loc", "travelers and vehicles", "", "");
    DoLine("", "", "", "type: " . $X->ctname, "");
    DoLine("", "", "", "direction: " . ($X->start == $loc ? $X->direction : ($X->direction + 180) % 360) . "&deg;", "");
    DoLine("", "", "", "length: " . $X->length, "");
    ceLog("Options for connection " . gLocation($X->start) . " - " . gLocation($X->end));
    break;

  // =================================
  case "conntrav":

    if (!HasPrivillege('connTrav')) {
      break;
    }


    $stm = $db->prepare("SELECT start, end FROM connections WHERE id = :id");
    $stm->bindInt("id", $conn);
    $stm->execute();
    list ($start, $end) = $stm->fetch(PDO::FETCH_NUM);
    ceLog("Travellers on connection " . gLocation($start) . " - " . gLocation($end));

    $stm = $db->prepare("
    SELECT t.*, n1.name AS name1, n2.name AS name2 FROM travels t
    INNER JOIN oldlocnames n1 ON n1.id = t.locfrom
    INNER JOIN oldlocnames n2 ON n2.id = t.locdest
    WHERE t.connection = :id");
    $stm->bindInt("id", $conn);
    $stm->execute();

    if ($stm->rowCount() > 0) {
      foreach ($stm->fetchAll() as $X) {
        if ($X->locfrom == $loc) {
          $pos = 100 * $X->travleft / $X->travneeded;
        } else {
          $pos = 100 * $X->travleft / $X->travneeded;
        }
        $travs [$pos][] = $X;
      }
      ksort($travs);

      foreach ($travs as $T)
        foreach ($T as $X) {
          $perc = round(100 - 100 * $X->travleft / $X->travneeded) . "%";
          if ($X->speed > 0) {
            $to = "at $perc to " . LocationName($X->name2, 1) . ", speed $X->speed<small>/$X->maxspeed</small>";
          } else {
            if ($X->speed < 0) {
              $to = "at $perc to " . LocationName($X->name1, 1) . ", speed $X->speed<small>/$X->maxspeed</small>";
            } else {
              $to = "stopped at $perc to " . LocationName($X->name1, 1) . ", max. speed $X->maxspeed";
            }
          }

          if ($X->type == 0) { // by foot
            $stm = $db->prepare("SELECT name FROM chars WHERE id= :charId");
            $stm->bindInt("charId", $X->person);
            $person = $stm->executeScalar();
            DoLine("char", "char=$X->person", "<i><font color=#b4faf6>" . htmlentities($person) . "</font></i>",
              "", DoID($X->person) . " " . $to);
          } else { // vehicle
            $stm = $db->prepare("SELECT id, name FROM locations WHERE id = :locationId");
            $stm->bindInt("locationId", $X->person);
            $stm->execute();
            $vehicle = $stm->fetchObject();
            DoLine("openloc", "loc=$vehicle->id", LocationName($vehicle->name, 2),
              "", DoID($vehicle->id) . " " . $to);
            $stm = $db->prepare("SELECT id, name FROM chars WHERE location = :locationId");
            $stm->bindInt("locationId", $X->person);
            $stm->execute();
            foreach ($stm->fetchAll() as $person) {
              DoLine("char", "char=$person->id", "<i><font color=#b4faf6>" . htmlentities($person->name) . "</font></i>",
                "", DoID($person->id) . " in " . LocationName($vehicle->name, 2));
            }
          }
        }
    } else {
      DoMessage("no people on this road");
    }
    break;

  // =================================
  case "subloc":

    if ($veh) {
      if (!HasPrivillege('listVehs')) {
        break;
      }
    }
    if (!$veh) {
      if (!HasPrivillege('listBlds')) {
        break;
      }
    }
    $veh = intval($veh);
    $loc = intval($loc);
    ceLog(($veh ? "Vehicles" : "Buildings") . " in " . gLocation($loc));
    $stm = $db->prepare("SELECT id, name, type FROM locations WHERE region = :locationId AND type " . ($veh ? "> 2" : "= 2") . " ORDER BY name");
    $stm->bindInt("locationId", $loc);
    $stm->execute();
    foreach ($stm->fetchAll() as $X) {
      DoLine("openloc", "loc=$X->id", LocationName($X->name, $X->type), "", DoID($X->id) .
        "<a href=\"index.php?page=infoloc&locid=$X->id\">" . GetImg("celoctools", "png") . "</a>");
    }
    if ($stm->rowCount() == 0) {
      DoMessage("no sublocations found");
    }
    break;

  // =================================
  case "locraws":

    if (!HasPrivillege('locRaws')) {
      break;
    }

    $loc = intval($loc);
    ceLog("Raws of " . gLocation($loc));
    $stm = $db->prepare("SELECT rt.name, rt.id FROM raws r INNER JOIN rawtypes rt ON rt.id = r.type WHERE r.location = :locationId");
    $stm->bindInt("locationId", $loc);
    $stm->execute();
    foreach ($stm->fetchAll() as $X) {
      DoLine("raw", "raw=$X->id&loc=$loc", "<i>$X->name</i>", "", "");
    }
    if ($stm->rowCount() == 0) {
      DoMessage("no resources found");
    }
    break;

  // =================================
  case "locanims":

    if (!HasPrivillege('locAnms')) {
      break;
    }

    $loc = intval($loc);
    ceLog("Animals of " . gLocation($loc));
    $stm = $db->prepare("SELECT at.name, at.id, a.type, number AS num FROM animals a INNER JOIN animal_types at ON at.id = a.type
      WHERE a.location = :locationId GROUP BY a.type");
    $stm->bindInt("locationId", $loc);
    $stm->execute();
    foreach ($stm->fetchAll() as $X) {
      DoLine("locanim", "type=$X->type&loc=$loc", "<i>{$X->name}s</i> ($X->num)", "", "");
    }
    if ($stm->rowCount() == 0) {
      DoMessage("no animals found");
    }
    break;

  // =================================
  case "locanim":
    if (!HasPrivillege('locAnm')) {
      break;
    }
    ceLog("List of " . gAnimal($type) . "s in " . gLocation($loc));
    $stm = $db->prepare("SELECT a.*, at.name, at.strength FROM animals a INNER JOIN animal_types at ON at.id = a.type
      WHERE a.location = :locationId AND type = :type");
    $stm->bindInt("locationId", $loc);
    $stm->bindInt("type", $type);
    $stm->execute();
    $c = 0;
    foreach ($stm->fetchAll() as $X) {
      if (!$c++) {
        DoLine("animaltype", "type=$type", "<i>$X->name</i> kind description", "", "");
      }
      DoLine("animal", "type=$X->id", "<i>$X->name</i>", "", DoID($X->id, 7) .
        ", damage $X->damage/<small>$X->strength</small>");
    }
    if ($stm->rowCount() == 0) {
      DoMessage("no animals of this kind found");
    }
    break;

  // =================================
  case "loccnames":
    if (!HasPrivillege('locMNames')) {
      break;
    }
    ceLog("Matched custom names (\"$cname\") of " . gLocation($loc));
    $stm = $db->prepare("SELECT COUNT(*) AS num, name FROM charnaming chn WHERE type = 2
      AND observed = :locationId AND name LIKE :name GROUP BY name ORDER BY name");
    $stm->bindInt("locationId", $loc);
    $stm->bindStr("name", "%" . urldecode($cname) . "%");
    $stm->execute();
    foreach ($stm->fetchAll() as $X) {
      DoLine("loccname", "loc=$X->id"
        . ($X->custom ? "&cname=" . urlencode($X->name) : ""), "<i><font color=#ffdd99>$X->name</font></i>",
        "", " (" . $X->num . " char" . ($X->num != 1 ? "s" : "") . ")");
    }
    break;

  // =================================
  case "charinloc":

    if (!HasPrivillege('locChrs')) {
      break;
    }
    $loc = intval($loc);
    $loctype = intval($loctype);

    if (isset($st)) {
      $st = intval($st);
    }
    if (!$st) {
      DoLine("charinloc", "loc=$loc&st=1", "living characters", "", "");
      DoLine("charinloc", "loc=$loc&st=1&inside=1", "living characters in " . ($loctype == 1 ? "vehicles/buildings" : "rooms/docked ships"), "", "");
      DoLine("charinloc", "loc=$loc&st=2", "dead characters", "", "");
      DoLine("charinloc", "loc=$loc&st=2&inside=1", "dead characters in " . ($loctype == 1 ? "vehicles/buildings" : "rooms/docked ships"), "", "");
      SkipLog();
      break;
    } else {
      ceLog(($st == 1 ? "Living" : "Dead") . " chars " . ($inside ? "inside buildings " : "") . "in " . gLocation($loc));
    }

    function RecursiveCharinloc($theloc)
    {
      global $chars, $st, $db;
      $stm = $db->prepare("SELECT id, name FROM locations WHERE region = :locationId AND type > 1 AND id > 1");
      $stm->bindInt("locationId", $theloc);
      $stm->execute();
      while (list ($locid, $locname) = $stm->fetch(PDO::FETCH_NUM)) {
        $charsStm = $db->prepare("SELECT ch.id, ch.name FROM chars ch WHERE location = :locationId AND status" .
          ($st == 1 ? "=1" : ">1") . " ORDER BY name");
        $charsStm->bindInt("locationId", $locid);
        $charsStm->execute();
        foreach ($charsStm->fetchAll() as $X) {
          $X->locname = $locname;
          $chars [] = clone $X;
        }
        RecursiveCharinloc($locid);
      }
    }

    if ($inside) {
      RecursiveCharinloc($loc);
    } else {
      $stm = $db->prepare("SELECT ch.id, ch.name FROM chars ch WHERE location = :locationId AND status " .
        ($st == 1 ? "=1" : ">1") . " ORDER BY name");
      $stm->bindInt("locationId", $loc);
      $stm->execute();
      foreach ($stm->fetchAll() as $X) {
        $chars[] = $X;
      }
    }
    if (count($chars)) {
      foreach ($chars as $X) {
        DoLine("char", "char=$X->id", "<i><font color=#b4faf6>" . htmlentities($X->name) . "</font></i>", "",
          DoID($X->id) . ($X->locname ? " at <i><font color=#d0ffd0>" . $X->locname . "</font></i>" : ""));
      }
    } else {
      DoLine("", "", "", "<font color=#a8a8a8>no characters found</font>", "");
    }
    break;

  // =================================
  case "plyrinloc":

    if (!HasPrivillege('locPlyr')) {
      break;
    }
    $loc = intval($loc);
    $loctype = intval($loctype);
    if (isset($st)) {
      $st = intval($st);
    }

    if (!$st) {
      DoLine("plyrinloc", "loc=$loc&st=1", "owners of living characters", "", "");
      DoLine("plyrinloc", "loc=$loc&st=2", "owners of dead characters", "", "");
      SkipLog();
      break;
    } else {
      ceLog("Owners of " . ($st == 1 ? "living" : "dead") . " characters in " . gLocation($loc));
    }

    function RecursivePlyrinloc($theloc)
    {
      global $plyrs, $st, $charcount, $db;

      $stm = $db->prepare(
        "SELECT ch.player, p.firstname, p.lastname FROM chars ch LEFT OUTER JOIN players p ON ch.player = p.id
        WHERE ch.location = :locationId AND ch.status" . ($st == 1 ? "=1" : ">1") . " ORDER BY p.lastname, p.firstname");
      $stm->bindInt("locationId", $theloc);
      $stm->execute();

      foreach ($stm->fetchAll() as $X) {
        $charcount [$X->player]++;
        $plyrs [$X->player] = clone $X;
      }

      $stm = $db->prepare("SELECT id FROM locations WHERE region = :locationId AND type > 1");
      $stm->bindInt("locationId", $theloc);
      $stm->execute();
      foreach ($stm->fetchScalars() as $locid) {
        RecursivePlyrinloc($locid);
      }
    }

    RecursivePlyrinloc($loc);

    if (count($charcount)) {
      arsort($charcount);
      foreach ($charcount as $plyrid => $count) {
        DoPlayer($plyrid, $plyrs [$plyrid]->firstname, $plyrs [$plyrid]->lastname, "",
          $count > 1 ? "(" . $count . " chars)" : "", "&loc=$loc&st=$st");
      }
    } else {
      DoMessage("no characters found");
    }
    break;

  // =================================
  case "char":

    if (!HasPrivillege('char')) {
      break;
    }
    $char = intval($char);
    $stm = $db->prepare("SELECT day FROM turn");
    $day = $stm->executeScalar();
    $stm = $db->prepare("
    SELECT ch.*, oln.name AS bornname
    FROM chars ch LEFT OUTER JOIN oldlocnames oln ON oln.id = ch.spawning_location
    WHERE ch.id = :charId LIMIT 1");
    $stm->bindInt("charId", $char);
    $stm->execute();
    $CH = $stm->fetchObject();

    ceLog("Char " . gChar($char) . " of " . gPlayer($CH->player));

    $stm = $db->prepare("
    SELECT *
    FROM players p
    WHERE p.id = :playerId LIMIT 1");
    $stm->bindInt("playerId", $CH->player);
    $stm->execute();
    if ($stm->rowCount() != 0) {
      $P = $stm->fetchObject();
    }

    $stm = $db->prepare("SELECT name, type FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $CH->location);
    $stm->execute();
    list ($locname, $loctype) = $stm->fetch(PDO::FETCH_NUM);
    if ($locname == "") {
      $stm = $db->prepare("SELECT name FROM oldlocnames WHERE id = :locationId");
      $stm->bindInt("locationId", $CH->location);
      $locname = $stm->executeScalar();
    }

    DoPlayer($CH->player, $P->firstname, $P->lastname, "owner: ");
    $statuses = array(1 => "active", 2 => "deceased", 3 => "being buried", 4 => "buried");
    if ($CH->status == 1) {
      DoLine("", "", "", "Status: " . $statuses [$CH->status], "");
    } else {
      DoLine("charstatus", "char=$char", $statuses [$CH->status], "status: ", "");
    }
    DoLine("", "", "", "sex: " . ($CH->sex == 1 ? "male" : "female"), "");
    DoLine("", "", "", "age: " . (floor(($day - $CH->register) / 20) + $CH->spawning_age), "");
    if ($CH->spawning_location) {
      DoLine("openloc", "loc=$CH->spawning_location", LocationName("$CH->bornname", 1),
        "spawned: ", " at day $CH->register");
    } else {
      DoLine("", "", "", "spawned: unknown", "");
    }
    if ($CH->location) {
      DoLine("openloc", "loc=$CH->location", LocationName($locname, $loctype),
        "location: ", DoID($CH->location));
    } else {
      // get road information
      $stm = $db->prepare("SELECT * FROM travels WHERE person = :charId AND type = 0");
      $stm->bindInt("charId", $char);
      $stm->execute();
      $travel = $stm->fetchObject();
      if ($travel) {
        $stm = $db->prepare("
        SELECT c.start, c.end, c.id, c.direction, n1.name AS name1, n2.name AS name2, ct.name AS ctname FROM connections c
        INNER JOIN connecttypes ct ON ct.id = c.type
        INNER JOIN oldlocnames n1 ON n1.id = c.start
        INNER JOIN oldlocnames n2 ON n2.id = c.end
        WHERE c.id = :id");
        $stm->bindInt("id", $travel->connection);
        $stm->execute();
        $conn = $stm->fetchObject();
        if ($conn) {
          $rev = $travel->locfrom != $conn->start;
          $progr = 100 - floor(100 * $travel->travleft / $travel->travneeded);
          if ($travel->speed < 0) {
            $rev = !$rev;
            $progr = 100 - $progr;
          }
          DoLine("conn", "conn=$conn->id", $conn->ctname . " " .
            LocationName($rev ? $conn->name2 : $conn->name1, 1) .
            " to " . LocationName($rev ? $conn->name1 : $conn->name2, 1), "",
            "($progr%), speed $travel->speed/<small>$travel->maxspeed</small>");
        } else {
          DoError("broken traveling/road information for that character.");
        }
      } else {
        DoError("broken location data for that character.");
      }
    }
    DoLine("", "", "", "last activity: $CH->lastdate-$CH->lasttime", "");
    DoLine("travhis", "char=$char", "travel history", "", "");

    break;

  // =================================
  case "charstatus":

    if (!HasPrivillege('charStts')) {
      break;
    }
    $char = intval($char);
    ceLog("Status of char " . gChar($char));

    $stm = $db->prepare("SELECT status, death_cause, death_weapon, death_date FROM chars WHERE id = :charId LIMIT 1");
    $stm->bindInt("charId", $char);
    $stm->execute();
    $death = $stm->fetchObject();
    if ($death->death_cause == 1 || $death->death_cause == 4) {
      $stm = $db->prepare("SELECT name FROM " . ($death->death_cause == 1 ? "objecttypes" : "animal_types") . " WHERE id = :deathWeapon LIMIT 1");
      $stm->bindInt("deathWeapon", $death->death_weapon);
      $cause = $stm->executeScalar();
      $cause = "killed " . ($death->death_cause == 1 ? "with" : "by") . " " . $cause;
    } elseif ($death->death_cause == 0) {
      $cause = "unknown";
    } elseif ($death->death_cause == 6) {
      $cause = "starvation";
    } else {
      $cause = "heart attack";
    }

    DoLine("", "", "", "death cause: $cause", "");
    DoLine("", "", "", "death date: " . ($death->death_date ? $death->death_date : "unknown"), "");
    if ($death->status == 2) {
      $stm = $db->prepare("SELECT id FROM objects WHERE type = 7 AND typeid = :charId LIMIT 1");
      $stm->bindInt("charId", $char);
      $bodyid = $stm->executeScalar();
      DoLine("object", "id=$bodyid", "body object", "", DoID($bodyid));
    }
    break;

  // =================================
  case "player":
  case "unplayer":

    if (!HasPrivillege('plyr')) {
      break;
    }

    $plyr = intval($plyr);
    ceLog("Player " . gPlayer($plyr));

    DoLine("charofplyr", "plyr=$plyr", "characters", "", "");

    if ($req == "player") {

      // get player information
      $stm = $db->prepare("
      SELECT p.*, l.name AS lang
      FROM players p LEFT OUTER JOIN languages l ON p.language = l.id WHERE p.id = :playerId");
      $stm->bindInt("playerId", $plyr);
      $stm->execute();
      $plyrinfo = $stm->fetchObject();

      // get departments information
      $stm = $db->prepare("
      SELECT a.council, a.status, a.special, c.name
      FROM assignments a INNER JOIN councils c ON a.council = c.id WHERE player = :playerId");
      $stm->bindInt("playerId", $plyr);
      $stm->execute();
      if ($stm->rowCount() > 0) {
        $memberType = array("hidden member", "chair", "vice chair", "member", "special member", "aspirant member", "member on leave");
        foreach ($stm->fetchAll() as $X) {
          DoLine("listdept", "dept=$plyr", "<font color=#f55><i>$X->name</i></font>", $memberType [$X->status] . " of ", "");
        }
      }
      DoLine("", "", "", "e-mail address: <i>$plyrinfo->email</i>", "");
      if ($plyrinfo->nick) {
        DoLine("", "", "", "IRC nick: <i>$plyrinfo->nick</i>", "");
      }
      if ($plyrinfo->forumnick) {
        DoLine("", "", "", "forum nick: <i>$plyrinfo->forumnick</i>", "");
      }
      DoLine("", "", "", "country: $plyrinfo->country", "");
      DoLine("", "", "", "language: $plyrinfo->lang", "");
      $plyrStat = array("pending", "approved", "active", "locked", "removed", "unsubscribed");
      DoLine("", "", "", "status: " . $plyrStat [$plyrinfo->status], "");
      DoLine("", "", "", "last login: $plyrinfo->lastdate-<small>$plyrinfo->lasttime</small>, $plyrinfo->lastlogin", "");
      $b = 1;
      for ($i = 1; $i <= 16; $i++) {
        if ($plyrinfo->recent_activity & $b) {
          $rec_act = getImg("cecby") . $rec_act;
        } else {
          $rec_act = getImg("cecbn") . $rec_act;
        }
        $b *= 2;
      }
      DoLine("", "", "", "recent activity: <small>past $rec_act today</small>", "");
      DoLine("", "", "", "time left: $plyrinfo->timeleft minutes", "");
      if ($plyrinfo->onleave) {
        DoLine("", "", "", "on leave set for $plyrinfo->onleave days", "");
      }
      DoLine("", "", "", "credit: $plyrinfo->credits", "");

      DoLine("plyradm", "plyr=$plyr", "administrative options", "", "");
    }
    if ($loc && $st) {
      DoLine("charofplyr", "plyr=$plyr&loc=$loc&st=$st", ($st == 1 ? "living" : "dead") . " characters in this location", "", "");
      DoLine("loctravs", "plyr=$plyr&loc=$loc&type=ch", "characters travels through this location", "", "");
    }
    break;

  // =================================
  case "plyradm":
    if (!HasPrivillege('plyrAdm')) {
      break;
    }
    SkipLog();
    //ceLog ("Player administration: ".gPlayer ($plyr));
    DoLine("plyrprivlg", "plyr=$plyr", "staff privilleges", "", "");
    DoLine("plyrceprivlg", "plyr=$plyr", "Cantr Explorer privilleges", "", "");
    break;


  // =================================
  case "plyrprivlg":
  case "setprivlg":

    if (!HasPrivillege('staffPrvlg')) {
      break;
    }
    $plyr = intval($plyr);
    $prvl = intval($prvl);
    if ($req == "plyrprivlg") {
      ceLog("Listed staff privilleges for " . gPlayer($plyr));
    }
    // staff privillege update
    if ($req == "setprivlg") {
      if ($val) {
        $stm = $db->prepare("INSERT INTO access VALUES (:playerId, :privilege)");
        $stm->bindInt("playerId", $plyr);
        $stm->bindInt("privilege", $prvl);
        $stm->execute();
      } else {
        $stm = $db->prepare("DELETE FROM access WHERE player = :playerId AND page = :privilege");
        $stm->bindInt("playerId", $plyr);
        $stm->bindInt("privilege", $prvl);
        $stm->execute();
      }
    }
    $stm = $db->prepare("
    SELECT t.id, t.description, a.player FROM access_types t LEFT OUTER JOIN access a ON (t.id = a.page AND a.player = :playerId)" .
      ($req == "setprivlg" ? " WHERE t.id = :privilege" : ""));
    $stm->bindInt("playerId", $plyr);
    $stm->bindInt("privilege", $prvl);
    $stm->execute();

    foreach ($stm->fetchAll() as $X) {
      DoCheckBox("setprivlg", "plyr=$plyr&prvl=$X->id&val=" . ($X->player !== null ? "0" : "1"),
        $X->description, "", "", $X->player !== null);
      $desc = $X->description;
    }

    if ($req == "setprivlg") {
      ceLog(($val ? "Enabled" : "Disabled") . " for " . gPlayer($plyr) . " staff privillege \"$desc\"");
    }
    break;

  // =================================
  case "plyrceprivlg":
  case "setceprivlg":

    if (!HasPrivillege('CEPrvlg')) {
      break;
    }
    $plyr = intval($plyr);

    if ($req == "plyrceprivlg") {
      ceLog("Listed CE privilleges for " . gPlayer($plyr));
    }
    // CE privillege update
    if ($req == "setceprivlg") {
      if ($val) {
        $stm = $db->prepare("INSERT INTO ceAccess VALUES (:playerId, :privilege)");
        $stm->bindInt("playerId", $plyr);
        $stm->bindStr("privilege", $prvl);
        $stm->execute();
      } else {
        $stm = $db->prepare("DELETE FROM ceAccess WHERE player = :playerId AND access = :privilege");
        $stm->bindInt("playerId", $plyr);
        $stm->bindStr("privilege", $prvl);
        $stm->execute();
      }
    }
    $stm = $db->prepare("SELECT t.id, t.description, a.player FROM ceAccessTypes t
      LEFT OUTER JOIN ceAccess a ON (t.id = a.access AND a.player = :playerId)" .
      ($req == "setceprivlg" ? " WHERE t.id = :privilege" : ""));
    $stm->bindInt("playerId", $plyr);
    $stm->bindStr("privilege", $prvl);
    $stm->execute();
    foreach ($stm->fetchAll() as $X) {
      DoCheckBox("setceprivlg", "plyr=$plyr&prvl=$X->id&val=" . ($X->player !== null ? "0" : "1"),
        $X->description, "", "", $X->player !== null);
      $desc = $X->description;
    }

    if ($req == "setceprivlg") {
      ceLog(($val ? "Enabled" : "Disabled") . " for " . gPlayer($plyr) . " CE privillege \"$desc\"");
    }
    break;

  // =================================
  case "charofplyr":

    if (!HasPrivillege('plyrChrs')) {
      break;
    }

    ceLog("List of " . ($st ? ($st == 1 ? "living " : "dead ") : "") . "chars of " . gPlayer($plyr) . ($st ? " in " . gLocation($loc) : ""));
    function RecursiveCharofplyr($theloc)
    {
      global $chars, $plyr, $st, $db;

      $stm = $db->prepare(
        "SELECT * FROM chars
      WHERE player = :playerId AND location = :locationId AND status" . ($st == 1 ? "=1" : ">1"));
      $stm->bindInt("playerId", $plyr);
      $stm->bindInt("locationId", $theloc);
      $stm->execute();
      foreach ($stm->fetchAll() as $X) {
        $chars [] = $X;
      }
      $stm = $db->prepare("SELECT id FROM locations WHERE region = :locationId AND type > 1");
      $stm->bindInt("locationId", $theloc);
      $stm->execute();
      foreach ($stm->fetchScalars() as $locid) {
        RecursiveCharofplyr($locid);
      }
    }

    if ($loc) {
      RecursiveCharofplyr($loc);
    } else {
      $stm = $db->prepare("SELECT * FROM chars WHERE player = :playerId ORDER BY register");
      $stm->bindInt("playerId", $plyr);
      $stm->execute();
      foreach ($stm->fetchAll() as $X) {
        $chars [] = $X;
      }
    }
    $count = count($chars);
    if ($count) {
      foreach ($chars as $X) {
        DoLine("char", "char=$X->id", "<i><font color=#b4faf6>" . TC(htmlentities($X->name)) . "</font></i>", "",
          DoID($X->id) . ($X->status > 1 ? " (dead)" : ""));
      }
    } else {
      DoMessage("no characters found");
    }
    break;

  // =================================
  case "travhis":

    if ($char) {
      if (!HasPrivillege('chrTrvl')) {
        break;
      }

      $shipTypeIds = Location::getShipTypeArray();
      $shipTypeStr = implode(",", $shipTypeIds);

      ceLog("Travel history of char " . gChar($char));
      $stm = $db->prepareWithIntList("
      SELECT th.*, oln.name AS name, COALESCE(l.name, CONCAT(ot.unique_name, ' (type)')) AS vehname
      FROM travelhistory th INNER JOIN oldlocnames oln ON oln.id = th.location
      LEFT OUTER JOIN locations l ON (th.vehicle > 0 AND (th.day NOT BETWEEN 4892 AND 5164 OR l.area IN (:shipTypes)) AND th.vehicle = l.id)
      LEFT OUTER JOIN objecttypes ot ON (th.vehicle > 0 AND th.vehicle = ot.id)
      WHERE person = :charId ORDER BY th.id", [
        "shipTypes" => $shipTypeIds,
      ]); // 4892-5164 then vehicle TYPE was recorded instead of vehicle ID
      $stm->bindInt("charId", $char);
      $stm->execute();
      foreach ($stm->fetchAll() as $X) {
        if ($X->arrival) {
          if ($item) {
            $travels[] = $item;
          }
          $item = new stdClass();
          $item->loc = $X->location;
          $item->name = $X->name;
          $item->arrveh = $X->vehname;
          $item->arrtime = $X->day . "<small>-" . $X->hour . "</small>";
        } else {
          if ($item) {
            if ($item->loc != $X->location) {
              $travels[] = $item;
              $item = new stdClass();
            }
          } else {
            $item = new stdClass();
          }
          $item->loc = $X->location;
          $item->name = $X->name;
          $item->depveh = $X->vehname;
          $item->deptime = $X->day . "<small>-" . $X->hour . "</small>";
          if (!$item->loc || $item->loc != $X->location) {
            $travels[] = clone $item;
            $item = new stdClass();
          }

          $item->to = LocationName($name, $type);
          $travels[] = clone $item;
          unset($item);
        }
      }
      if ($item) {
        $travels [] = clone $item;
        unset ($item);
      }
      $count = count($travels);
      if ($count) {
        foreach ($travels as $T) {
          DoLine("openloc", "loc=$T->loc", LocationName($T->name, 1),
            ($T->arrtime ? $T->arrtime : "&nbsp; &nbsp; &nbsp; &nbsp;- <small>&nbsp; </small>") . " &ndash; " .
            ($T->deptime ? $T->deptime : "&nbsp; &nbsp; &nbsp; &nbsp;- <small>&nbsp; </small>") . " ",
            DoID($T->loc) .
            ($T->arrveh ? GetImg("cein") : "") .
            ($T->arrveh && $T->arrveh != $T->depveh ? LocationName($T->arrveh, 2) : "") .
            ($T->depveh ? GetImg("ceout") . LocationName($T->depveh, 2) : ""));
        }
      } else {
        DoMessage("no travels found");
      }
    }
    break;

  // =================================
  case "loctravs":
    if (!HasPrivillege('locTrvl')) {
      break;
    }
    $loc = intval($loc);
    $plyr = intval($plyr);
    if (!$type) {
      DoLine("loctravs", "loc=$loc&type=ch", "characters", "", "");
      DoLine("loctravs", "loc=$loc&type=p", "players", "", "");
      DoLine("loctravs", "loc=$loc&type=v", "vehicles/ships", "", "");
      SkipLog();
      break;
    } else {
      switch ($type) {
        case 'ch':
          if ($start === null) {
            $stm = $db->prepare("
          SELECT COUNT(*) FROM travelhistory th INNER JOIN chars ch ON th.person = ch.id
          WHERE th.location = :locationId" . ($plyr ? " AND ch.player=$plyr" : ""));
          $stm->bindInt("locationId", $loc);
          $num = $stm->executeScalar();
            if ($num > 20) {
              $imax = ceil($num / 20);
              for ($i = 0; $i < $imax; $i++) {
                $max = $i == $imax - 1 ? $num : $i * 20 + 20;
                DoLine("loctravs", "loc=$loc&type=ch&start=$i" . ($plyr ? "&plyr=$plyr" : ""),
                  "travels <small>" . ($i * 20 + 1) . "&ndash;$max</small>", "", "");
              }
              ceLog("Amount of character travels through " . gLocation($loc));
            } else {
              $start = 0;
            }
          }
          if ($start !== null) {
            $stm = $db->prepare("
          SELECT day, hour, arrival, ch.id, ch.name AS chname, v.name AS vname
          FROM travelhistory th INNER JOIN chars ch ON th.person=ch.id
            LEFT OUTER JOIN locations v ON (v.id = th.vehicle AND th.vehicle > 0)
          WHERE th.location = :locationId " . ($plyr ? " AND ch.player=$plyr" : "") . "
          ORDER BY th.id
          LIMIT 20 OFFSET :offset");
            $stm->bindInt("locationId", $loc);
            $stm->bindInt("offset", $start * 20);
            $stm->execute();
            foreach ($stm->fetchAll() as $T) {
              DoLine("char", "char=$T->id", "<i><font color=#b4faf6>" . htmlentities($T->chname) . "</font></i>",
                "$T->day-$T->hour " . GetImg("ce" . ($T->arrival ? "in" : "out")),
                DoID($T->id) . " " . ($T->vname ? " in " . LocationName($T->vname, 2) : ""));
            }
            if ($stm->rowCount() == 0) {
              DoMessage("no travels found");
            }
            ceLog("Character travels (" . ($start * 20 + 1) . "-" . ($start * 20 + $stm->rowCount()) . ") through " . gLocation($loc));
          }
          break;

        case 'p':
          ceLog("Owners of chars which traveled " . gLocation($loc));
          $stm = $db->prepare("
        SELECT ch.player, COUNT(DISTINCT ch.id) AS chcount, COUNT(*) as tcount, p.firstname, p.lastname
          FROM travelhistory th INNER JOIN chars ch ON th.person=ch.id LEFT OUTER JOIN players p ON p.id = ch.player
        WHERE th.location = :locationId
        GROUP BY ch.player
        ORDER BY 2 DESC, 3 DESC, 1 DESC");
          $stm->bindInt("locationId", $loc);
          $stm->execute();
          foreach ($stm->fetchAll() as $T) {
            DoPlayer($T->player, $T->firstname, $T->lastname, "",
              "(" . ($T->chcount > 1 ? $T->chcount . " chars, " : "") . "$T->tcount travel items)", "&loc=$loc");
          }
          if ($stm->rowCount() == 0) {
            DoMessage("no travelers found");
          }
          break;

        case 'v':
          ceLog("Vehicles which traveled through " . gLocation($loc));
          $stm = $db->prepare("
        SELECT v.id, v.name, COUNT(*) AS count
          FROM travelhistory th INNER JOIN locations v ON (th.vehicle=v.id AND th.vehicle > 0)
        WHERE th.location = :locationId
        GROUP BY th.vehicle
        ORDER BY 3 DESC, 2");
          $stm->bindInt("locationId", $loc);
          $stm->execute();
          foreach ($stm->fetchAll() as $T) {
            DoLine("openloc", "loc=$T->id", LocationName($T->name, 2),
              "", DoID($T->id) . " ($T->count travel items)");
          }
          if ($stm->rowCount() == 0) {
            DoMessage("no vehicles traveling through this location found");
          }
          break;
        default:
          DoLine("", "", "", "<font color=#888888><i>sorry, this feature is not yet implemented</i></font>", "");
      } // switch ($type)
    }
    break;   // SOA MFC

  // =================================
  case "charsearch":
    if (!HasPrivillege('maChars')) {
      break;
    }
    $max = 50;
    $whereConditions = array();

    $stm = $db->prepare("SELECT day FROM turn LIMIT 1");
    $today = $stm->executeScalar();
    //getting search condition data and preparing.
    if (!empty($cid)) {
      $whereConditions [] = "chars.id = " . intval($cid);
    }
    if (!empty($cname)) {
      $whereConditions [] = "chars.name like " . $db->quote("%$cname%");
    }
    if (!empty($knowas)) {
      $knowasentities = htmlentities($knowas, ENT_COMPAT | ENT_HTML401, 'utf-8');
      //I use two version, older entries can be not coding in htmlentieties I think.
      $cond = "( id in ( select observed from charnaming where name like " . $db->quote("%$knowas%");
      $cond .= " OR id in ( select observed from charnaming where name like ".  $db->quote("%$knowasentities%") .") )";

      $whereConditions [] = $cond;
    }
    if (!empty($pid)) {
      $whereConditions [] = "chars.player = " . intval($pid);
    }
    if (!empty($locid)) {
      $whereConditions [] = "chars.location = " . intval($locid);
    }
    if (isset($agefrom)) {
      $agefrom = intval($agefrom);
      $whereConditions [] = "ROUND( ( $today - cast( chars.register AS SIGNED ) ) / 20 ) + 20 >= $agefrom";
    }
    if (isset($ageto)) {
      $ageto = intval($ageto);
      $whereConditions [] = "ROUND( ( $today - cast( chars.register AS SIGNED ) ) / 20 ) + 20 <= $ageto";
    }

    if (isset($bornfrom)) {
      $whereConditions [] = "chars.register >= " . intval($bornfrom);
    }
    if (isset($bornto)) {
      $whereConditions [] = "chars.register <= " . intval($bornto);
    }
    if (isset($status) && $status != -1) {
      $whereConditions [] = "chars.status = " . intval($status);
    }
    if (!empty($lang)) {
      $whereConditions [] = "chars.language = " . intval($lang);
    }

    $whereQ = "";
    foreach ($whereConditions as $cond) {
      $whereQ .= "$cond AND ";
    }
    if (strlen($whereQ) > 5) {
      $whereQ = "WHERE " . substr($whereQ, 0, -5);
    }

    $stm = $db->prepare("SELECT COUNT(*) FROM chars $whereQ");
    $rCount = $stm->executeScalar();

    $query = "SELECT id, name FROM chars $whereQ";
    echo "<!-- Search query: $query --> Characters";
    ceLog("Character search: $query");

    if ($rCount) {
      if ($rCount > $max) {
        DoLine("", "", "", ($rCount) . " characters follow your search criteria. Please be more specific.", "");
      } else {
        $stm = $db->query("SELECT id, name FROM chars $whereQ");
        while (list ($id, $name) = $stm->fetch(PDO::FETCH_NUM)) {
          DoCharacter($id, $name);
        }
      }
    } else {
      DoLine("", "", "", "No characters follow your search criteria.", "");
    }
    break;
  case "plyrsearch":
    if (!HasPrivillege('maPlyrs')) {
      break;
    }
    if (isset($pid)) {
      $pid = intval($pid);
    }
    ceLog("Search players"
      . ($pid ? ", ID = $pid" : "")
      . ($first ? ", first name like \"$first\"" : "")
      . ($last ? ", last name like \"$last\"" : "")
      . ($lang ? ", language - " . gLang($lang) : "")
      . ($email ? ", e-mail like \"$email\"" : "")
      . ($ip ? ", IP address like \"$ip\"" : "")
      . ($status ? ", status - " . ($status == 'l' ? "locked" : "active") : ""));

    if ($pid) {
      // search by IP
      $stm = $db->prepare("SELECT firstname, lastname FROM players WHERE id = :playerId");
      $stm->bindInt("playerId", $pid);
      $stm->execute();
      if ($stm->rowCount() > 0) {
        list ($firstname, $lastname) = $stm->fetch(PDO::FETCH_NUM);
        DoPlayer($pid, $firstname, $lastname);
      } else {
        $stm = $db->prepare("SELECT COUNT(*) FROM chars WHERE player = :playerId");
        $stm->bindInt("playerId", $pid);
        $count = $stm->executeScalar();
        if ($count) {
          DoPlayer($pid, "", "");
        } else {
          DoLine("", "", "", "There's no player with this ID number.", "");
        }
      }

    } else {
      // search by other criteria
      $Where = "1" .
        ($pid && is_numeric($pid) ? " AND id = $pid" : "") .
        ($first ? " AND firstname LIKE " . $db->quote("%$first%") : "") .
        ($last ? " AND lastname LIKE " . $db->quote("%$last%") : "") .
        ($lang && is_numeric($lang) ? " AND language = $lang" : "") .
        ($email ? " AND email like " . $db->quote("%$email%") : "") .
        ($status ? " AND status " . ($status == 'l' ? "= 3" : "< 3") : "");
      if ($ip) {
        $where1 = " AND ip LIKE " . $db->quote("$ip%");
        $where2 = " AND client_ip LIKE " . $db->quote("$ip%");
      }

      if ($ip) {
        $Tables = "ips LEFT OUTER JOIN players ON player = id";
      } else {
        $Tables = "players";
      }
      $IDStr = $ip ? "player" : "id";

      $stm = $db->prepare(
        "SELECT SUM(tab.num) FROM (SELECT COUNT(DISTINCT $IDStr) AS num FROM $Tables WHERE $Where $where1" .
        ($ip ? " UNION ALL SELECT COUNT(DISTINCT $IDStr) AS num FROM $Tables WHERE $Where $where2" : "") . ") AS tab");
      $count = $stm->executeScalar();
      if (!$count) {
        DoLine("", "", "", "No players follow your search criteria.", "");
      } elseif ($count <= 30) {
        $stm = $db->query(
          "SELECT $IDStr, firstname, lastname FROM $Tables
        WHERE $Where $where1 GROUP BY $IDStr" .
          (!$ip ? " ORDER BY $IDStr" : " UNION SELECT $IDStr, firstname, lastname FROM $Tables
        WHERE $Where $where2 GROUP BY $IDStr ORDER BY $IDStr"));

        while (list ($id, $firstname, $lastname) = $stm->fetch(PDO::FETCH_NUM)) {
          DoPlayer($id, $firstname, $lastname);
        }

      } else {
        DoLine("", "", "", ($count) . " players follow your search criteria. Please be more specific.", "");
      }
    }

    break;

  // =================================
  default:
    DoLine("", "", "", "<font color=#888888><i>sorry, this feature is not yet implemented</i></font>", "");

}

$count = count($treeLines);
$counter = 0;
if ($count) {
  foreach ($treeLines as $T) {
    echo WriteLine($T, ++$counter == $count);
  }
}

// Default logging
if (!$Logged) {
  ceLog();
}

?>
</table> 
