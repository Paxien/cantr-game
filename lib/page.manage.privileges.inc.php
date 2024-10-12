<?php

// SANITIZE INPUT
$pid = HTTPContext::getInteger("pid");
$cid = HTTPContext::getInteger("cid");
$prpage = HTTPContext::getInteger("prpage");
$council = HTTPContext::getInteger("council");
$status = HTTPContext::getInteger("status");
$special = HTTPContext::getRawString("special");
$action = $_REQUEST['action'];
$priv = $_REQUEST['priv'];

$db = Db::get();

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::ALTER_PRIVILEGES)) {

  $victimInfo = Player::loadById($pid);

  switch ($action) {

    case "remove" :        // Remove an assignment
      $stm = $db->prepare("SELECT assignments.status AS status,
        assignments.special AS special, councils.name AS council 
        FROM assignments, councils WHERE player = :playerId AND council = :council AND assignments.council = councils.id");
      $stm->bindInt("playerId", $pid);
      $stm->bindInt("council", $cid);
      $stm->execute();
      $assignment_info = $stm->fetchObject();

      $stm = $db->prepare("DELETE FROM assignments WHERE player = :playerId AND council = :council");
      $stm->bindInt("playerId", $pid);
      $stm->bindInt("council", $cid);
      $stm->execute();

      $message = "Administrator {$playerInfo->getFullName()} removed from assignments:\n\n";
      $message .= "{$victimInfo->getFullName()} as ";

      switch ($assignment_info->status) {
        case 0 :
          $message .= "hidden member";
          break;
        case 1 :
          $message .= "chair";
          break;
        case 2 :
          $message .= "senior member";
          break;
        case 3 :
          $message .= "member";
          break;
        case 4 :
          $message .= "special member ($assignment_info->special)";
          break;
        case 5 :
          $message .= "aspirant member";
          break;
        case 6 :
          $message .= "on leave";
          break;
      }
      $message .= " of the $assignment_info->council.";

      $mailService = new MailService($playerInfo->getFullName(), $GLOBALS['emailPersonnel'], $playerInfo->getEmail());
      $mailService->sendPlaintext($GLOBALS['emailGAB'].",".$GLOBALS['emailPersonnel'], "Change in assignments", $message);

      break;

    case "removepr" :     // Remove access privilege
      $stm = $db->prepare("SELECT description FROM access_types WHERE id = :id");
      $stm->bindInt("id", $prpage);
      $stm->execute();
      $access_info = $stm->fetchObject();

      $stm = $db->prepare("DELETE FROM access WHERE player = :playerId AND page = :page");
      $stm->bindInt("playerId", $pid);
      $stm->bindInt("page", $prpage);
      $stm->execute();

      $message = "Administrator {$playerInfo->getFullName()} removed from privileges:\n\n";
      $message .= "{$victimInfo->getFullName()} as $access_info->description.";

      $mailService = new MailService($playerInfo->getFullName(), $GLOBALS['emailPersonnel'], $playerInfo->getEmail());
      $mailService->sendPlaintext($GLOBALS['emailGAB'].",".$GLOBALS['emailPersonnel'], "Change in privileges", $message);

      break;

    case "add" :          // Add assignment
      show_title("ADD ASSIGNMENT");

      echo "<CENTER><TABLE WIDTH=700>";
      echo "<FORM METHOD=post ACTION=\"index.php?page=manage_privs&action=saveadd\">";
      echo "<TR><TD>Player:</TD><TD>{$victimInfo->getFullName()}</TD></TR>";
      echo "<TR><TD>Council:</TD><TD><SELECT NAME=council>";

      $stm = $db->query("SELECT id, name FROM councils");
      foreach ($stm->fetchAll() as $councilInfo) {
        echo "<OPTION VALUE=$councilInfo->id>$councilInfo->name";
      }

      echo "</SELECT></TD></TR>";
      echo "<TR><TD>Status:</TD><TD><SELECT NAME=status>";
      echo "<OPTION VALUE=1>Chair";
      echo "<OPTION VALUE=2>Senior Member";
      echo "<OPTION VALUE=3>Member";
      echo "<OPTION VALUE=4>Special Member";
      // echo "<OPTION VALUE=5>Aspirant Member";
      echo "<OPTION VALUE=6>On Leave";
      echo "</SELECT></TD></TR>";
      echo "<TR><TD>Description (optional):</TD><TD><INPUT TYPE=text NAME=special SIZE=30></TD></TR>";
      echo "<INPUT TYPE=hidden NAME=pid VALUE=$pid>";
      echo "<TR><TD COLSPAN=2 ALIGN=center><INPUT TYPE=submit VALUE=Store></TD></TR>";
      echo "</FORM>";
      echo "</TABLE></CENTER>";

      break;

    case "saveadd" :        // Save added assignment
      $stm = $db->prepare("INSERT INTO assignments (player, council, status, special)
        VALUES (:playerId, :council, :status, :special)");
      $stm->bindInt("playerId", $pid);
      $stm->bindInt("council", $council);
      $stm->bindInt("status", $status);
      $stm->bindStr("special" ,$special);
      $stm->execute();


      $stm = $db->prepare("SELECT name FROM councils WHERE id = :council");
      $stm->bindInt("council", $council);
      $councilName = $stm->executeScalar();

      $message = "Administrator {$playerInfo->getFullName()} added to assignments:\n\n";
      $message .= "{$victimInfo->getFullName()} as ";

      switch ($status) {

        case 0 :
          $message .= "hidden member";
          break;
        case 1 :
          $message .= "chair";
          break;
        case 2 :
          $message .= "senior member";
          break;
        case 3 :
          $message .= "member";
          break;
        case 4 :
          $message .= "special member ($special)";
          break;
        case 5 :
          $message .= "aspirant member";
          break;
        case 6 :
          $message .= "on leave";
          break;
      }

      $message .= " of the $councilName.";

      $mailService = new MailService($playerInfo->getFullName(), $GLOBALS['emailPersonnel'], $playerInfo->getEmail());
      $mailService->sendPlaintext($GLOBALS['emailGAB'].",".$GLOBALS['emailPersonnel'], "Change in assignments", $message);

      break;

    case "editpr" :
      show_title("EDIT ACCESS PRIVILEGES");

      echo "<center><table width=700>";
      echo "<form method=post action=\"index.php?page=manage_privs&action=saveeditpr\">";
      echo "<tr><td>Player:</td><td>{$victimInfo->getFullName()}</td></tr>";
      echo "<tr><td valign=top>Privileges:</td><td>";

      $stm = $db->prepare("SELECT page FROM access WHERE player = :playerId");
      $stm->bindInt("playerId", $pid);
      $stm->execute();
      $access_array = $stm->fetchScalars();

      $stm = $db->query("SELECT id,description FROM access_types");
      foreach ($stm->fetchAll() as $access_type_info) {

        echo "<input type=checkbox name=priv[$access_type_info->id]";

        if (count($access_array) && in_array($access_type_info->id, $access_array)) {
          echo " checked";
        }
        echo "> $access_type_info->description<br />";
      }

      $stm = $db->prepare("SELECT access AS page FROM ceAccess WHERE player = :playerId");
      $stm->bindInt("playerId", $pid);
      $stm->execute();
      foreach ($stm->fetchAll() as $access_info) {
        $access_array[] = $access_info->page;
      }

      $stm = $db->query("SELECT id,description FROM ceAccessTypes");
      foreach ($stm->fetchAll() as $access_type_info) {

        echo "<input type=checkbox name=privCE[$access_type_info->id]";
        if (count($access_array) && in_array($access_type_info->id, $access_array)) {
          echo " checked";
        }

        echo "> [Cantr Explorer] $access_type_info->description<br />";
      }

      echo "</td></tr>";
      echo "<INPUT TYPE=hidden NAME=pid VALUE=$pid>";
      echo "<TR><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=Store></TD></TR>";
      echo "</FORM>";
      echo "</TABLE></CENTER>";

      break;

    case "saveeditpr" :           // Save edited privilege
      $message = "Administrator {$playerInfo->getFullName()} alter privileges ";
      $message .= "of {$victimInfo->getFullName()}:\n\n";

      $access_set = [];
      $stm = $db->prepare("SELECT page FROM access WHERE player = :playerId");
      $stm->bindInt("playerId", $pid);
      $stm->execute();
      foreach ($stm->fetchAll() as $access_info) {
        $access_set[] = $access_info->page;
      }

      $stm = $db->prepare("DELETE FROM access WHERE player = :playerId");
      $stm->bindInt("playerId", $pid);
      $stm->execute();
      $stm = $db->query("SELECT id, description FROM access_types");
      foreach ($stm->fetchAll() as $access_info) {
        if (isset($priv[$access_info->id])) {
          $stm = $db->prepare("INSERT INTO access (player,page) VALUES (:playerId, :page)");
          $stm->bindInt("playerId", $pid);
          $stm->bindInt("page", $access_info->id);
          $stm->execute();
          if (in_array($access_info->id, $access_set)) {
            $message .= "(is still)   $access_info->description\n";
          } else {
            $message .= "(is now)     $access_info->description\n";
          }
        } elseif (in_array($access_info->id, $access_set)) {
          $message .= "(was)        $access_info->description\n";
        }
      }

      $access_set = [];
      $stm = $db->prepare("SELECT access AS page FROM ceAccess WHERE player = :playerId");
      $stm->bindInt("playerId", $pid);
      $stm->execute();
      foreach ($stm->fetchAll() as $access_info) {
        $access_set[] = $access_info->page;
      }

      $stm = $db->prepare("DELETE FROM ceAccess WHERE player = :playerId");
      $stm->bindInt("playerId", $pid);
      $stm->execute();
      $stm = $db->query("SELECT id, description FROM ceAccessTypes");
      foreach ($stm->fetchAll() as $access_info) {
        if (isset($privCE[$access_info->id])) {
          $stm = $db->prepare("INSERT INTO ceAccess (player, access) VALUES (:playerId, :page)");
          $stm->bindInt("playerId", $pid);
          $stm->bindStr("page", $access_info->page);
          $stm->execute();
          if (in_array($access_info->id, $access_set)) {
            $message .= "(is still)   [Cantr Explorer] $access_info->description\n";
          } else {
            $message .= "(is now)     [Cantr Explorer] $access_info->description\n";
          }
        } elseif (in_array($access_info->id, $access_set)) {
          $message .= "(was)        [Cantr Explorer] $access_info->description\n";
        }
      }

      $mailService = new MailService($playerInfo->getFullName(), $GLOBALS['emailPersonnel'], $playerInfo->getEmail());
      $mailService->sendPlaintext($GLOBALS['emailGAB'].",".$GLOBALS['emailPersonnel'], "Change in privileges", $message);

      break;
  }
} else {
  CError::throwRedirectTag("player", "error_not_authorized");
}

if (in_array($action, ["remove", "removepr", "saveadd", "saveaddpr", "saveeditpr"])) {
  redirect("infoplayer", ["player_id" => $pid]);
}
