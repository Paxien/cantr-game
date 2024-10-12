<?php

include_once "../lib/stddef.inc.php";

$db = Db::get();

function LoadCharInfo ($id) {
  global $Chars, $db;
  $stm = $db->prepare("SELECT * FROM chars WHERE id = :charId");
  $stm->bindInt("charId", $id);
  $stm->execute();
  $X = $stm->fetchObject();
  $Chars [$X->id] = $X;
}

function CharName ($id) {
  global $Chars, $playerColour;
  if (!$Chars [$id]) LoadCharInfo ($id);
  return "<font color=\"".($playerColour [$Chars [$id]->player] ? $playerColour [$Chars [$id]->player] :
      "White")."\">".$Chars [$id]->name."</font>";
}

function SuspChar ($id) {
  global $Chars, $playerName;
  return ($playerName [$Chars [$id]->player] != "");
}

function PlayerInfo ($output = true) {
  global $Colours, $ids, $playerName, $playerColour, $db;
  $stm = $db->prepareWithIntList("SELECT * FROM players WHERE id in (:ids) ORDER by id", [
    "ids" => explode(",", $ids),
  ]);
  $stm->execute();
  $Colours = array (1 => "#ffb0b0", "#b0ffb0", "#b0b0ff", "#feff5d", "#73f3ff", "#ff99fe", "#ffbc66");

  foreach ($stm->fetchAll() as $playerInfo) {
    // Assign a colour
    if ($count++ <= count ($Colours))
      $playerColour [$playerInfo->id] = $Colours [$count];
    else
      $playerColour [$playerInfo->id] = "White";

    $playerName [$playerInfo->id] = "<font color=\"".
      $playerColour [$playerInfo->id]."\">".
      "$playerInfo->firstname $playerInfo->lastname</font>";
    if ($output)
      echo "<tr><td align=right><a href=\"index.php?" .
	"page=infoplayer&player_id=$playerInfo->id\"><small>$playerInfo->id.</small></a>&nbsp;</td>".
	"<td>".$playerName [$playerInfo->id]."</td>".
	"<td>$playerInfo->age</td>".
	"<td>$playerInfo->country</td>".
	"<td>$playerInfo->lastdate-$playerInfo->lasttime</td>".
	"<td>$playerInfo->timeleft min".($playerInfo->timeleft != 1 ? "s" : "")." left</td>".
	"</tr><tr>".
	"<td></td><td><small>$playerInfo->email<small></td>".
	"<td colSpan=8><small><font color=\"#00cc00\">$playerInfo->lastlogin</font></small></td>".
	"</tr>";
  }
}

include_once '../lib/stddef.inc.php';
include_once _LIB_LOC . "/header.functions.inc.php";

$db = Db::get();

$DoIP = $_REQUEST['DoIP'];
$ids = $_REQUEST['ids'];
$start = HTTPContext::getInteger('start');

if ($DoIP) {

  $limit = 10;

  $s = session::getSessionFromCookie();

  $session_handle = new session($s);
  $session_info = $session_handle->checklogin();

  $player = $session_info->player;

  // check for privilleges
  $playerInfo = Request::getInstance()->getPlayer();
  if ($playerInfo->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
    PlayerInfo (false);
    echo "<table width=750>";
    $stm = $db->prepareWithIntList("
		  SELECT COUNT(*) FROM ips
		  WHERE player IN (:ids)
		  GROUP BY ip, client_ip", [
		  "ids" => explode(",", $ids),
    ]);
    $C = $stm->executeScalar();

    $stm = $db->prepareWithIntList("
		  SELECT ip, client_ip FROM ips
		  WHERE player IN (:ids)
		  GROUP BY ip, client_ip
		  ORDER BY MAX(lasttime) DESC
			LIMIT :start, :limit ", [
		  "ids" => explode(",", $ids),
    ]);
    $stm->bindInt("start", $start);
    $stm->bindInt("limit", $limit);
    $stm->execute();

    foreach ($stm->fetchAll() as $ipInfo) {
      $remhost = gethostbyaddr ($ipInfo->ip);
      echo
	"<tr><td colSpan=4>$remhost".
	($remhost == $ipInfo->ip ? "" : " <small>$ipInfo->ip</small>");
      if ($ipInfo->client_ip) {
	// bug workaround
	$clientpattern = $ipInfo->client_ip;
	$comma = strpos ($clientpattern, ", ");
	if ($comma !== false)
	  $clientpattern = substr ($clientpattern, 0, $comma);
        $remhost = gethostbyaddr ($clientpattern);
	echo " | ".$ipInfo->client_ip.
	  ($remhost == $ipInfo->client_ip ? "" : " <small>$ipInfo->client_ip</small>");
      }
      echo
        "</td></tr>";

      if ($ipInfo->client_ip)
	$cond = "client_ip=:clientIp";
      else
	$cond = "(client_ip IS NULL OR client_ip = \"\")";

      $stm = $db->prepareWithIntList("
			  SELECT * FROM ips
			  WHERE player IN (:ids) AND ip = :ip AND $cond
			  ORDER BY lasttime DESC
			", [
        "ids" => explode(",", $ids),
      ]);
      $stm->bindStr("ip", $ipInfo->ip);
      if ($ipInfo->client_ip) {
        $stm->bindStr("clientIp", $ipInfo->client_ip);
      }
      $stm->execute();

      foreach ($stm->fetchAll() as $plyrIpInfo) {
	echo
	  "<tr><td width=30>&nbsp;</td><td width=320>".$playerName [$plyrIpInfo->player].
	  "<td align=right width=100> $plyrIpInfo->times <small>time".
	  ($plyrIpInfo->times != 1 ? "s" : "")."</small></td>".
	  "<td align=center width=150>$plyrIpInfo->lasttime</td>".
	  "<td align=center width=150>".
	  ($plyrIpInfo->endtime && $plyrIpInfo->endtime != "0000-00-00 00:00:00"
	   ? $plyrIpInfo->endtime : "n/a")."</td>".
	  "</tr>";
      }

    }
    if ($C > $start+$limit) {
      $Left = $C - ($start+$limit);
      echo "</table><div id=\"ajax\"><table width=750><tr><td>".
	"<span onclick=\"ShowIPs ('ajax.doubleaccts.utils.inc.php?DoIP=1&ids=".urlencode ($ids)."&start=".
	($start+$limit)."')\"><u>"."Click here to get next ".($Left < $limit ? $Left : $limit)." adresses ($Left left).</u></span>".
	"</td></tr></table></div>";
    }
    echo "</table>";

  } else {
    echo "<table width=750 bgColor=Red><tr><td>You lost your privilleges.</td></tr></table>";
  }
 }