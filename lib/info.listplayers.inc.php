<?php

// SANITIZE INPUT
$player_id = HTTPContext::getInteger("player_id", null);
$username = HTTPContext::getRawString("username");
$firstname = HTTPContext::getRawString("firstname");
$lastname = HTTPContext::getRawString("lastname");
$country = HTTPContext::getRawString("country");
$ip = HTTPContext::getRawString("ip");
$email = HTTPContext::getRawString("email");
$status = HTTPContext::getRawString("status");
$character_id = HTTPContext::getInteger("character_id");
$name = HTTPContext::getRawString("name");
$language = HTTPContext::getInteger("language");
$location = HTTPContext::getInteger("location");
$email = HTTPContext::getRawString("email");
$data = HTTPContext::getRawString("data");

$trouble = $_REQUEST['trouble'];
$set = $_REQUEST['set'];

$adminPlayer = Request::getInstance()->getPlayer();
if (!$adminPlayer->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
  CError::throwRedirect("player", "You are not authorized to read the players list");
}

$db = Db::get();

show_title("LIST PLAYERS");

echo "<center><table>";

if ($data) {
  $playerInfo = Request::getInstance()->getPlayer();
  $report = print_r($_POST, true);
  $report = "{$playerInfo->getFullName()} searched in players database with: $report";
  Report::saveInDb("playersearch", $report);

  $count = 0;

  echo "<tr><td colspan=\"2\" width=\"700\"><table>";

  $result_count = 0;

  switch ($set) {

    case "player" :
      $where = "1"
        . ($player_id ? " AND p.id = " . intval($player_id) : "")
        . ($username ? " AND p.username LIKE " . $db->quote("%$username%") : "")
        . ($firstname ? " AND p.firstname LIKE " . $db->quote("%$firstname%") : "")
        . ($lastname ? " AND p.lastname LIKE " . $db->quote("%$lastname%") : "")
        . ($email ? " AND p.email LIKE " . $db->quote("%$email%") : "")
        . ($ip ? " AND (i.ip LIKE " . $db->quote("%$ip%") . " OR i.client_ip LIKE " . $db->quote("%$ip%") ." OR p.lastlogin LIKE " . $db->quote("%$ip%") . ")" : "")
        . ($status ? " AND p.status " . ($status == 'l' ? "= 3" : "< 3") : "")
        . ($country ? " AND p.country LIKE " . $db->quote("%$country%") : "")
        . (($trouble == "trouble") ? " AND p.trouble = 1" : "");

      if ($ip) {
        $tables = "ips i LEFT OUTER JOIN players p ON i.player = p.id";
      } else {
        $tables = "players p";
      }

      $id_str = $ip ? "i.player" : "p.id";

      $stm = $db->query("SELECT $id_str, p.* FROM $tables WHERE $where GROUP BY $id_str ORDER BY $id_str");
      $result_count = $stm->rowCount();

      break;

    case "character" :
      $prev_player = 0;

      if (!empty($character_id)) {
        $id_query = "chars.id = " . intval($character_id) . " AND ";
      } else {
        $id_query = "";
      }

      if ($location) {
        $stm = $db->query("SELECT * FROM chars WHERE $id_query name LIKE " . $db->quote("%$name%") . " AND language = ". intval($language) . " AND location = " . intval($location) . " AND status <= " . CharacterConstants::CHAR_ACTIVE . " ORDER BY player");
      } else {
        $stm = $db->query("SELECT * FROM chars WHERE $id_query chars.name LIKE ". $db->quote("%$name%") .
          ($language > 0 ? "AND chars.language = " . intval($language) : "") . " ORDER BY chars.player");
      }
      $stm->execute();

      $charCounts = [];
      foreach ($stm->fetchAll() as $char_info) {
        if (!isset($charCounts[$char_info->player])) {
          $charCounts[$char_info->player] = 0;
        }
        $charCounts[$char_info->player]++;
      }

      $playerIds = array_keys($charCounts);
      $result_count = 0;
      if (!empty($playerIds)) {
        $stm = $db->prepareWithIntList("SELECT * FROM players WHERE id IN (:playerIds) ORDER BY lastname, firstname", [
          "playerIds" => $playerIds,
        ]);
        $stm->execute();
        $result_count = $stm->rowCount();
      }

      break;
  }

  if (!$result_count) {
    echo "<tr><td>No results found!</td></tr>";
  } elseif ($result_count > 300) {
    echo "<tr><td>Too many matches! Found $result_count.</td></tr>";
  } else {

    foreach ($stm->fetchAll() as $player_info) {
      $count++;

      echo "<tr valign=\"top\"><td width=\"250\"><a href=\"mailto:$player_info->email\" title=\"mail to player\">$player_info->firstname $player_info->lastname</a>";

      echo " <a href=\"index.php?page=infoplayer&player_id=$player_info->id\">[more info]</a></td>";

      echo "<td width=\"50\">";
      switch ($player_info->status) {
        case 0 :
          echo "(P)";
          break;
        case 1 :
        case 2 :
          echo "(A)";
          break;
        case 3 :
          echo "(L)";
          break;
        case 4 :
          echo "(R)";
          break;
        case 5 :
          echo "(U)";
          break;
        case 6 :
          echo "(I)";
          break;
      }
      echo "</td>";

      echo "<td width=\"100\">$player_info->country</td>";
      echo "<td width=\"60\">$player_info->age</td>";

      $stm = $db->prepare("SELECT COUNT(id) FROM chars WHERE player = :playerId");
      $stm->bindInt("playerId", $player_info->id);
      $ch_count = $stm->executeScalar();

      echo "<td width=\"90\">$ch_count chars <a href=\"index.php?page=infoplayer&player_id=$player_info->id\">[i]</a></td>";
      echo "<td width=\"50\">$player_info->lastdate-$player_info->lasttime</td>";
      echo "<td>";
      if ($player_info->onleave > 0) {
        echo "on leave: $player_info->onleave";
      }
      echo "</td>";
      echo "<td>($player_info->register)</td>";
      echo "<td>";
      if (isset($charCounts)) {
        echo "(" . $charCounts[$player_info->id] . ") ";
      }
      if ($player_info->trouble == 1) {
        echo "<font color=\"yellow\">TL</font>";
      }
      echo "</td>";
      echo "</tr>";
    }
  } // if (!$result_count) {} else {

  echo "</table></td></tr>";

  echo "<tr><td colspan=\"2\" align=\"center\">";
  echo "<br />(Counted: $count)";
  echo "<br />Go <a href=\"index.php?page=listplayers\">back to player selection page</a> ...";
  echo "<br /><a href=\"index.php?page=pendingplayers\">Manage database of pending players</a>";
  echo "<br />Go <a href=\"index.php?page=player\">back to player page</a> ...</td></tr>";

  echo "<center><table>";
} else {

  echo "<tr><td width=\"700\" colspan=\"2\">Fill in any or all of the below fields. You can either enter full contents, or just parts (e.g. both 'kin' and 'Elkink' in lastname will show up player 'Jos Elkink'. The field Id, however, has to contain the exact id of the player you are looking for.)</td></tr>";

  echo "<form method=\"post\" action=\"index.php?page=$page\">";

  echo "<tr><td width=\"300\"><br />Id:</td><td width=\"400\"><br /><input type=\"text\" name=\"player_id\" size=\"5\"></td></tr>";
  echo "<TR><TD WIDTH=300>Username:</TD><TD WIDTH=400><INPUT TYPE=text NAME=username SIZE=50></TD></TR>";
  echo "<TR><TD WIDTH=300><br>Firstname:</TD><TD WIDTH=400><br><INPUT TYPE=text NAME=firstname SIZE=50></TD></TR>";
  echo "<TR><TD WIDTH=300>Lastname:</TD><TD WIDTH=400><INPUT TYPE=text NAME=lastname SIZE=50></TD></TR>";
  echo "<TR><TD WIDTH=300>Email:</TD><TD WIDTH=400><INPUT TYPE=text NAME=email SIZE=50></TD></TR>";
  echo "<TR><TD WIDTH=300>Country:</TD><TD WIDTH=400><INPUT TYPE=text NAME=country SIZE=50></TD></TR>";
  echo "<TR><TD WIDTH=300>IP etc.:</TD><TD WIDTH=400><INPUT TYPE=text NAME=ip SIZE=50></TD></TR>";
  echo "<TR><TD WIDTH=300>On Trouble List:</TD><TD WIDTH=400><INPUT TYPE=\"checkbox\" NAME=\"trouble\" value=\"trouble\"></TD></TR>";

  echo "<INPUT TYPE=hidden NAME=data VALUE=yes>";
  echo "<INPUT TYPE=hidden NAME=set VALUE=player>";

  echo "<TR><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=\"Search on player info\"></TD></TR></FORM>";

  echo "<FORM METHOD=post ACTION=\"index.php?page=$page\">";

  echo "<TR><TD WIDTH=300><BR>Id:</TD><TD WIDTH=400><BR><INPUT TYPE=text NAME=character_id SIZE=7></TD></TR>";
  echo "<TR><TD WIDTH=300><BR>Name:</TD><TD WIDTH=400><BR><INPUT TYPE=text NAME=name SIZE=50></TD></TR>";
  echo "<TR><TD WIDTH=300>Location ID:</TD><TD WIDTH=400><INPUT TYPE=text NAME=location SIZE=20></TD></TR>";
  echo "<TR><TD WIDTH=300><BR>Language:</TD><TD WIDTH=400><BR><SELECT NAME=language>";
  echo "<OPTION VALUE=0>Any";
  $stm = $db->query("SELECT id,name FROM languages ORDER BY name");
  foreach ($stm->fetchAll() as $lang_info) {
    echo "<OPTION VALUE=$lang_info->id >" . ucfirst($lang_info->name);
  }
  echo "</SELECT></TD></TR>";

  echo "<INPUT TYPE=hidden NAME=data VALUE=yes>";
  echo "<INPUT TYPE=hidden NAME=set VALUE=character>";

  echo "<TR><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=\"Search on character info\"></TD></TR></FORM>";

  $playerInfo = Request::getInstance()->getPlayer();
  if ($playerInfo->hasAccessTo(AccessConstants::SEARCH_CHARACTER_EVENTS)) {

    echo "<FORM METHOD=post ACTION=\"index.php?page=listevents\">";

    echo "<TR><TD WIDTH=700 COLSPAN=2><BR>Below you can search the database of events, either for all events that one character observes or events fitting the parameters you specify. Use % as a wildcard. When looking for characters, enter their ID, not the name. If you enter a character ID in the parameters, only events in which his name appears in the event message (e. g. \"You see John poke a bear\" but not \"You poke a bear\") will appear. Be sure not to include parts of the standard text (e. g. \"You leave\") in the parameters but rather just the place name. Case doesn't matter. Don't use quotation marks.</TD></TR>";

    echo "<TR><TD WIDTH=350 ALIGN=center><INPUT NAME=src_observer TYPE=radio VALUE=\"true\" CHECKED>See all events visible to character (enter ID):</TD><TD WIDTH=350><INPUT NAME=observer_id TYPE=text></TD></TR>";
    // echo "<TR><TD WIDTH=300 ALIGN=right> for time (day-hour, optional): </TD><TD WIDTH=400><INPUT NAME=src_cday TYPE=text SIZE=10> - <INPUT NAME=src_chour TYPE=text SIZE=5></TD></TR>";
    echo "<TR><TD WIDTH=350 ALIGN=center><INPUT NAME=src_observer TYPE=radio VALUE=\"false\">See all events that fulfill the following conditions (leave blank if any):</TD><TD></TD></TR>";
    echo "<TR><TD WIDTH=300 ALIGN=right> Time (Day-Hour): </TD><TD WIDTH=400><INPUT NAME=src_day TYPE=text SIZE=10> - <INPUT NAME=src_hour TYPE=text SIZE=5></TD></TR>";
    echo "<BR><TR><TD WIDTH=700 COLSPAN=2>1st parameter (e. g. ID of involved character, vehicle / building name, animal name, weapon name, part of speech...):</TD></TR>";
    echo "<TR><TD WIDTH=700 COLSPAN=2 ALIGN=right><INPUT NAME=src_par1 TYPE=text></TD></TR>";
    echo "<BR><TR><TD WIDTH=700 COLSPAN=2>2nd parameter, if applicable (see above):</TD></TR>";
    echo "<TR><TD WIDTH=700 COLSPAN=2 ALIGN=right><INPUT NAME=src_par2 TYPE=text></TD></TR>";

    echo "<TR><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=\"Search in events\"></TD></TR></FORM>";
  }
}

echo "<TR><TD COLSPAN=2 ALIGN=center><BR>" .
  "<A HREF=\"index.php?page=listlocationspd\" target=\"_blank\">Locations Overview</A><BR>" .
  "<A HREF=\"index.php?page=doubleaccts\">Search for player(s) cooperation</A><BR>" .
  "<A HREF=\"index.php?page=multiaccount_tracker\">Multiaccounts caught by a cookieless tracker</A><BR>" .
  "<A HREF=\"index.php?page=listlimitations\">Player limitations overview</A><BR>" .
  "<A HREF=\"index.php?page=pendingplayers\">Manage database of pending players</A><BR>" .
  "<A HREF=\"index.php?page=player\">Go back to player page</A></TD></TR>";
echo "</TABLE></CENTER>";

