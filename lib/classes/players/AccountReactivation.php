<?php

class AccountReactivation
{

  /** @var Player */
  private $adminPlayer;
  private $domainAddress;
  /** @var Db */
  private $db;

  public function __construct(Player $adminPlayer, $domainAddress)
  {
    $this->adminPlayer = $adminPlayer;
    $this->domainAddress = $domainAddress;
    $this->db = Db::get();
  }

  public function accept(Player $acceptedPlayer)
  {
    $stm = $this->db->prepare("UPDATE players SET status = :active WHERE id = :id");
    $stm->bindInt("active", PlayerConstants::ACTIVE);
    $stm->bindInt("id", $acceptedPlayer->getId());
    $stm->execute();

    Limitations::delLims($acceptedPlayer->getId(), Limitations::TYPE_NEW_CHARACTERS);
    $tag = new Tag();
    $tag->language = $acceptedPlayer->getLanguage();
    $tag->content = "<CANTR REPLACE NAME=message_reactivation_approved" .
      " NAME=" . urlencode($acceptedPlayer->getFullName()) .
      " ACCOUNT_ID=" . urlencode($acceptedPlayer->getId()) .
      " MAIN_PAGE_LINK=" . urlencode($this->domainAddress) .
      " PASSWORD_PAGE_LINK=" . urlencode($this->domainAddress . "/index.php?page=passreminder") .
      " ADMIN=" . urlencode($this->adminPlayer->getFullName()) . ">";
    $message = $tag->interpret();
    $tag->content = "<CANTR REPLACE NAME=message_reactivation_approved_subject>";
    $subject = $tag->interpret();

    $reactivationMailService = new MailService("Cantr Accounts", $GLOBALS['emailPlayers']);
    $reactivationMailService->send($acceptedPlayer->getEmail(), $subject, $message);

    $this->reportToDailyReport($acceptedPlayer, $this->adminPlayer, true);
  }

  public function reject(Player $rejectedPlayer, $reason)
  {
    $stm = $this->db->prepare("UPDATE players SET status = :locked WHERE id = :id");
    $stm->bindInt("locked", PlayerConstants::LOCKED);
    $stm->bindInt("id", $rejectedPlayer->getId());
    $stm->execute();
    $tag = new Tag();
    $tag->language = $rejectedPlayer->getLanguage();
    $tag->content = "<CANTR REPLACE NAME=message_reactivation_refused" .
      " NAME=" . urlencode($rejectedPlayer->getFullName()) .
      " REASON=" . urlencode($reason) .
      " ACCOUNT_ID=" . urlencode($rejectedPlayer->getId()) .
      " ADMIN=" . urlencode($this->adminPlayer->getFullName()) . ">";
    $message = $tag->interpret();
    $tag->content = "<CANTR REPLACE NAME=message_reactivation_refused_subject>";
    $subject = $tag->interpret();

    $reactivationMailService = new MailService("Cantr Accounts", $GLOBALS['emailPlayers']);
    $reactivationMailService->send($rejectedPlayer->getEmail(), $subject, $message);

    $this->reportToDailyReport($rejectedPlayer, $this->adminPlayer, false);
  }

  private function reportToDailyReport(Player $reviewedPlayer, Player $admin, $isAccepted)
  {
    $message = $admin->getFullName() . " " . ($isAccepted ? "accepted " : "refused");
    $message .= " reactivation request of player {$reviewedPlayer->getFullNameWithId()} ";
    $message .= "[{$reviewedPlayer->getEmail()}]";

    Report::saveInPlayerReport($message);
  }
}