<?php
$requestData = Request::getInstance();
$adminPlayer = $requestData->getPlayer();

if (!$adminPlayer->hasAccessTo(AccessConstants::ACCEPT_PLAYERS)) {
  CError::throwRedirect("player", "You are not authorized to read the players list");
}

// SANITIZE INPUT
$player_id = HTTPContext::getInteger('player_id');
$player_not_found = false;
$step = $_REQUEST['step'];
$reasontype = $_REQUEST['reasontype'];
$reason = $_REQUEST['reason'];

$secLevel = HTTPContext::getInteger('sec_level');

$SECURITY_LEVELS = [
  RegistrationSecurityManager::RESTRICT_NONE => "NONE",
  RegistrationSecurityManager::RESTRICT_SUSPICIOUS => "SUSPICIOUS",
  RegistrationSecurityManager::RESTRICT_ALL => "ALL",
];

$db = Db::get();
// change intro server protection level
if (array_key_exists($secLevel, $SECURITY_LEVELS)) {
  $globalConfig = new GlobalConfig($db);
  $globalConfig->setIntroProtectionLevel($secLevel);

  $message = $adminPlayer->getFullName() . " changed intro server security level to " .
    $SECURITY_LEVELS[$secLevel] . ".";
  Report::saveInPlayerReport($message);
}

$js = '<script type="text/javascript">
    $(function() {
      $(".toshow").hide();
      $(".show").each(function(it) {
        $(this).click(function(evt) {
          $("#toshow_" + $(this).data("show")).toggle();
        });
      });
      
      $(".customRefusalForm").submit(function(event) {
        var formToValidate = $(event.target);
        var textInTextArea = formToValidate.find("textarea").val();
        
        if (!textInTextArea) {
          alert("The textarea for custom reason of refusal cannot be empty");
          return false;
        }
        return true;
      });
    });
  </script>';
echo $js;

if ($step) {
  $stm = $db->prepare("SELECT * FROM newplayers WHERE id = :playerId");
  $stm->bindInt("playerId", $player_id);
  $stm->execute();
  $application = $stm->fetchObject();

  $playerNotFound = empty($application);
  if ($playerNotFound) {
    echo "<center><div class=\"admin_error\">The pending player ($player_id) could not be found.
      Possibly handled by someone else simultaneously. It should be obvious from the PD History below.</div></center>";
  } else {
    $reviewedPlayer = Player::loadById($application->id);
    switch ($step) {
      case "accept" :
        $acceptationManager = new PlayerAcceptation($adminPlayer);
        $acceptationManager->accept($reviewedPlayer, $application);
        break;
      case "refuse" :
        $acceptationManager = new PlayerAcceptation($adminPlayer);
        $acceptationManager->reject($reviewedPlayer, $application, $reasontype, $reason);
        break;
      case "reactivate":
        if ($application->type == PlayersDeptConstants::NEWPLAYER_TYPE_REACTIVATION) {
          $domainAddress = Request::getInstance()->getDomainAddress();
          $reactivationManager = new AccountReactivation($adminPlayer, $domainAddress);
          $reactivationManager->accept($reviewedPlayer);
        }
        break;
      case "reactivate_refuse":
        if ($application->type == PlayersDeptConstants::NEWPLAYER_TYPE_REACTIVATION) {
          $domainAddress = Request::getInstance()->getDomainAddress();
          $reactivationManager = new AccountReactivation($adminPlayer, $domainAddress);
          $reactivationManager->reject($reviewedPlayer, $reason);
        }
        break;
    }

    $stm = $db->prepare("DELETE FROM newplayers WHERE id = :newPlayerId");
    $stm->bindInt("newPlayerId", $reviewedPlayer->getId());
    $stm->execute();
  }
}


show_title("PENDING PLAYERS");

echo "<div class=\"page\"><table>";
$env = Request::getInstance()->getEnvironment();
if ($env->introExists()){

  $globalConfig = new GlobalConfig($db);
  $currentProtectionLevel = $globalConfig->getIntroProtectionLevel();
  echo "<tr><td width=350>Intro Security - who needs acceptance to enter test char:</td><td width=350>";
  foreach ([RegistrationSecurityManager::RESTRICT_NONE => "none",
             RegistrationSecurityManager::RESTRICT_SUSPICIOUS => "suspicious",
             RegistrationSecurityManager::RESTRICT_ALL => "all"] as $lvlVal => $levelName) {
    echo "<a class='button_charmenu" . ($lvlVal == $currentProtectionLevel ? "active" : "")
      . "' href='index.php?page=pendingplayers&sec_level=$lvlVal'>$levelName</a>";
  }
  echo "</td></tr>";
  echo "<tr><td colspan=2><br><br><br><br></td></tr>";
}

