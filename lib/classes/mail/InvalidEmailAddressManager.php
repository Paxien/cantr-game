<?php

class InvalidEmailAddressManager
{

  const EXPIRATION_DAYS = 3;
  const SEC_PER_DAY = 60 * 60 * 24;

  /** @var int */
  private $playerId;
  /** @var Db */
  private $db;

  public function __construct($playerId)
  {
    $this->playerId = $playerId;
    $this->db = Db::get();
  }

  public function hasValidEmail()
  {
    $playerInfo = Player::loadById($this->playerId);
    $stm = $this->db->prepare("SELECT count FROM undelivered_emails WHERE email = :email");
    $stm->bindStr("email", $playerInfo->getEmail());
    $undeliveredEmails = $stm->executeScalar();
    if ($undeliveredEmails > 1) {
      // staff members have access to mailing lists (which are sometimes eaten), so are excluded from the validation
      $stm = $this->db->prepare("SELECT COUNT(*)
        FROM assignments a
        INNER JOIN players p ON p.id = a.player WHERE p.email = :email");
      $stm->bindStr("email", $playerInfo->getEmail());
      $isStaffMember = $stm->executeScalar();
      return $isStaffMember > 0;
    }
    return true;
  }

  public function sendConfirmationEmail()
  {
    $playerInfo = Player::loadById($this->playerId);
    $confirmationMailService = new MailService("Cantr Support", $GLOBALS['emailSupport']);
    $confirmationMailService->setUndeliveredEmailCheck(false);
    $confirmationMailService->send($playerInfo->getEmail(), TagBuilder::forTag("mail_title_email_confirmation")->build()->interpret(),
      TagBuilder::forText("<CANTR REPLACE NAME=mail_text_email_confirmation CONFIRMATION_LINK=" .
        urlencode($this->createConfirmationLink($playerInfo)) . ">")->build()->interpret());
  }

  private function createConfirmationLink(Player $playerInfo)
  {
    $currentTimestamp = time();
    $hashablePlayerInfo = $this->hashablePlayerSpecificString($playerInfo->getId(), $playerInfo->getEmail(), $currentTimestamp);
    $hash = SecurityUtil::generatePasswordHash($hashablePlayerInfo);
    return "https://cantr.net/index.php?page=activate_invalid_email&id=" .
      $playerInfo->getId() . "&timestamp=" . $currentTimestamp . "&hash=" . $hash;
  }

  public function canValidateEmail($hash, $timestamp)
  {
    $currentTimestamp = time();
    $expirationTimestamp = $timestamp + self::SEC_PER_DAY * self::EXPIRATION_DAYS;
    if ($currentTimestamp > $expirationTimestamp) {
      return false;
    }
    $playerInfo = Player::loadById($this->playerId);
    $hashablePlayerInfo = $this->hashablePlayerSpecificString($playerInfo->getId(), $playerInfo->getEmail(), $timestamp);
    return SecurityUtil::verifyPassword($hashablePlayerInfo, $hash);
  }

  public function makeEmailValid()
  {
    $playerInfo = Player::loadById($this->playerId);
    $stm = $this->db->prepare("DELETE FROM undelivered_emails WHERE email = :email");
    $stm->bindStr("email", $playerInfo->getEmail());
    $stm->execute();

    $emailConfirmedStat = new Statistic("email_confirmed", Db::get());
    $emailConfirmedStat->store(substr($playerInfo->getEmail(), 0, 31), $this->playerId);
  }

  /**
   * @param $playerId int used to create hash
   * @param $playerEmail string used to create hash
   * @param $timestamp int timestamp at the moment of generating the hash. It's to be able to reject expired confirmation links.
   * @return string to be hashed
   */
  private static function hashablePlayerSpecificString($playerId, $playerEmail, $timestamp)
  {
    return strval($playerId) . $playerEmail . strval($timestamp) . "p23kMk2kOKq";
  }
}