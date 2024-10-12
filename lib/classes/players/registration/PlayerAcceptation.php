<?php

/**
 * Responsible for acceptation and rejection of new players
 */
class PlayerAcceptation
{
  protected $admin;
  /** @var Db */
  private $db;

  public function __construct(Player $admin)
  {
    $this->admin = $admin;
    $this->db = Db::get();
  }

  final public function accept(Player $acceptedPlayer, $application)
  {
    $tag = TagBuilder::forTag("player_account_accepted_subject")
      ->language($acceptedPlayer->getLanguage())->build();
    $subject = $tag->interpret();

    $tag = TagBuilder::forText(
      "<CANTR REPLACE NAME=player_account_accepted_message ADMIN=" .
      urlencode($this->admin->getFullName()) . " NAME=" . urlencode($acceptedPlayer->getFirstName()) .
      " ID={$acceptedPlayer->getId()} USERNAME=" . urlencode($acceptedPlayer->getUserName()) .
      " LOGINPAGEURL=https://cantr.net/index.php?page%3Dlogin&l%3D{$acceptedPlayer->getLanguage()}>"
    )->language($acceptedPlayer->getLanguage())->build(); // TODO fix link for instant login
    $message = $tag->interpret();

    $stm = $this->db->prepare("UPDATE players SET status = :approved WHERE id = :id");
    $stm->bindInt("approved", PlayerConstants::APPROVED);
    $stm->bindInt("id", $application->id);
    $stm->execute();

    $mailService = new MailService("Cantr Players Department", $GLOBALS['emailPlayers']);
    $mailService->send($acceptedPlayer->getEmail(), $subject, $message);

    $messageManager = new MessageManager(Db::get());
    $messageManager->sendMessage(MessageManager::PQUEUE_SYSTEM_MESSAGE, $acceptedPlayer->getId(), "<CANTR REPLACE NAME=message_player_accepted>", 1);

    $this->reportAcceptation($acceptedPlayer, $this->admin);
  }

  final public function reject(Player $rejectedPlayer, $application, $reasonType, $reason)
  {
    $this->reportRefusal($rejectedPlayer, $application, $this->admin, $reasonType, $reason);

    $env = Request::getInstance()->getEnvironment();
    if ($env->introExists()) {
      $introDb = $env->getDbNameFor("intro");
      $this->removeCharactersFromIntro($rejectedPlayer, $introDb);
    }

    $this->addToRejected($rejectedPlayer, $reasonType, $reason);
    if ($reasonType == PlayerConstants::REFUSAL_REACTIVATED_OTHER) {
      $this->reactivateOtherAccount($rejectedPlayer, intval($reason));
    }
  }

