<?Php
/**
 * End of turn script. Manages purging of old events, objects, reports etc.
 *
 */

$page = "server.turn.finish";
include "server.header.inc.php";
import_lib("func.expireobject.inc.php");

$nlimit = 10000;

$db = Db::get();
$gameDate = GameDate::NOW();

$k = 0;
do {
  $stm = $db->prepare("DELETE FROM `events` WHERE (hour <= :currentHour AND day < :sixDaysAgo) OR day < :sevenDaysAgo LIMIT :limit");
  $stm->bindInt("sixDaysAgo", $gameDate->getDay() - _EXPFTHBCK);
  $stm->bindInt("sevenDaysAgo", $gameDate->getDay() - _EXPFTHBCK - 1);
  $stm->bindInt("currentHour", $gameDate->getHour());
  $stm->bindInt("limit", $nlimit);
  $stm->execute();
  $k++;
} while ($k < 10 && $stm->rowCount() > 0);

purge_expired_objects($gameDate, $db);

$stm = $db->prepare("SELECT MIN(id) FROM `events`");
$minimumEventId = $stm->executeScalar();

$k = 0;
do {
  $stm = $db->prepare("DELETE FROM events_obs WHERE events_obs.event < :eventId LIMIT :limit");
  $stm->bindInt("eventId", $minimumEventId);
  $stm->bindInt("limit", $nlimit);
  $stm->execute();
  $k++;
} while ($k < 10 && $stm->rowCount() > 0);

$env = Request::getInstance()->getEnvironment();
if ($gameDate->getHour() == 1) {
  // Character activity memory is moved
  $db->query("UPDATE players SET recent_activity = recent_activity - 32768 WHERE recent_activity >= 32768");
  $db->query("UPDATE players SET recent_activity = recent_activity << 1");
} else {
  if ($env->is("main") && $gameDate->getHour() == 2) {
    // Violence report is only sent once a day
    include "server/server.violencereport.inc.php";
  }
}

$stm = $db->prepare("DELETE FROM violence WHERE turn < :yesterday_1 OR (turn = :yesterday_2 AND turnpart <= :currentHour)");
$stm->bindInt("yesterday_1", $gameDate->getDay() - 1);
$stm->bindInt("yesterday_2", $gameDate->getDay() - 1);
$stm->bindInt("currentHour", $gameDate->getHour());
$stm->execute();

$expirationTimestamp = time() - Session::SESSION_EXPIRATION_SECONDS;
Session::deleteExpiredSessions($db, $expirationTimestamp);

// General reports etc
if ($env->is("main") && $gameDate->getHour() == 2) {

  $message = "";
  $stm = $db->query("SELECT contents FROM players_report");
  foreach ($stm->fetchAll() as $report) {
    $message .= "$report->contents\n";
  }

  $mailService = new MailService("Cantr Players Department", $GLOBALS['emailPlayers']);
  $mailService->sendPlaintext($GLOBALS['emailPlayers'], "Players Department Daily Report (" . _ENV . ")", $message);
  $db->query("TRUNCATE TABLE players_report");

  $stm = $db->query("SELECT * FROM reports");
  foreach ($stm->fetchAll() as $report_info) {
    if ((!empty($report_info->contents)) or ($report_info->name == "goodspassing")) {
      if ($report_info->name == "goodspassing") {
        $report_info->contents = CooperationUtil::getGoodsPassingReport($report_info->contents, $db);
      } elseif ($report_info->name == "ooc_chatter") {

        $messages = explode("\n", $report_info->contents);
        $messages = CooperationUtil::groupMessagesByCharId($messages);

        $report_info->contents = implode("\n----------------\n", $messages);

      } elseif ($report_info->name == "votinglinks") {
        $report_info->contents = htmlspecialchars_decode($report_info->contents);
      }

      $report_info->title .= " :  " . $gameDate->getDay() . "-" . $gameDate->getHour();

      $mailService = new MailService("Cantr Players Department", $GLOBALS['emailPlayers']);
      $mailService->sendPlaintext($report_info->email, $report_info->title . " (" . _ENV . ")", $report_info->contents);
    }
  }

  $db->query("UPDATE reports SET contents=''");

  // Cantr Explorer log data
  $report = "Cantr Explorer usage\n";
  $stm = $db->prepare("SELECT COUNT(*) FROM ceLog");
  $LocCount = $stm->executeScalar();
  if ($LocCount > 0) {
    $stm = $db->query("SELECT * FROM ceLog ORDER BY player, accesstime");
    $lastplayer = 0;
    foreach ($stm->fetchAll() as $X) {
      if ($lastplayer != $X->player) {
        $lastplayer = $X->player;
        $stm = $db->prepare("SELECT CONCAT(firstname, ' ', lastname) FROM players WHERE id = :player");
        $stm->bindInt("player", $lastplayer);
        $playerName = $stm->executeScalar();

        $report .= "\n$playerName ($lastplayer):\n";
      }
      if ($X->description) {
        $report .= "  $X->accesstime - $X->description\n";
      } else {
        $report .= "  $X->accesstime - $X->params\n";
      }
    }
    $db->query("DELETE FROM ceLog");

    $title = "Cantr Explorer usage :  " . $gameDate->getDay() . "-" . $gameDate->getHour();
    $mailService = new MailService("Cantr Players Department", $GLOBALS['emailPlayers']);
    $mailService->sendPlaintext($GLOBALS['emailGAB'], $title . " (" . _ENV . ")", $report);
  } else {
    $report .= "No activity notified.\n";
  }

  $count_newplayers = 0;

  $message = "";
  $stm = $db->query("SELECT * FROM advert_report ORDER BY id");
  foreach ($stm->fetchAll() as $report) {
    $count_newplayers++;
    $message .= "$report->register $report->name ($report->id): $report->reference (referrer:$report->referrer) (lang:$report->language, country:$report->country)\n";
  }

  $db->query("DELETE FROM advert_report");

  $message .= "\nTotal number of new players: $count_newplayers\n";

  $mailService = new MailService("Cantr Advertisements", $GLOBALS['emailMarketing']);
  $mailService->sendPlaintext($GLOBALS['emailMarketing'], "Advertisement Daily Report (" . _ENV . ")", $message);
}
// The below part should only be done once a day
if ($gameDate->getHour() == 1) {

  $stm = $db->prepare("DELETE FROM unreported_turns WHERE turnnumber < :sixDaysAgo");
  $stm->bindInt("sixDaysAgo", $gameDate->getDay() - _EXPFTHBCK);
  $stm->execute();
  // ***** EXPIRED UNVALIDATED E-MAILS ********
  $db->query("DELETE FROM unvalidated_email WHERE TO_DAYS(NOW()) - TO_DAYS(expires) > 0");

  $stm = $db->prepare("DELETE FROM timing WHERE day < :sixDaysAgo");
  $stm->bindInt("sixDaysAgo", $gameDate->getDay() - _EXPTIMING);
  $stm->execute();

  // *** MAKE AN ENTRY IN THE UNREPORTED TURNS TABLE FOR EVERY PLAYER
  $stm = $db->prepare("INSERT INTO unreported_turns (player, turnnumber) SELECT id, :yesterday FROM players");
  $stm->bindInt("yesterday", $gameDate->getDay() - 1);
  $stm->execute();
}

include "../lib/server/server.footer.inc.php";