function displayDecisionForApplication($playerId, $text, $stepName, $reasonType = "")
{
  echo "<TR VALIGN=top>";
  echo "<TD><FORM METHOD=post ACTION=\"index.php?page=pendingplayers\">";
  echo "<INPUT TYPE=hidden NAME=player_id VALUE=$playerId>";
  echo "<INPUT TYPE=hidden NAME=step VALUE=\"$stepName\">";
  if ($reasonType) {
    echo "<INPUT TYPE=hidden NAME=reasontype VALUE=\"$reasonType\">";
  }
  echo "<INPUT TYPE=submit VALUE=\"$text\"><br><br>";
  echo "</FORM></TD>";
  echo "</TR>";
}

$stm = $db->query("SELECT * FROM newplayers ORDER BY id");

foreach ($stm->fetchAll() as $applicationInfo) {
  $playerId = $applicationInfo->id;
  
  try {

    $pendingPlayer = Player::loadById($playerId);

    $stm = $db->prepare("SELECT name FROM languages where id = :playerId LIMIT 1");
    
    $stm->bindInt("playerId", $pendingPlayer->getLanguage());
    
    $lang = ucfirst($stm->executeScalar());

    echo "<TR>";
    
    echo "<TD WIDTH=350><B>" . $pendingPlayer->getFullNameWithId() . " ({$pendingPlayer->getBirthYear()}, {$pendingPlayer->getCountry()})</B> [$lang]";
    if ($applicationInfo->type == PlayersDeptConstants::NEWPLAYER_TYPE_REACTIVATION) {
      echo " - <span style=\"color:red;\">Reactivation request</span>";
    }
    echo "</TD>";
    echo "<TD WIDTH=350 class='email-{$pendingPlayer->getId()}'>{$pendingPlayer->getEmail()}</TD>";
    echo "</TR>";
    echo "<TR>";
    echo "<TD COLSPAN=2>";
    preg_match("/\d+\.\d+\.\d+\.\d+(\.\d+\.\d+)?/", $pendingPlayer->getLastLoginString(), $matches);
    echo "<BR>IP information: {$pendingPlayer->getLastLoginString()}
      <a href='http://whatismyipaddress.com/ip/{$matches[0]}' target='_blank'>[wimia]</a><BR><BR>";
    $research = str_replace("#S#", "$s", $applicationInfo->research);
    echo "<PRE>$research</PRE><BR>";
    echo "<BR>Given reference: $applicationInfo->reference<BR>";
    echo "<br>Referrer URL: $applicationInfo->referrer<br>";
    echo "<BR>Given comments:  $applicationInfo->comment<BR>";

    // show character names, useful especially for reactivated players
    $stm = $db->prepare("SELECT name FROM chars WHERE player = :playerId ORDER BY id");
    $stm->bindInt("playerId", $pendingPlayer->getId());
    $stm->execute();
    $characterNames = $stm->fetchScalars();
    if ($characterNames) {
      $characterNames = implode(", ", $characterNames);
      echo "<br> Character names: " . htmlspecialchars($characterNames) . "<br>";
    }

    if (Validation::isPositiveInt($applicationInfo->refplayer)) {
      $stm = $db->prepare("SELECT CONCAT(username, ' ', email) FROM players WHERE id = :playerId");
      $stm->bindInt("playerId", $applicationInfo->refplayer);
      $refPlayerData = $stm->executeScalar();
      $refPlayerData .= " (<a href=\"index.php?page=listplayers&player_id="
        . $applicationInfo->refplayer . "&data=yes&set=player\">$applicationInfo->refplayer</a>)";
      $applicationInfo->refplayer = $refPlayerData;
      echo "<BR>Referring player: $applicationInfo->refplayer<BR><BR>";
    } elseif ($applicationInfo->refplayer) {
      echo "<BR>Referring player: $applicationInfo->refplayer (Unknown player)<BR><BR>";
    } else {
      echo "<BR>";
    }

    echo "</TD>";
    echo "</TR>";

    if ($applicationInfo->type == PlayersDeptConstants::NEWPLAYER_TYPE_REGISTRATION) {
      displayDecisionForApplication($pendingPlayer->getId(), "Accept above player", "accept");
      displayDecisionForApplication($pendingPlayer->getId(), "Refuse for double account", "refuse", PlayerConstants::REFUSAL_DOUBLE_ACCOUNT);
      displayDecisionForApplication($pendingPlayer->getId(), "Refuse for double application", "refuse", PlayerConstants::REFUSAL_DOUBLE_APPLICATION);
      displayDecisionForApplication($pendingPlayer->getId(), "Refuse for proxy", "refuse", PlayerConstants::REFUSAL_PROXY);

      echo "<TR VALIGN=top>";
      echo "<TD><FORM METHOD=post ACTION=\"index.php?page=pendingplayers\">";
      echo "<INPUT TYPE=hidden NAME=player_id VALUE={$pendingPlayer->getId()}>";
      echo "<INPUT TYPE=hidden NAME=step VALUE=\"refuse\">";
      echo "<INPUT TYPE=hidden NAME=reasontype VALUE=" . PlayerConstants::REFUSAL_REACTIVATED_OTHER . ">";
      echo "<INPUT TYPE=submit class=\"submitReactivation\" VALUE=\"Refuse and reactivate account\">";
      echo "<input type=text placeholder='ID to reactivate' class='accountToReactivate' NAME=reason size='11'>";
      echo "</FORM></TD>";
      echo "</TR><TR VALIGN=top>";
      echo "<TD><FORM METHOD=post ACTION=\"index.php?page=pendingplayers\" class='customRefusalForm'>";
      echo "<INPUT TYPE=hidden NAME=player_id VALUE={$pendingPlayer->getId()}>";
      echo "<INPUT TYPE=hidden NAME=step VALUE=\"refuse\">";
      echo "<INPUT TYPE=hidden NAME=reasontype VALUE=" . PlayerConstants::REFUSAL_CUSTOM . ">";
      echo "<INPUT TYPE=submit VALUE=\"Refuse above player\">";
      echo "<BR><BR>Reason for refusing above player:<BR>";
      echo "<TEXTAREA NAME=reason COLS=30 ROWS=5></TEXTAREA>";
      echo "</FORM></TD>";
      echo "</TR>";
    } else {
      displayDecisionForApplication($pendingPlayer->getId(), "Accept above player", "reactivate");

      echo "<TR VALIGN=top>";
      echo "<TD><FORM METHOD=post ACTION=\"index.php?page=pendingplayers\" class='customRefusalForm'>";
      echo "<INPUT TYPE=hidden NAME=player_id VALUE={$pendingPlayer->getId()}>";
      echo "<INPUT TYPE=hidden NAME=step VALUE=\"reactivate_refuse\">";
      echo "<INPUT TYPE=hidden NAME=reasontype VALUE=" . PlayerConstants::REFUSAL_CUSTOM . ">";
      echo "<INPUT TYPE=submit VALUE=\"Refuse above player\">";
      echo "<BR><BR>Reason for refusing above player:<BR>";
      echo "<TEXTAREA NAME=reason COLS=30 ROWS=5></TEXTAREA>";
      echo "</FORM></TD>";
      echo "</TR>";
    }
  } catch (InvalidArgumentException $e) {
    echo "<tr><td><hr>Player #{$applicationInfo->id} Broken application</td></tr>";
  }
}

