<?php

class user
{

  var $id;
  var $username;
  var $firstname;
  var $lastname;
  var $email;
  var $password;
  var $password_retype;
  var $passwordHash;
  var $country;
  var $age;
  var $onleave;
  var $timeleft;
  var $reference;
  var $comment;
  var $charname1;
  var $sex1;
  var $language;
  var $refplayer;
  var $referrer;
  var $credits;
  var $admin;
  var $error;
  var $ipinfo;

  var $refPlayerId;
  const PASSWORD_MIN_LENGTH = 6;
  /** @var Db */
  private $db;

  public function __construct()
  {
    $this->db = Db::get();
  }

  /**
   * Validate a new user object. Called from the registration process
   */
  function validate()
  {

    $accepted = true;

    if ($accepted) {
      $accepted = $this->validate_char($this->charname1, $this->sex1);
    }

    if ($accepted) {
      //let validate user name
      if (strlen($this->username) < 4) {
        $this->error = "<CANTR REPLACE NAME=error_username_too_short>";
        $accepted = false;
      } elseif (!preg_match("/^[a-zA-Z].{3,}/", $this->username)) {
        $this->error = "<CANTR REPLACE NAME=error_username_first_char>";
        $accepted = false;
      } else {
        $stm = $this->db->prepare("SELECT COUNT(*) FROM players WHERE username LIKE :username");
        $stm->bindStr("username", $this->username);
        $exist = $stm->executeScalar() > 0;
        if ($exist) {
          $this->error = "<CANTR REPLACE NAME=error_username_exist>";
          $accepted = false;
        }
      }
    }

    if ($accepted AND ($this->firstname == '' OR $this->lastname == '')) {
      $this->error = "<CANTR REPLACE NAME=error_no_name>";
      $accepted = false;
    }

    if ($this->age == 0) {
      $this->error = "<CANTR REPLACE NAME=error_no_year_of_birth>";
      $accepted = false;
    }

    if ($accepted and !$this->check_email()) {
      $this->error = "<CANTR REPLACE NAME=error_no_email>";
      $accepted = false;
    }

    if ($accepted and mb_strlen($this->password) < self::PASSWORD_MIN_LENGTH) {
      $this->error = "<CANTR REPLACE NAME=error_no_password>";
      $accepted = false;
    }

    if ($accepted and $this->password != $this->password_retype) {
      $this->error = "<CANTR REPLACE NAME=error_different_passwords>";
      $accepted = false;
    }

    $stm = $this->db->prepare("SELECT COUNT(*) FROM players WHERE email = :email AND status <= :locked");
    $stm->bindStr("email", $this->email);
    $stm->bindInt("locked", PlayerConstants::LOCKED);
    $count_emails = $stm->executeScalar();
    if ($accepted and $count_emails > 0) {
      $email = urlencode($this->email);
      $this->error = "<CANTR REPLACE NAME=error_email_exists EMAIL=$email>";
      $accepted = false;
    }

    $this->reportValidationProcess($accepted);

    return $accepted;
  }

