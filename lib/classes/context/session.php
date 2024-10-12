<?php

class Session
{
  const SESSION_EXPIRATION_SECONDS = 48 * 60 * 60;

  //players id, not session id.
  var $id;
  var $user_name;
  var $password;
  var $session_id;
  var $session_char;
  var $language;
  var $languagestr;
  var $error;
  var $html;
  var $onetime;

  private $needUpdateHashingMethod = false;
  /** @var Db */
  private $db;

  public function __construct($s = null, $charId = null, $html = true)
  {
    $this->session_id = $s;
    $this->session_char = $charId;
    $this->html = $html;
    $this->db = Db::get();
  }

  function validate()
  {
    $accepted = true;

    $this->id = intval($this->id);

    if (!$this->id && !$this->user_name) {
      $this->error = "Invalid ID";
      $accepted = false;
    } else {
      if (!empty($this->user_name)) {
        $stm = $this->db->prepare("SELECT p.id, p.password, p.language, p.status, l.abbreviation AS languagestr
        FROM players p, languages l WHERE (p.username LIKE :username) AND l.id = p.language LIMIT 1");
        $stm->bindStr("username", $this->user_name);
        $stm->execute();
      } else {
        $stm = $this->db->prepare("SELECT p.id, p.password, p.language, p.status, l.abbreviation AS languagestr
        FROM players p, languages l WHERE (p.id = :id) AND l.id = p.language LIMIT 1");
        $stm->bindInt("id", $this->id);
        $stm->execute();
      }
      if ($player_info = $stm->fetchObject()) {
        $this->id = $player_info->id;
        $this->user_name = $player_info->user_name;

        if (in_array($player_info->status, [PlayerConstants::REMOVED, PlayerConstants::REFUSED])) {
          $this->error = "Invalid ID";
          $accepted = false;
        } elseif (empty($this->password)) {
          //blank passwords are not allowed
          $this->error = "<CANTR REPLACE NAME=error_incorrect_password>";
          $accepted = false;
        } else {
          if ($this->onetime) { // it's a one-time password
            $stm = $this->db->prepare("SELECT password FROM onetime_passwords WHERE player = :playerId");
            $stm->bindInt("playerId", $this->id);
            $stm->execute();
            $potentialPasswords = $stm->fetchScalars();

            $success = false;
            foreach ($potentialPasswords as $oneTimePasswordHash) {
              if (strlen($oneTimePasswordHash) <= 32) {
                if (SecurityUtil::generateOldPasswordHash($this->password) == $oneTimePasswordHash) {
                  $success = true;
                  $stm = $this->db->prepare("DELETE FROM onetime_passwords WHERE player = :playerId AND password = :password");
                  $stm->bindInt("playerId", $this->id);
                  $stm->bindStr("password", $oneTimePasswordHash);
                  $stm->execute();
                }
              } else {
                if (SecurityUtil::verifyPassword($this->password, $oneTimePasswordHash)) {
                  $success = true;
                  $stm = $this->db->prepare("DELETE FROM onetime_passwords WHERE player = :playerId AND password = :password");
                  $stm->bindInt("playerId", $this->id);
                  $stm->bindStr("password", $oneTimePasswordHash);
                  $stm->execute();
                }
              }
            }
            if (!$success) {
              $this->error = "<CANTR REPLACE NAME=error_incorrect_onetime_password>";
              $accepted = false;
            }
          } else { // it's a normal password
            if (strlen($player_info->password) <= 32) { // password is hashed using old hash
              $this->needUpdateHashingMethod = true;
              if (SecurityUtil::generateOldPasswordHash($this->password) != $player_info->password) {
                $this->error = "<CANTR REPLACE NAME=error_incorrect_password>";
                $accepted = false;
              }
            } elseif (!SecurityUtil::verifyPassword($this->password, $player_info->password)) {
              $this->error = "<CANTR REPLACE NAME=error_incorrect_password>";
              $accepted = false;
            }
          }
        }

        // let's guess how many people try to login to learn they idled out
        $closedAccountLoginAttemptStats = new Statistic("closed_account_login", Db::get());
        if ($player_info->status > PlayerConstants::LOCKED && $this->error != "<CANTR REPLACE NAME=error_incorrect_password>") {
          $closedAccountLoginAttemptStats->store($player_info->status, $player_info->id);
        }
      } else {
        $this->error = "<CANTR REPLACE NAME=error_no_such_id>";
        $accepted = false;
      }
    }

    if ($accepted) {

      $this->language = $player_info->language;
      $this->languagestr = $player_info->languagestr;
    }

    return $accepted;
  }

  public static function sessionExistsInDatabase($sessionId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT COUNT(*) FROM sessions WHERE id = :id");
    $stm->bindInt("id", $sessionId);
    return $stm->executeScalar() > 0;
  }

  public static function deleteSessionFromDatabase($sessionId)
  {
    $db = Db::get();
    $stm = $db->prepare("DELETE FROM sessions WHERE id = :id");
    $stm->bindInt("id", $sessionId);
    $stm->execute();
  }

  public static function deleteExpiredSessions(Db $db, $expirationTimestamp)
  {
    $stm = $db->prepare("DELETE FROM sessions WHERE lasttime < FROM_UNIXTIME(:date)");
    $stm->bindInt("date", $expirationTimestamp);
    $stm->execute();
  }

  function start()
  {
    srand((float)microtime() * 1000000);

    $remaddr = $_SERVER['REMOTE_ADDR'];
    $remhost = gethostbyaddr($remaddr);

    $date = date("d/m/Y H:i");
    $info = "$date $remaddr ($remhost)";
    $randval = $this->make_id();

    $stm = $this->db->prepare("INSERT INTO sessions (id, player, language, info, login_ip, lasttime)
      VALUES (:id, :playerId, :language, :info, :remaddr, NOW())");
    $stm->bindInt("id", $randval);
    $stm->bindInt("playerId", $this->id);
    $stm->bindInt("language", $this->language);
    $stm->bindStr("info", $info);
    $stm->bindStr("remaddr", $remaddr);
    $stm->execute();
    if ($stm->rowCount() == 0) {
      return -1;
    }
    session::updateCookie($randval);

    $headers = emu_getallheaders();

    if (isset($headers["Client-IP"])) {
      $client_ip = $headers["Client-IP"];
    }

    if (isset($headers["X-Forwarded-For"])) {
      $client_ip = $headers["X-Forwarded-For"];
    }

    if (isset($client_ip) && preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\.\d{1,3}.\d{1,3})?$/", $client_ip)) {
      $stm = $this->db->prepare("SELECT COUNT(*) FROM ips WHERE player = :playerId AND client_ip = :clientIp");
      $stm->bindInt("playerId", $this->id);
      $stm->bindStr("clientIp", $client_ip);
      $countips = $stm->executeScalar();
      $date = date("Y-m-d H:i:s");
      if ($countips > 0) {
        $stm = $this->db->prepare("UPDATE ips SET times = times + 1, lasttime = :date, endtime = NULL WHERE player = :playerId AND client_ip = :clientIp");
        $stm->bindStr("date", $date);
        $stm->bindInt("playerId", $this->id);
        $stm->bindStr("clientIp", $client_ip);
        $stm->execute();
      } else {
        $stm = $this->db->prepare("INSERT INTO ips (player,ip,client_ip,times,lasttime) VALUES (:playerId, :remaddr, :clientIp, 1, :date)");
        $stm->bindInt("playerId", $this->id);
        $stm->bindStr("remaddr", $remaddr);
        $stm->bindStr("date", $date);
        $stm->bindStr("clientIp", $client_ip);
        $stm->execute();
      }
    } else {
      // Update IP history entries
      $stm = $this->db->prepare("SELECT COUNT(*) FROM ips WHERE player = :playerId AND ip = :remaddr");
      $stm->bindInt("playerId", $this->id);
      $stm->bindStr("remaddr", $remaddr);
      $countips = $stm->executeScalar();
      $date = date("Y-m-d H:i:s");
      if ($countips > 0) {
        $stm = $this->db->prepare("UPDATE ips SET times = times + 1,lasttime = :date, endtime = NULL WHERE player = :playerId AND ip = :remaddr");
        $stm->bindStr("date", $date);
        $stm->bindInt("playerId", $this->id);
        $stm->bindStr("remaddr", $remaddr);
        $stm->execute();
      } else {
        $stm = $this->db->prepare("INSERT INTO ips (player,ip,times,lasttime) VALUES (:playerId, :remaddr, 1, :date)");
        $stm->bindInt("playerId", $this->id);
        $stm->bindStr("remaddr", $remaddr);
        $stm->bindStr("date", $date);
        $stm->execute();
      }
    }

    $gameDate = GameDate::NOW();
    // Update players last login settings
    $stm = $this->db->prepare("UPDATE players SET lastdate = :day, lasttime = :hour, lastlogin = :info, recent_activity = recent_activity | 1 WHERE id = :playerId LIMIT 1");
    $stm->bindInt("day", $gameDate->getDay());
    $stm->bindInt("hour", $gameDate->getHour());
    $stm->bindStr("info", $info);
    $stm->bindInt("playerId", $this->id);
    $stm->execute();

    // In case of watch, mail headers
    $message = "$info\n\n";

    foreach ($headers as $header => $value) {
      $message .= "$header: $value\n";
    }

    $stm = $this->db->prepare("SELECT watches.email AS email, players.firstname AS fname, players.lastname AS lname FROM watches, players WHERE watches.player = :playerId AND watches.player = players.id");
    $stm->bindInt("playerId", $this->id);
    $stm->execute();
    foreach ($stm->fetchAll() as $watch_info) {
      $mailService = new MailService("Players Department", $GLOBALS['emailPlayers']);
      $mailService->sendPlaintext($watch_info->email, "IP Watch on $watch_info->fname $watch_info->lname ($this->id)", $message);
    }

    if ($this->needUpdateHashingMethod) {
      $newPasswordHash = SecurityUtil::generatePasswordHash($this->password);
      $stm = $this->db->prepare("UPDATE players SET password = :password WHERE id = :playerId");
      $stm->bindStr("password", $newPasswordHash);
      $stm->bindInt("playerId", $this->id);
      $stm->execute();
    }

    return $randval;
  }

  public static function updateCookie($session_id)
  {
    $expirationTime = time() + self::SESSION_EXPIRATION_SECONDS;
    setcookie(_SESSION_COOKIE_NAME, $session_id, $expirationTime, "/", null, null, true);
  }

  public static function getSessionFromCookie()
  {
    if (isset($_COOKIE[_SESSION_COOKIE_NAME]) && !empty($_COOKIE[_SESSION_COOKIE_NAME])) {
      return intval($_COOKIE[_SESSION_COOKIE_NAME]);
    }
    return null;
  }

  public static function deleteCookie()
  {
    setcookie(_SESSION_COOKIE_NAME, 0, time() - _SESSION_EXPIRE_MINUTES * 60);
  }

  /**
   * Use of this method is risky, because it works based on global state
   */
  function checklogin()
  {

    $useHtml = $this->html;

    if (!$this->session_id) {
      if ($useHtml) {
        CError::throwRedirect("login", "You did not login correctly. Please re-login.");
      } else {
        return _SESSION_ERROR_LOGIN;
      }
    }

    $stm = $this->db->prepare("SELECT s.*, p.status FROM sessions s
      INNER JOIN players p ON p.id = s.player
      WHERE s.id = :sessionId LIMIT 1");
    $stm->bindInt("sessionId", $this->session_id);
    $stm->execute();
    if ($session_info = $stm->fetchObject()) {
      if (!Validation::isPositiveInt($this->session_id)) {
        die("Session id not numeric");
      }

      $this->language = $session_info->language;
      $session_info->languagestr = LanguageConstants::$LANGUAGE[$this->language]["lang_abr"] or die("Unknown language in session->checklogin()");
      $this->languagestr = $session_info->languagestr;
    } else {
      if ($useHtml) {
        Session::deleteCookie();
        CError::throwRedirect("login", "You did not login correctly or your login expired. Please re-login.");
      } else {
        return _SESSION_ERROR_EXPIRED;
      }
    }

    if ($session_info->id != $this->session_id) {
      if ($useHtml) {
        Session::deleteCookie();
        CError::throwRedirect("login", "You did not login correctly or your login expired. Please re-login.");
      } else {
        return _SESSION_ERROR_EXPIRED;
      }
    }

    if (in_array($session_info->status, [
      PlayerConstants::REFUSED,
      PlayerConstants::REMOVED,
    ])) {
      if ($useHtml) {
        Session::deleteCookie();
        CError::throwRedirect("login", "This account was removed");
      } else {
        return _SESSION_ERROR_EXPIRED;
      }
    }

    if ($this->session_char) {

      $stm = $this->db->query("SELECT locked FROM gamelock");
      $gamelock = $stm->fetchObject();

      if ($gamelock->locked) {

        if ($useHtml) {
          $error = new CError("<CANTR REPLACE NAME=error_locked>", "player");

          global $character;
          $character = null;

          $error->report();
        } else {
          return _SESSION_ERROR_LOCKED;
        }
      }

      if (!Validation::isPositiveInt($this->session_char)) {
        die("Character id is not numeric");
      }

      $stm = $this->db->prepare("SELECT ch.*, l.abbreviation AS languagestr FROM chars ch, languages l
        WHERE ch.id = :charId AND l.id = ch.language");
      $stm->bindInt("charId", $this->session_char);
      $stm->execute();

      $char_info = $stm->fetchObject();

      global $l;
      $l = $char_info->language;

      $stm = $this->db->prepare("SELECT COUNT(*) AS count FROM access WHERE player = :playerId AND page = 27");
      $stm->bindInt("playerId", $session_info->player);
      $canViewOtherChars = $stm->executeScalar();

      if ((!$char_info->player) || (($char_info->player != $session_info->player) && !$canViewOtherChars)) {

        global $character;
        $character = null;

        if ($useHtml) {
          CError::throwRedirect("login", "You did not login correctly or you are trying to use someone elses character. Please re-login.");
        } else {
          return _SESSION_ERROR_WRONG_CHAR;
        }
      } else {
        $this->language = $char_info->language;
        $this->languagestr = $char_info->languagestr;
      }
    }

    $stm = $this->db->prepare("UPDATE sessions SET lasttime=NOW() WHERE id = :sessionId LIMIT 1");
    $stm->bindInt("sessionId", $this->session_id);
    $stm->execute();

    $gameDay = GameDate::NOW()->getDay();
    $stm = $this->db->prepare("SELECT lastdate FROM players WHERE id = :playerId");
    $stm->bindInt("playerId", $session_info->player);
    $lastDay = $stm->executeScalar();
    if ($lastDay < $gameDay) {
      $stm = $this->db->prepare("UPDATE players SET lastdate = :lastDate WHERE id = :playerId");
      $stm->bindInt("lastDate", $gameDay);
      $stm->bindInt("playerId", $session_info->player);
      $stm->execute();
    }

    return $session_info;
  }

  private function make_id()
  {
    while (true) {
      //if we manipulate this - we need remember that url crypt system are related
      //with session key length.
      $remaddr = $_SERVER['REMOTE_ADDR'];

      // 4 byte suffix of session id is based on player's IP to eliminate session conflicts
      $sessionIdSuffix = intval(sprintf("%u", ip2long($remaddr)));

      $new_id = (1 << 32) * hexdec(sprintf("%X%X%X%X", mt_rand(0, 127), mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255))) + $sessionIdSuffix;
      $new_id = intval(sprintf("%u", $new_id));

      $stm = $this->db->prepare("SELECT id FROM sessions WHERE id = :sessionId LIMIT 1");
      $stm->bindInt("sessionId", $new_id);
      $idAlreadyExists = $stm->executeScalar();
      if (!$idAlreadyExists) {
        return $new_id;
      }
    }
  }

  public static function getActivePlayersCount($currentTime, $minutesInterval)
  {
    $db = Db::get();
    $timeBefore = $currentTime - $minutesInterval * 60;
    $stm = $db->prepare("SELECT COUNT(*) FROM sessions
      WHERE lasttime >= FROM_UNIXTIME(:timeBefore)");
    $stm->bindInt("timeBefore", $timeBefore);
    return $stm->executeScalar();
  }
}