  private function removeCharactersFromIntro(Player $rejectedPlayer, $introDb)
  {
    $stm = $this->db->prepareWithIntList("SELECT id, location FROM `$introDb`.`chars`
      WHERE player = :playerId AND status IN (:statuses)", [
       "statuses" => [CharacterConstants::CHAR_PENDING, CharacterConstants::CHAR_ACTIVE],
    ]);
    $stm->bindInt("playerId", $rejectedPlayer->getId());
    $stm->execute();
    foreach ($stm->fetchAll() as $charToRemove) {
      $this->removeCharacterFromIntro($introDb, $charToRemove);
    }
  }

  private function removeCharacterFromIntro($introDb, $charToRemove)
  {
    $stm = $this->db->prepare("UPDATE `$introDb`.`objects` SET location = :locationId, person = 0
        WHERE person = :charId"); // TODO should be replaced by CharacterDeath::drop_everything_unweared, but now it's impossible to use a different db
    $stm->bindInt("locationId", $charToRemove->location);
    $stm->bindInt("charId", $charToRemove->id);
    $stm->execute();

    $stm = $this->db->prepare("UPDATE `$introDb`.`chars` SET status = :deceased WHERE id = :charId");
    $stm->bindInt("deceased", CharacterConstants::CHAR_DECEASED);
    $stm->bindInt("charId", $charToRemove->id);
    $stm->execute();
  }


  private function addToRejected(Player $rejectedPlayer, $reasonType, $reason)
  {
    $refusalReason = "REFUSED - " . PlayerConstants::$REFUSAL_REASONS[$reasonType];
    if ($reason) {
      $refusalReason .= ": $reason";
    }

    $stm = $this->db->prepare("UPDATE players SET status = :refused, notes = CONCAT(:refusalReason, COALESCE(notes, '')) WHERE id = :playerId");
    $stm->bindInt("refused", PlayerConstants::REFUSED);
    $stm->bindStr("refusalReason", "$refusalReason\n");
    $stm->bindInt("playerId", $rejectedPlayer->getId());
    $stm->execute();

    $stm = $this->db->prepare("DELETE events_obs.* FROM events_obs, chars WHERE events_obs.observer = chars.id and chars.player = :playerId");
    $stm->bindInt("playerId", $rejectedPlayer->getId());
    $stm->execute();
    $stm = $this->db->prepare("DELETE events_view.* FROM events_view,chars WHERE events_view.observer = chars.id and chars.player = :playerId");
    $stm->bindInt("playerId", $rejectedPlayer->getId());
    $stm->execute();
    $stm = $this->db->prepare("DELETE FROM chars WHERE player = :playerId");
    $stm->bindInt("playerId", $rejectedPlayer->getId());
    $stm->execute();
  }

  private function reportAcceptation(Player $acceptedPlayer, Player $acceptor)
  {
    $this->reportToDailyReport($acceptedPlayer, $acceptor, true);

    Report::saveInPcStatistics("psubscribed", $acceptedPlayer->getId());

    preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $acceptedPlayer->getLastLoginString(), $ip);
    $stm = $this->db->prepare("INSERT INTO ips (player, ip, lasttime) VALUES (:playerId, :ip, NOW())");
    $stm->bindInt("playerId", $acceptedPlayer->getId());
    $stm->bindStr("ip", $ip[0]);
    $stm->execute();
  }

  private function reportFatalError($reasonType, $reason, $application)
  {
    $message = "A fatal error has occured. Tried to set an invalid reason for account refusal.<br>\n";
    $message .= "Player id: " . $application->id . "<br>\n";
    $message .= "GET: " . print_r($_GET, true) . "<br>\n";
    $message .= "POST: " . print_r($_POST, true) . "<br>\n";
    $message .= "reason: $reasonType, " . var_export($reason, true) . "<br>\n";
    $message .= "Please contact with ProgD.<br>\n";

    $refusalErrorService = new MailService("Cantr Accounts", $GLOBALS['emailProgramming']);
    $refusalErrorService->send($GLOBALS['emailProgramming'], "ACCOUNT REFUSAL ERROR", $message);

    die($message);
  }

  private function reportRefusal(Player $refusedPlayer, $application, Player $refusor, $reasonType, $reason = "")
  {
    $tag = new tag;
    $tag->language = $refusedPlayer->getLanguage();

    if ($reasonType == PlayerConstants::REFUSAL_DOUBLE_ACCOUNT) {
      $tag->content = "<CANTR REPLACE NAME=message_refused_double_account NAME=" . urlencode($refusedPlayer->getFirstName()) . " ADMIN=" . urlencode($refusor->getFullName()) . ">";
    } elseif ($reasonType == PlayerConstants::REFUSAL_DOUBLE_APPLICATION) {
      $tag->content = "<CANTR REPLACE NAME=message_refused_double_application NAME=" . urlencode($refusedPlayer->getFirstName()) . " ADMIN=" . urlencode($refusor->getFullName()) . ">";
    } elseif ($reasonType == PlayerConstants::REFUSAL_PROXY) {
      $tag->content = "<CANTR REPLACE NAME=message_refused_proxy NAME=" . urlencode($refusedPlayer->getFirstName()) . " ADMIN=" . urlencode($refusor->getFullName()) . ">";
    } elseif ($reasonType == PlayerConstants::REFUSAL_REACTIVATED_OTHER) {
      $revivedPlayerId = intval($reason);
      $revivedAccountEmail = Player::loadById($revivedPlayerId)->getEmail();
      $tag->content = "<CANTR REPLACE NAME=message_other_account_reactivated NAME=" . urlencode($refusedPlayer->getFirstName()) .
        " ADMIN=" . urlencode($refusor->getFullName()) . " ACCOUNT_ID=" . urlencode($reason) . " EMAIL=" . urlencode($revivedAccountEmail) . ">";
    } elseif ($reasonType == PlayerConstants::REFUSAL_CUSTOM) {
      if (!$reason) {
        $this->reportFatalError($reasonType, $reason, $application);
      }

      $tag->content = "<CANTR REPLACE NAME=message_refused_any_reason NAME=" . urlencode($refusedPlayer->getFirstName()) . " ADMIN=" . urlencode($refusor->getFullName()) . " REASON=" . urlencode($reason) . ">";
    } else {
      $this->reportFatalError($reasonType, $reason, $application);
    }

    $message = $tag->interpret();

    $tag->content = "<CANTR REPLACE NAME=message_refused_subject>";
    if ($reasonType == PlayerConstants::REFUSAL_REACTIVATED_OTHER) {
      $tag->content = "<CANTR REPLACE NAME=message_other_account_reactivated_subject ACCOUNT_ID=" . intval($reason) . ">";
    }
    $subject = $tag->interpret();

    $refusalMailService = new MailService("Cantr Accounts", $GLOBALS['emailPlayers']);
    $refusalMailService->send($refusedPlayer->getEmail(), $subject, $message);


    $message = "The following account was refused by " . $refusor->getFullName() . ":\n\n";
    $message .= "Name: {$refusedPlayer->getFullName()}}\n";
    $message .= "Email: {$refusedPlayer->getEmail()}\n";
    $message .= "Info: {$refusedPlayer->getBirthYear()}, {$refusedPlayer->getCountry()}\n";
    $message .= "IP: {$refusedPlayer->getLastLoginString()} \n";
    $message .= "Reason type: " . PlayerConstants::$REFUSAL_REASONS[$reasonType] . "\n";
    $message .= "Reason given: $reason\n\n";
    $message .= "Research info:\n\n$application->research\n\n";
    $message .= "\n\n(This is an automatically generated message)";

    $toMailingList = new MailService($refusor->getFullName(), $GLOBALS['emailPlayers']);
    $toMailingList->sendPlaintext($GLOBALS['emailPlayers'], "Player account refused", $message);

    $this->reportToDailyReport($refusedPlayer, $refusor, false);

    Report::saveInPcStatistics("prefused", $refusedPlayer->getId());
  }

  private function reportToDailyReport(Player $reviewedPlayer, Player $admin, $isAccepted)
  {
    $message = $admin->getFullName() . " " . ($isAccepted ? "accepted " : "refused");
    $message .= " player {$reviewedPlayer->getFullNameWithId()} [{$reviewedPlayer->getEmail()}]";

    Report::saveInPlayerReport($message);
  }

  private function reactivateOtherAccount(Player $refusedAccount, $accountIdToRevive)
  {
    $stm = $this->db->prepare("SELECT status FROM players WHERE id = :playerId");
    $stm->bindInt("playerId", $accountIdToRevive);
    $previousStatus = $stm->executeScalar();

    $gameDate = GameDate::NOW();
    $stm = $this->db->prepare("UPDATE players SET status = :status, lastdate = :lastDate, lasttime = :lastTime WHERE id = :playerId");
    $stm->bindInt("status", PlayerConstants::ACTIVE);
    $stm->bindInt("lastDate", $gameDate->getDay());
    $stm->bindInt("lastTime", $gameDate->getHour());
    $stm->bindInt("playerId", $accountIdToRevive);
    $stm->execute();
    $accountToRevive = Player::loadById($accountIdToRevive);

    $message = $this->admin->getFullName() . " revived player " . $accountToRevive->getFullNameWithId() .
      " (email: " . $accountToRevive->getEmail() . ")";

    if (!in_array($previousStatus, [PlayerConstants::ACTIVE, PlayerConstants::APPROVED]) &&
      $refusedAccount->getEmail() != $accountToRevive->getEmail()
    ) {
      $message .= " and changed email to " . $refusedAccount->getEmail();
      $stm = $this->db->prepare("UPDATE players SET email = :email WHERE id = :playerId");
      $stm->bindStr("email", $refusedAccount->getEmail());
      $stm->bindInt("playerId", $accountToRevive->getId());
      $stm->execute();
    }

    Report::saveInPlayerReport($message);
  }
}