echo "</TABLE>";
echo "</div>";
echo "<div class='centered'>";
echo "<BR><A HREF=\"index.php?page=listplayers\">Manage database of players</A>";
echo "<BR><A HREF=\"index.php?page=player\">Back to player page</A>";
echo "</div>";

echo "<BR><BR>";
show_title("Players Department History of the Day");

echo "<div class='page' style='width:95%'>";
echo "<ul class='plain'>";

$stm = $db->query("SELECT * FROM players_report");
foreach ($stm->fetchAll() as $report) {
  echo "<li>$report->contents</li>";
}

echo "</ul>";
echo "</div>\n";

$javaScriptCode = <<<MYHEREDOC
<script type="text/javascript">
  $(function() {
    $(".submitReactivation").click(function(event) {
      event.preventDefault();
      var clickedForm = $(event.target).closest("form");
      var playerId = clickedForm.find(".accountToReactivate").val();

      $.ajax({
        dataType: "json",
        "type": "POST",
        cache: false,
        url: "ajax.tools.php",
        data: {
          page: "playerinfo",
          player_id: playerId
        },
        success: function(ret) {
          if (isError(ret)) {
            return;
          }

          var appId = clickedForm.find("[name='player_id']").val();
          var appEmail = $(".email-" + appId).text();
          console.log(ret);
          if ([1, 2, 3].indexOf(+ret.status) != -1) {
            alert("Account " + ret.id + " is already active!");
            return;
          }

          if (confirm("Do you want to refuse the application and reactivate account " + ret.firstName + " " + ret.lastName + " [" + ret.id + "]?\\n" +
              "Account's email will also be set to " + appEmail + " if necessary.")) {
            clickedForm.submit();
          }
        }
      });
    });
  });
</script>

MYHEREDOC;

echo $javaScriptCode;