  private function reportValidationProcess($accepted)
  {
    $stm = $this->db->prepare("INSERT INTO registration_attempts (firstname, lastname, errors, success)
      VALUES (:firstname, :lastname, :errors, :success)");
    $stm->bindStr("firstname", $this->firstname);
    $stm->bindStr("lastname", $this->lastname);
    $stm->bindStr("errors", $this->error, true);
    $stm->bindInt("success", ($accepted ? 1 : 0));
    $stm->execute();
  }

  /**
   * Validate that the email address is of correct format
   */
  function check_email()
  {
    $accepted = Validation::isEmailValid($this->email);

    list($username, $domain) = explode("@", $this->email);

    if (!checkdnsrr($domain, "ANY")) {
      Logger::getLogger(__CLASS__)->warn("It would have failed for email '$this->email' if we kept DNS check");
    }

    return $accepted;
  }

  /**
   * Add a player to the newplayers database for approval. Check various columns and
   * tables for matching name, ip, password and email addresses.
   */
  public function add($playerId)
  {
    $refPlayerId = self::parseReferringPlayer($this->refplayer, $this->db);
    if ($refPlayerId > 0) {
      $this->refplayer = $refPlayerId;
    } elseif (!empty($this->refplayer)) {
      $this->refplayer .= " (Unknown player)";
      $unmatchedRefPlayerStatistic = new Statistic("unmatched_refplayer", $this->db);
      $unmatchedRefPlayerStatistic->store(substr($this->refplayer, 0, 31), $playerId);
    }

    $this->refPlayerId = intval($refPlayerId);

    $stm = $this->db->prepare("INSERT INTO newplayers (id, reference,comment,refplayer,cedata,type)
      VALUES (:id, :reference, :comment, :refplayer, '', :type)");
    $stm->bindInt("id", $playerId);
    $stm->bindStr("reference", $this->reference);
    $stm->bindStr("comment", $this->comment);
    $stm->bindInt("refplayer", $this->refPlayerId);
    $stm->bindInt("type", PlayersDeptConstants::NEWPLAYER_TYPE_REGISTRATION);
    $stm->execute();

    $stm = $this->db->prepare("INSERT INTO advert_report (id, register, name, email, reference, referrer, language, country)
      VALUES (:id, :register, :name, :email, :reference, :referrer, :language, :country)");
    $stm->bindInt("id", $playerId);
    $stm->bindInt("register", GameDate::NOW()->getDay());
    $stm->bindStr("name", $this->firstname . " " . $this->lastname);
    $stm->bindStr("email", $this->email);
    $stm->bindStr("reference", $this->reference);
    $stm->bindStr("referrer", $this->referrer);
    $stm->bindInt("language", $this->language);
    $stm->bindStr("country", $this->country);
    $stm->execute();

    Report::saveInPcStatistics("papplied", $playerId);

    if (!empty($this->comment)) {
      $registrationCommentStat = new Statistic("registration_comment", $this->db);
      $registrationCommentStat->store(substr($this->comment, 0, 31), $playerId);
    }
    if (!empty($this->reference)) {
      $registrationReferenceStat = new Statistic("registration_reference", $this->db);
      $registrationReferenceStat->store(substr($this->reference, 0, 31), $playerId);
    }

    $remaddr = $_SERVER['REMOTE_ADDR'];
    $remhost = gethostbyaddr($remaddr);
    $newPlayerMatchesReporting = new NewPlayerMatchesReporting($this, $this->db);
    list($message, $CEIP) = $newPlayerMatchesReporting->findMatchesWithPlayersDatabase($playerId, $remaddr);

    $info = date("d/m/Y H:i") . " $remaddr ($remhost)";
    $this->ipinfo = $info;

    $stm = $this->db->prepare("UPDATE newplayers SET ipinfo = :info, research = :message,
      cedata = :ceip WHERE id = :playerId");
    $stm->bindStr("info", $info);
    $stm->bindStr("message", $message);
    $stm->bindStr("ceip", $CEIP);
    $stm->bindInt("playerId", $playerId);
    $stm->execute();

    $tag = new tag;
    $tag->language = $this->language;
    $tag->html = false;
    $name = urlencode($this->firstname);
    $tag->content = "<CANTR REPLACE NAME=email_pending_accept FIRSTNAME=$name>";
    $message = $tag->interpret();

    $tag->content = "<CANTR REPLACE NAME=email_pending_accept_subject>";
    $subject = $tag->interpret();

    $mailService = new MailService("Cantr Accounts", $GLOBALS['emailSupport']);
    $mailService->send($this->email, $subject, $message);
  }

  /**
   * @param $refPlayer string specified by the player as a referrer
   * @param Db $db database handle
   * @return int|null integer if the ref player is found, null otherwise
   */
  private static function parseReferringPlayer($refPlayer, Db $db)
  {
    if (Validation::isPositiveInt($refPlayer)) {
      return $refPlayer;
    }

    if (StringUtil::contains($refPlayer, "@")) { // looks like an email
      // select the oldest account, preferably an active one
      $stm = $db->prepare("SELECT id FROM players WHERE email = :email ORDER BY status, id LIMIT 1");
      $stm->bindStr("email", $refPlayer);
      $refPlayerId = $stm->executeScalar();
      if ($refPlayerId > 0) {
        return $refPlayerId;
      }
    }
    if (!empty($refPlayer)) {
      $stm = $db->prepare("SELECT id FROM players WHERE username = :userName");
      $stm->bindStr("userName", $refPlayer);
      $refPlayerId = $stm->executeScalar();
      if ($refPlayerId > 0) {
        return $refPlayerId;
      }
    }
    return 0;
  }

  /**
   *  Validate a character name
   */
  function validate_char($name, $sex)
  {
    if (!Validation::isOnlyAlphabeticOrSpace($name, "\-'\"`")) {
      $this->error = "<CANTR REPLACE NAME=error_invalid_charname CHARNAME=$name>";
      return false;
    }
    return true;
  }

  /**
   * @return string
   */
  public function getFirstname()
  {
    return $this->firstname;
  }

  /**
   * @return string
   */
  public function getLastname()
  {
    return $this->lastname;
  }

  /**
   * @return string
   */
  public function getEmail()
  {
    return $this->email;
  }
}

