<script type="text/javascript" language="javascript">

function ShowIPs (url) {
  $('#ajax').html('<table width=750><tr><td colSpan=9>' +
      'Please wait a while...</td></tr>' +
      '</table>');

  asyncRequest({
    url: url,
    success: function(response) {
      $('#ajax').html(response);
    },
  });
}
</script>

<?php

include 'doubleaccts.utils.inc.php';

// SANITIZE INPUT
$ids = $_REQUEST['ids'];
$trackset = $_REQUEST['trackset'];
$rem = $_REQUEST['rem'];

// check for privilleges
$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$db = Db::get();

  echo "<center><h2>Research on multiple accounts</h2></table>";

  echo
    "<form method=post action=\"index.php?page=doubleaccts\">".
    "Enter account IDs separated by comma (or single ID):<br>".
    "<input size=\"34\" name=\"ids\">".
    "<input type=submit value=\"search\">".
    "</form>";

  if ($ids) { // ids provided

    $ids = urldecode($ids);
    $idsArray = explode(",", $ids);

    $report = $playerInfo->getFullName() . " searched for cooperation of player(s): $ids";
    Report::saveInDb("playersearch", $report);

    $day = GameDate::NOW()->getDay();

    // Multiplayer raws passing tracking on/off
    $stm = $db->prepareWithIntList("SELECT id, firstname, lastname FROM players WHERE id IN (:ids)", [
      "ids" => $idsArray,
    ]);
    $stm->execute();
    $players = $stm->fetchAll();
    $PlayerCount = count($players);
    $idstring = "|";
    foreach ($players as $X) {
      $idstring .= $X->id."|";
      $namestring .= ($namestring ? "\r" : "").$X->id."\t$X->firstname $X->lastname";
    }

    $stm = $db->prepare("SELECT 1 FROM troubleplayers WHERE ids = :ids");
    $stm->bindStr("ids", $idstring);
    $stm->execute();
    $track = $stm->executeScalar();
    if ($trackset) {
      if ($track) {
        $stm = $db->prepare("DELETE FROM troubleplayers WHERE ids = :ids");
        $stm->bindStr("ids", $idstring);
        $stm->execute();
      } else {
        $stm = $db->prepare("INSERT INTO troubleplayers (ids, names, owner) VALUES (:ids, :names, :owner)");
        $stm->bindStr("ids", $idstring);
        $stm->bindStr("names", $namestring);
        $stm->bindInt("owner", $player);
        $stm->execute();
      }
      $track = !$track;
    }

    // Players summary

    echo "<table width=750><tr><td colSpan=2><b>Players summary:</b></td>
      <td colSpan=7 align=right>".
      ($PlayerCount > 1 ? "<a href=\"index.php?page=doubleaccts&trackset=1&ids=".urlencode ($ids)."\">
      <small>".($track ? "stop" : "start")." multiplayer cooperation tracking</small></a>" : "")."</td></tr>";

    PlayerInfo ();
    echo "</table>";

    // Characters summary

    $stm = $db->prepareWithIntList("
      SELECT * FROM chars WHERE player in (:ids)
      ORDER by player", [
      "ids" => $idsArray,
    ]);
    $stm->execute();
    $lastPlayer = -1;
    echo "<br><br><table width=750><tr><td colSpan=9><b>Characters summary:</b></td></tr>";

    foreach ($stm->fetchAll() as $charInfo) {
      if ($charInfo->player != $lastPlayer) {
        if ($lastPlayer > 0)
          echo "</td></tr>";

        $lastPlayer = $charInfo->player;
        echo "<tr>".
          "<td colSpan=2>".$playerName [$charInfo->player].
          "</tr><tr><td width=30>&nbsp;</td><td>";
      }
      $age = $charInfo->spawning_age + floor (($day - $charInfo->register)/20);

      $dead = $charInfo->status != 1;
      echo ($dead ? "<font color=#808080>" : "")."<small>$charInfo->id</small> ".$charInfo->name." ($age), ".
        ($dead ? "</font>" : "");
      $Chars [$charInfo->id] = $charInfo;
    }
    echo "</td></tr></table>";

    echo
      "<br><br><table width=750><tr><td colSpan=9><b>Login IP summary:</b></td></tr>".
      "</table>";
    echo "<div id=\"ajax\">".
      "<table width=750><tr><td colSpan=9>".
      "<span onclick=\"ShowIPs ('ajax.doubleaccts.utils.inc.php?DoIP=1&ids=".urlencode ($ids)."')\"><u>Press here to show IP research</u></span></td></tr>".
      "</table>";

    echo "</div>";

    // More than 1 char in location

    echo
      "<br><br><table width=750><tr><td colSpan=9><b>".
      "Multiple characters in the same place: </b></td></tr>";

    $stm = $db->prepareWithIntList("
      SELECT l.x, l.y, COUNT(*) as count
      FROM chars ch, locations l
      WHERE ch.player in (:ids)
        AND ch.location = l.id
        AND ch.location > 0
        AND ch.status = :status
      GROUP BY l.x, l.y
      HAVING count > 1
    ", [
      "ids" => $idsArray,
    ]);
    $stm->bindInt("status", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();

    $Count = 0;
    foreach ($stm->fetchAll() as $posInfo) {

      $Count++;
      $stm = $db->prepare("
        SELECT oln.name FROM locations l, oldlocnames oln WHERE l.id = oln.id
        AND l.x = :x AND l.y = :y AND l.type = :outside"
      );
      $stm->bindInt("x", $posInfo->x);
      $stm->bindInt("y", $posInfo->y);
      $stm->bindInt("outside", LocationConstants::TYPE_OUTSIDE);
      $stm->execute();

      $nameInfo =$stm->fetchObject();
      if ($nameInfo) {
        $nameInfo = $nameInfo->name;
      } else {
        $nameInfo = "<i>at position $posInfo->x, $posInfo->y</i>";
      }

      $stm = $db->prepareWithIntList("
        SELECT ch.name as chname, ch.player, l.name as lname, l.type
        FROM chars ch, locations l
        WHERE ch.player in (:ids) 
          AND l.x = :x AND l.y = :y
          AND ch.location = l.id
          AND ch.location > 0
          AND ch.status = :active
          ORDER BY player", [
            "ids" => $idsArray,
      ]);
      $stm->bindInt("x", $posInfo->x);
      $stm->bindInt("y", $posInfo->y);
      $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
      $stm->execute();

      echo
        "<tr><td colSpan=2>$nameInfo</td></tr>".
        "<tr><td width=30>&nbsp;</td><td>";

      foreach ($stm->fetchAll() as $charLocInfo) {
        echo "<font color=\"".$playerColour [$charLocInfo->player]."\">".
        $charLocInfo->chname."</font>".
        ($charLocInfo->type > 1 ? " <small> at $charLocInfo->lname</small>" : "").", ";
      }
      echo "</td></tr>";

    }
    if (!$Count) echo "<tr><td colSpan=8>No items found.</td></tr>";

    echo "</table>";

    // Cooperation on projects

    echo "<br><br><table width=750><tr><td colSpan=9><b>Cooperation on projects:</b>".
      "</td></tr>";

    // Unnecessary connection with players
    $stm = $db->prepareWithIntList("
      SELECT pr.id, pr.name, pr.turnsleft, pr.turnsneeded, 
                         pr.initiator, ch.id as coop, ch.project,
       init_day, init_turn
      FROM projects pr, chars ch
      WHERE player IN (:ids) 
                    AND (pr.id = ch.project OR pr.initiator = ch.id)
        AND ch.status = :active
    ", [
      "ids" => $idsArray,
    ]);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();

    $Projects = [];
    foreach ($stm->fetchAll() as $projInfo) {
      $Add = !$Projects [$projInfo->id];
      $Projects [$projInfo->id]["c"]++;
      $Projects [$projInfo->id]["i"] = $projInfo->initiator;
      $Projects [$projInfo->id]["p"][$projInfo->coop] = true;
      $Projects [$projInfo->id]["n"] = $projInfo->name;
      $Projects [$projInfo->id]["tl"] = $projInfo->turnsleft;
      $Projects [$projInfo->id]["tn"] = $projInfo->turnsneeded;
      $Projects [$projInfo->id]["time"] = $projInfo->init_day
                                    . "-" . $projInfo->init_turn;
      if ($Add)
        $namesRef [$projInfo->name][] =& $Projects [$projInfo->id];
    }

    // using raws on projects initiated by this player
    $stm = $db->prepareWithIntList("
      SELECT e.*, eo.observer
      FROM chars ch, events_obs eo, events e
      WHERE ch.player IN (:ids)
                          AND eo.observer = ch.id 
                          AND e.type = :useRawsEvent 
                          AND eo.event = e.id
    ", [
      "ids" => $idsArray,
    ]);
    $stm->bindInt("useRawsEvent", 85);
    $stm->execute();
    foreach ($stm->fetchAll() as $eventInfo) {
      preg_match ("|MATERIAL=(.*) AMOUNT=(.*) PROJECT=(.*) PROJID=(.*)|", $eventInfo->parameters, $result);
      $result [3] = urldecode ($result [3]);
      if ($result [4]) if (!is_null ($Projects [$result [4]])) {
        $ref =& $Projects [$result [4]];
        if (!$ref ["p"][$eventInfo->observer]) {
          $ref ["p"][$eventInfo->observer] = true;
          $ref ["c"]++;
        }
        $X = [];
        $X ["mat"] = $result [1];
        $X ["am"] = $result [2];
        $X ["who"] = $eventInfo->observer;
        $X ["when"] = $eventInfo->day."-".$eventInfo->hour;
        $ref ["rw"][] = $X;
        }
    }

    // using objects
    $stm = $db->prepareWithIntList("
      SELECT e.*, eo.observer
      FROM chars ch, events_obs eo, events e
      WHERE ch.player IN (:ids) AND eo.observer = ch.id AND e.type = :useObjectEvent AND eo.event = e.id
    ", [
      "ids" => $idsArray,
    ]);
    $stm->bindInt("useObjectEvent", 41);
    $stm->execute();

    foreach ($stm->fetchAll() as $eventInfo) {
      preg_match ("|OBJECT=(.*) PROJECT=(.*) PROJID=(.*)|", $eventInfo->parameters, $result);
      $result [1] = urldecode ($result [1]);
      $result [2] = urldecode ($result [2]);
      if ($result [3]) if (!is_null ($Projects [$result [3]])) {
        $ref =& $Projects [$result [3]];
        if (!$ref ["p"][$eventInfo->observer]) {
          $ref ["p"][$eventInfo->observer] = true;
          $ref ["c"]++;
        }
        $X = [];
        $X ["mat"] = $result [1];
        $X ["am"] = "";
        $X ["who"] = $eventInfo->observer;
        $X ["when"] = $eventInfo->day."-".$eventInfo->hour;
        $ref ["rw"][] = $X;
      }
    }

    reset ($Projects);
    foreach ($Projects as $projectId => $projInfo) {
      if ($projInfo ["c"] > 1 && $projectId) {

        $Count++;

        echo "<tr><td colSpan=2>" . $projInfo["n"] ." (".
          floor (100-100*$projInfo ["tl"]/$projInfo ["tn"]).
          "%) &ndash; initiated by ".CharName ($projInfo ["i"])." at ".$projInfo ["time"].":</td></tr>";
        $stm = $db->prepare("SELECT id FROM chars WHERE project = :projectId");
        $stm->bindInt("projectId", $projectId);
        $stm->execute();
        $workers = $stm->fetchScalars();

        if (count($workers)) {
          echo "<tr><td width=30>&nbsp;</td><td>currently working: ";
          foreach ($workers as $id) {
            echo CharName($id) . ", ";
          }
          echo "</td></tr>";
        }

        for ($i = 0; $i < count ($projInfo ["rw"]); $i++) {
          echo "<tr><td width=30>&nbsp;</td><td>At ".$projInfo ["rw"][$i]["when"]." ".
          CharName ($projInfo ["rw"][$i]["who"])." used ".$projInfo ["rw"][$i]["am"]." ".
          $projInfo ["rw"][$i]["mat"]." on this project. ".
          "</td></tr>";
        }
      }
    }

    if ($Count == 0)
      echo "<tr><td colSpan=8>No cooperation found.</td></tr>";
    echo "</table>";

    echo "<br><br><table width=750><tr><td colSpan=9>".
      "<b><font color=\"Yellow\">Following features use events, so it's from last 7 days only</font>".
      "</b><br><br>".
      "<b>Raws and objects transfer:</b>";

    // passing raws
    $Count = 0;
    $stm = $db->prepareWithIntList("
      SELECT e.*, eo.observer
      FROM chars ch, events_obs eo, events e
      WHERE ch.player IN (:ids) AND eo.observer = ch.id AND e.type = :giveRawsEvent AND eo.event = e.id
    ", [
      "ids" => $idsArray,
    ]);
    $stm->bindInt("giveRawsEvent", 78);
    $stm->execute();

    foreach ($stm->fetchAll() as $eventInfo) {
      preg_match ("|MATERIAL=(.*) VICTIM=(.*)|", urldecode ($eventInfo->parameters), $result);

      if (SuspChar ($result [2])) {
        $Count++;
        echo "<tr><td width=30>&nbsp;</td><td> ".CharName ($eventInfo->observer).
          " passed ".$result [1]." to ".CharName ($result [2])." at ".$eventInfo->day."-".$eventInfo->hour.
          ".</td></tr>";
      }
    }

    // passing items
    $stm = $db->prepareWithIntList("
      SELECT e.*, eo.observer
      FROM chars ch, events_obs eo, events e
      WHERE ch.player IN (:ids) AND eo.observer = ch.id AND e.type=:giveObjectEvent AND eo.event = e.id
    ", [
      "ids" => $idsArray,
    ]);
    $stm->bindInt("giveObjectEvent", 143);
    $stm->execute();

    foreach ($stm->fetchAll() as $eventInfo) {
      preg_match ("|OBJECT=(.*) TYPE=(.*) VICTIM=(.*)|", urldecode ($eventInfo->parameters), $result);

      if (SuspChar ($result [3])) {
        $Count++;

        $stm = $db->prepare("SELECT ot.name FROM objecttypes ot, objects o WHERE o.id= :id AND ot.id = o.type");
        $stm->bindInt("id", $result[1]);
        $objname = $stm->executeScalar();

         echo "<tr><td width=30>&nbsp;</td><td> ".CharName ($eventInfo->observer).
          " passed ".$objname." to ".CharName ($result [3])." at ".$eventInfo->day."-".$eventInfo->hour.
          ".</td></tr>";
      }
    }

    if (!$Count) echo "<tr><td colSpan=8>No cooperation found.</td></tr>";
    echo "</table>";

    $attacks = CooperationUtil::getFightingCooperationFor($idsArray, $db);
    $fightText = Pipe::from($attacks)->map(function($attack) {
      return $attack['date'] . ": " . CharName($attack['victim']) . " attacked by " .
        CharName($attack['perpetrator']) ." with " . $attack['weapon'];
    })->toArray();


    echo "<br><br><table width=750><tr><td colSpan=9><b>Cooperation on fights:</b><br>";
    if (count($fightText) > 0) {
      echo implode("<br>", $fightText);
    } else {
      echo "No cooperation found";
    }
    echo "</td></tr></table>";

    echo "<br><br><table width=750><tr><td colSpan=9><b>Cooperation on dragging:</b>".
      "<br><i>not yet implemented</i></td></tr></table>";

    echo "<br><br><table width=750><tr><td colSpan=9><b>Vocabulary comparision:</b>".
      "<br><i>not yet implemented</i></td></tr></table>";
   } else {
    if ($rem > 0) {
      $stm = $db->prepare("SELECT names FROM troubleplayers WHERE id = :id");
      $stm->bindInt("id", $rem);
      $remNames = $stm->executeScalar();

      $stm = $db->prepare("DELETE FROM troubleplayers WHERE id = :id");
      $stm->bindInt("id", $rem);
      $stm->execute();
      $report = $playerInfo->getFullName() . " removed tracking for: \n$remNames\n\n";
      Report::saveInDb("playersearch", $report);
    }

    echo "<center><h2>Active trackings</h2><br>";
    echo "<table>";
    $stm = $db->query("
      SELECT tp.id, tp.owner, p.firstname, p.lastname, tp.ids, tp.names, tp.ids
      FROM troubleplayers tp
      LEFT JOIN players p ON tp.owner = p.id
      ORDER BY owner");
    $last = -1;
    foreach ($stm->fetchAll() as $track) {
      if ($last != $track->owner) {
        $last = $track->owner;
        echo "<tr colSpan=3 widht=100%><td style='border-top: 1px solid #99bb99; color: yellow'><b>by $track->firstname $track->lastname</b></td></tr>";
        $Odd = 0;
      }
      echo ($Odd == 0 ? "<tr>" : "").
        "<td vAlign=top style='border-top: 1px solid #99bb99'>".
        "<a href=\"index.php?page=doubleaccts&rem=$track->id\"><small>remove</small></a> <small>|</small> ".
        "<a href=\"index.php?page=doubleaccts&ids=".str_replace ("|", ",", substr ($track->ids, 1, -1))."\"><small>view current</small></a><br>".
        str_replace ("\r", "<br>", $track->names).
        "</td>".
        ($Odd == 2 ? "</tr>" : "");
      $Odd = ($Odd + 1) % 3;
    }
    echo "</table>";
   }
  echo "<BR><A HREF=\"index.php?page=listplayers\">Manage database of players</A>";
  echo "<BR><A HREF=\"index.php?page=player\">Back to player page</A>";
  echo "</CENTER>";
