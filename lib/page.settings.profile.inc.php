<?php

class SettingsProfile
{

  public $template_name = "page.settings.profile.tpl";

  /** @var Db */
  private $db;

  public function __construct()
  {
    $this->db = Db::get();
  }

  /**
   * @param $username string username
   * @param $player int player id that should be excluded from the matching
   * @param Environment $env environment giving access to intro db
   * @return bool|string true when it's ok, otherwise a translation tag with error message
   */
  public function validateUsername($username, $player, Environment $env)
  {
    if (strlen($username) < 4) {
      return "<CANTR REPLACE NAME=error_username_too_short>";
    }
    if (!preg_match("/^[a-zA-Z].{3,}/", $username)) {
      return "<CANTR REPLACE NAME=error_username_first_char>";
    }

    $stm = $this->db->prepare("SELECT COUNT(*) FROM players WHERE username LIKE :username AND id != :ownId");
    $stm->bindStr("username", $username);
    $stm->bindInt("ownId", $player);
    $exist = $stm->executeScalar() > 0;
    if ($exist) {
      return "<CANTR REPLACE NAME=error_username_exist>";
    }

    return true;
  }


  /**
   * @param $data
   * @param $player
   */
  private function updatePlayerProfile($data, $player)
  {
    // RETRIEVE _REQUEST DATA
    $username = HTTPContext::getRawString('username');
    $firstname = HTTPContext::getString('firstname', '');
    $lastname = HTTPContext::getString('lastname', '');
    $country = HTTPContext::getString('country', '');
    $ircnick = HTTPContext::getString('ircnick', '');
    $forumnick = HTTPContext::getString('forumnick', '');
    $language = HTTPContext::getInteger('language', 1);
    $unsub_countdown = HTTPContext::getInteger("unsub_countdown", 1);
    $autosend_reports = HTTPContext::getInteger("autosend_reports");

    // PASSWORD CHANGE - W/O SANITY CHECK
    $old_password = $_REQUEST['old_password'];
    $new_password = $_REQUEST['new_password'];
    $retype_new_password = $_REQUEST['retype_new_password'];

    // EMAIL
    $new_email = $_REQUEST['new_email'];
    $validation_code = HTTPContext::getInteger('validation_code', null);

    // ON LEAVE
    $onleave = HTTPContext::getInteger('onleave', 0);
    $days = HTTPContext::getInteger('days');


    $playerInfo = Request::getInstance()->getPlayer();
    // possbile $data values: (onleave, personal, password, newemail, validateemail)
    if ($data == "onleave") {

      if ($onleave) {
        if ($days > 90) {
          $days = 90;
        }
      } else {
        $days = 0;
      }

      $stm = $this->db->prepare("UPDATE players SET onleave = :onleaveDays WHERE id = :player LIMIT 1");
      $stm->bindInt("onleaveDays", $days);
      $stm->bindInt("player", $player);
      $stm->execute();
    }

    if ($data == "personal") {
      if ($playerInfo->getUserName() != $username && $username != '') {
        $env = Request::getInstance()->getEnvironment();
        $res = $this->validateUsername($username, $player, $env);
        if ($res !== true) {
          CError::throwRedirect("settings", $res);
        }
      }

      if (empty($username)) {
        $username = null;
      }
      $stm = $this->db->prepare("UPDATE players SET
          username = :username,
          firstname = :firstname,
          lastname = :lastname,
          language = :language,
          nick = :ircnick,
          forumnick = :forumnick,
          country = :country
        WHERE id = :playerId LIMIT 1");
      $stm->bindStr("username", $username, true);
      $stm->bindStr("firstname", $firstname);
      $stm->bindStr("lastname", $lastname);
      $stm->bindInt("language", $language);
      $stm->bindStr("ircnick", $ircnick);
      $stm->bindStr("forumnick", $forumnick);
      $stm->bindStr("country", $country);
      $stm->bindInt("playerId", $player);
      $stm->execute();


      $stm = $this->db->prepare("UPDATE sessions SET language = :language WHERE player = :playerId LIMIT 1");
      $stm->bindInt("language", $language);
      $stm->bindInt("playerId", $player);
      $stm->execute();
    }

    if ($data == "newemail") {
      if (!Validation::isEmailValid($new_email)) {
        CError::throwRedirect("settings", "The specified email address is not valid.");
      }

      $stm = $this->db->prepare("SELECT COUNT(*) FROM players WHERE email = :email AND status <= :status");
      $stm->bindStr("email", $new_email);
      $stm->bindInt("status", PlayerConstants::LOCKED);
      $emailAlreadyUsed = $stm->executeScalar() > 0;
      if ($emailAlreadyUsed) {
        CError::throwRedirect("settings", "There is already a player registered with this email ($new_email)");
      }

      // you can try to send email again if the previous didn't arrive
      $stm = $this->db->prepare("SELECT COUNT(*) FROM unvalidated_email WHERE email = :email AND player != :playerId");
      $stm->bindStr("email", $new_email);
      $stm->bindInt("playerId", $player);
      $count_emails = $stm->executeScalar();
      if ($count_emails > 0) {
        CError::throwRedirect("settings", "There is already a player who wants to set this email as his account email ($new_email)");
      }

      $code = $this->getValidationCode();

      $stm = $this->db->prepare("DELETE FROM unvalidated_email WHERE `player`= :playerId LIMIT 1");
      $stm->bindInt("playerId", $player);
      $stm->execute();

      $stm = $this->db->prepare("INSERT INTO unvalidated_email (email, code, player, expires)
        VALUES (:email, :code, :playerId, DATE_ADD(NOW(), INTERVAL 7 DAY))");
      $stm->bindStr("email", $new_email);
      $stm->bindStr("code", (string)$code);
      $stm->bindInt("playerId", $player);
      $stm->execute();

      $message = "This e-mail-address has been entered as a new address for a player of Cantr II!\n\n";
      $message .= "To validate this address you will have to enter the code\n";
      $message .= "$code";
      $message .= "\non the player's settings page within 7 days.\n\n";
      $message .= "\n\n(this is an automatically sent message)";

      $mailService = new MailService("Cantr Players Department", $GLOBALS['emailSupport']);
      $emailChangeTitle = TagBuilder::forTag("mail_change_email_title")->build()->interpret();
      $mailService->sendPlaintext($new_email, $emailChangeTitle, $message);
    }

    $unvalidated_email_info = $this->getUnvalidatedEmailInfo($player);

    if ($data == "validateemail" && $unvalidated_email_info != null) {
      if ($validation_code != $unvalidated_email_info->code) {
        CError::throwRedirect("settings", "Wrong validation code!");
      }

      $stm = $this->db->prepare("UPDATE players SET email = :email WHERE id = :playerId LIMIT 1");
      $stm->bindStr("email", $unvalidated_email_info->email);
      $stm->bindInt("playerId", $player);
      $stm->execute();

      $stm = $this->db->prepare("DELETE FROM unvalidated_email WHERE player = :playerId");
      $stm->bindInt("playerId", $player);
      $stm->execute();
    }

    if ($data == "password" && !empty($old_password)) {
      if (!SecurityUtil::verifyPassword($old_password, $playerInfo->getPasswordHash())) {
        CError::throwRedirect("settings", "Old Password is incorrect!");
      }
      if (mb_strlen($new_password) < 6) {
        CError::throwRedirect("settings", "New Password is too short!");
      }

      if ($new_password != $retype_new_password) {
        CError::throwRedirect("settings", "New Password and the Re-Typed New Password do not match!");
      }
      $hashed_new_password = SecurityUtil::generatePasswordHash($new_password);
      $stm = $this->db->prepare("UPDATE players SET password = :password WHERE id = :playerId LIMIT 1");
      $stm->bindStr("password", $hashed_new_password);
      $stm->bindInt("playerId", $player);
      $stm->execute();
    }

    if ($data == "unsub_countdown") {
      $unsubCountdown = ($unsub_countdown == 1) ? "true" : "false";
      $stm = $this->db->prepare("SELECT unsub_countdown FROM players WHERE id = :playerId");
      $stm->bindInt("playerId", $player);
      $isCountdownActive = $stm->executeScalar();
      if ($isCountdownActive && ($unsubCountdown == "false")) { // if disabling the limitation then countdown is needed
        Limitations::delLims($player, Limitations::TYPE_PLAYER_UNSUB_LOCK);
        Limitations::addLim($player, Limitations::TYPE_PLAYER_UNSUB_LOCK, Limitations::dhmstoc(PlayerConstants::UNSUB_LOCK_DAYS, 0, 0, 0));
      } elseif (!$isCountdownActive && ($unsubCountdown == "true")) {
        Limitations::delLims($player, Limitations::TYPE_PLAYER_UNSUB_LOCK);
        Limitations::delLims($player, Limitations::TYPE_PLAYER_UNSUB_ALLOW);
      }
      $stm = $this->db->prepare("UPDATE players SET unsub_countdown = :countdown WHERE id = :playerId");
      $stm->bindInt("countdown", $unsubCountdown);
      $stm->bindInt("playerId", $player);
      $stm->execute();
    }

    if ($data == "autosend_reports") {
      $autosend_reports = $autosend_reports != 0 ? 1 : 0;
      $stm = $this->db->prepare("UPDATE players SET profile_options = :autosendReports WHERE id = :playerId");
      $stm->bindInt("autosendReports", $autosend_reports);
      $stm->bindInt("playerId", $player);
      $stm->execute();
    }
  }

  public function getSmarty()
  {
    global $player;
    global $data;

    $this->updatePlayerProfile($data, $player);

    // player account data again to get new data
    $playerInfo = Request::getInstance()->getPlayer();

    $smarty = new CantrSmarty;

    $personal = array();
    $personal['id'] = $playerInfo->getId();
    $personal['username'] = $playerInfo->getUserName();
    $personal['firstname'] = $playerInfo->getFirstName();
    $personal['lastname'] = $playerInfo->getLastName();
    $personal['language_id'] = $playerInfo->getLanguage();
    $personal['country'] = $playerInfo->getCountry();
    $personal['irc_nick'] = $playerInfo->getIrcNick();
    $personal['forum_nick'] = $playerInfo->getForumNick();

    $smarty->assign("personal", $personal);

    $languages_list = array();
    $stm = $this->db->query("SELECT id, original_name FROM languages ORDER BY id");
    foreach ($stm->fetchAll() as $lang_info) {
      $languages_list[$lang_info->id] = $lang_info->original_name;
    }

    $smarty->assign("languages_list", $languages_list);
    $smarty->assign("countries_list", $this->getCountries());

    $smarty->assign("email", $playerInfo->getEmail());

    $unvalidatedEmailInfo = $this->getUnvalidatedEmailInfo($player);
    $smarty->assign("tovalidate", $unvalidatedEmailInfo != null);
    $smarty->assign("newemail", $unvalidatedEmailInfo->email);

    $smarty->assign("onleave_days", $playerInfo->getOnLeaveDays());
    $smarty->assign("unsub_countdown", $playerInfo->isUnsubCountdownEnabled());
    $smarty->assign("autosend_reports", $playerInfo->shouldAutosendTurnReports());

    /*
      STAFF ASSIGNMENTS
    */

    $smarty->assign("assignments", $this->getAssignments($player));
    $smarty->assign("privileges", $this->getPrivileges($player));
    $smarty->assign("CE_privileges", $this->getCE_privileges($player));

    return $smarty;
  }


  /*
    PRIVATE FUNCTIONS
  */

  private function getCountries()
  {
    $countries_list = array();
    $countries_list["Other"] = "form_other";
    $countries_list["Australia"] = "country_australia";
    $countries_list["Austria"] = "country_austria";
    $countries_list["Belgium"] = "country_belgium";
    $countries_list["Brazil"] = "country_brazil";
    $countries_list["Canada"] = "country_canada";
    $countries_list["China"] = "country_china";
    $countries_list["Denmark"] = "country_denmark";
    $countries_list["Egypt"] = "country_egypt";
    $countries_list["Finland"] = "country_finland";
    $countries_list["France"] = "country_france";
    $countries_list["Germany"] = "country_germany";
    $countries_list["Indonesia"] = "country_indonesia";
    $countries_list["Ireland"] = "country_ireland";
    $countries_list["Lithuania"] = "country_lithuania";
    $countries_list["Mexico"] = "country_mexico";
    $countries_list["Netherlands"] = "country_netherlands";
    $countries_list["New Zealand"] = "country_newzealand";
    $countries_list["Norway"] = "country_norway";
    $countries_list["Poland"] = "country_poland";
    $countries_list["Portugal"] = "country_portugal";
    $countries_list["Puerto Rico"] = "country_puertorico";
    $countries_list["Romania"] = "country_romania";
    $countries_list["Russia"] = "country_russia";
    $countries_list["Singapore"] = "country_singapore";
    $countries_list["Spain"] = "country_spain";
    $countries_list["Sweden"] = "country_sweden";
    $countries_list["Switserland"] = "country_switserland";
    $countries_list["Turkey"] = "country_turkey";
    $countries_list["United Kingdom"] = "country_unitedkingdom";
    $countries_list["United States"] = "country_usa";
    $countries_list["Zambia"] = "country_zambia";

    return $countries_list;
  }

  /**
   * @return int random 4 digit number
   */
  private function getValidationCode()
  {
    return mt_rand(1000, 9999);
  }

  private function getAssignments($player_id)
  {
    $staff = array();
    $staff[0] = "Hidden member";
    $staff[1] = "Chair";
    $staff[2] = "Senior member";
    $staff[3] = "Member";
    $staff[4] = "Special member";
    $staff[5] = "Aspirant member";
    $staff[6] = "On leave";

    $stm = $this->db->prepare("SELECT assignments.status AS status,
        assignments.special AS special, councils.name AS council
      FROM assignments,councils
      WHERE player = :playerId AND assignments.council = councils.id");
    $stm->bindInt("playerId", $player_id);
    $stm->execute();

    $assignments = array();
    foreach ($stm->fetchAll() as $assignment_info) {
      $assignment = array();
      $assignment['status_name'] = preg_replace("/ /", "&nbsp;", $staff[$assignment_info->status]);
      $assignment['status_id'] = $assignment_info->status;
      $assignment['council'] = preg_replace("/ /", "&nbsp;", $assignment_info->council);

      if ($assignment['status'] == 4) { // if a special member
        $assignment['special'] = $assignment_info->special;
      }
      $assignments[] = $assignment;
    }
    return $assignments;
  }

  private function getPrivileges($player_id)
  {
    $stm = $this->db->prepare("SELECT access_types.description
      FROM access,access_types
      WHERE player = :playerId AND access.page = access_types.id");
    $stm->bindInt("playerId", $player_id);
    $stm->execute();
    return $stm->fetchScalars();
  }

  private function getCE_privileges($player_id)
  {
    $stm = $this->db->prepare("SELECT ceAccessTypes.description
      FROM ceAccess, ceAccessTypes
      WHERE player = :playerId AND ceAccess.access = ceAccessTypes.id");
    $stm->bindInt("playerId", $player_id);
    $stm->execute();
    return $stm->fetchScalars();
  }

  /**
   * @param $player
   * @return stdClass|null object with fields 'email' and 'code' when the new email is not yet validated, null otherwise
   */
  private function getUnvalidatedEmailInfo($player)
  {
    $stm = $this->db->prepare("SELECT * FROM unvalidated_email WHERE player = :playerId LIMIT 1");
    $stm->bindInt("playerId", $player);
    $stm->execute();
    if ($unvalidated_email_info = $stm->fetchObject()) {
      return $unvalidated_email_info;
    }
    return null;
  }
}
