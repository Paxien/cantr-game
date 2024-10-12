<?php

class Player
{

  private $id;

  private $userName;
  private $firstName;
  private $lastName;
  private $email;
  private $password;
  private $language;
  private $registerDay; // cantr day
  private $lastDate; // cantr day
  private $lastTime; // cantr hour
  private $lastLogin;
  private $status;
  private $onLeave; // 1 or 0
  private $approval;
  private $country;
  private $age;
  private $trouble;
  private $forumNick;
  private $ircNick;
  private $unsubCountdownEnabled;
  private $profileOptions;
  /**
   * @var Db
   */
  private $db;
  /** @var GlobalConfig */
  private $globalConfig;

  public static function loadById($playerId, $db = null)
  {
    if ($db === null) {
      $db = Db::get();
    }

    $stm = $db->prepare("SELECT * FROM players WHERE id = :id");
    $stm->bindInt("id", $playerId);
    $stm->execute();
    if ($playerInfo = $stm->fetchObject()) {
      $globalConfig = new GlobalConfig($db);
      return new Player($playerInfo, $db, $globalConfig);
    }
    throw new InvalidArgumentException("$playerId is not a valid player id");
  }

  private function __construct($playerInfo, Db $db, GlobalConfig $globalConfig)
  {
    $this->id = $playerInfo->id;

    $this->userName = $playerInfo->username;
    $this->firstName = $playerInfo->firstname;
    $this->lastName = $playerInfo->lastname;

    $this->email = $playerInfo->email;
    $this->password = $playerInfo->password;
    $this->status = $playerInfo->status;
    $this->language = $playerInfo->language;
    $this->registerDay = $playerInfo->register;
    $this->lastDate = $playerInfo->lastdate;
    $this->lastTime = $playerInfo->lasttime;
    $this->lastLogin = $playerInfo->lastlogin;
    $this->onLeave = $playerInfo->onleave;
    $this->approval = $playerInfo->approval;

    $this->country = $playerInfo->country;
    $this->age = $playerInfo->age;
    $this->trouble = $playerInfo->trouble;

    $this->forumNick = $playerInfo->forumnick;
    $this->ircNick = $playerInfo->nick;
    $this->unsubCountdownEnabled = $playerInfo->unsub_countdown;
    $this->profileOptions = $playerInfo->profile_options;

    $this->db = $db;
    $this->globalConfig = $globalConfig;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getFirstName()
  {
    return $this->firstName;
  }

  public function getLastName()
  {
    return $this->lastName;
  }

  public function getFullName()
  {
    return $this->getFirstName() . " " . $this->getLastName();
  }

  public function getFullNameWithId()
  {
    return sprintf("%s [%d]", $this->getFullName(), $this->getId());
  }

  public function getOnLeaveDays()
  {
    return $this->onLeave;
  }

  public function isOnLeave()
  {
    return $this->onLeave > 0;
  }

  public function getLanguage()
  {
    return $this->language;
  }

  public function getAgeInDays()
  {
    return GameDate::NOW()->minus(GameDate::fromDate($this->registerDay, 0, 0, 0))->getDay();
  }

  public function getBirthYear()
  {
    return $this->age;
  }

  public function getRegisterDay()
  {
    return $this->registerDay;
  }

  /**
   * Returns number of days of waiting to get a new free slot.
   * @return int 0 - can be done immediately, PlayerConstants::NO_CHAR_SLOTS_LEFT when unspecified or account is quite new
   */
  public function getDaysToFreeCharacterSlot()
  {
    $aliveChars = $this->getCharacterCount();
    if (($aliveChars + 1) > $this->globalConfig->getMaxCharactersPerPlayer()) {
      return PlayerConstants::NO_CHAR_SLOTS_LEFT;
    }

    $gameDay = GameDate::NOW()->getDay();
    // check if some slots are blocked
    $youngCharDeathDay = $gameDay - CharacterConstants::DEAD_CLOSE_SLOT_DAYS;
    $youngCharSpawnDay = $gameDay - CharacterConstants::DEAD_CLOSE_SLOT_AGE;
    $stm = $this->db->prepare("SELECT id FROM chars WHERE player = :playerId
      AND register > :spawnDay AND death_date > :deathDay
      AND status > :active");
    $stm->bindInt("playerId", $this->getId());
    $stm->bindInt("spawnDay", $youngCharSpawnDay);
    $stm->bindInt("deathDay", $youngCharDeathDay);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();

    $deadChars = $stm->fetchScalars();

    $allChars = count($deadChars) + $aliveChars;

    if (($allChars + 1) > ($this->getAgeInDays() + 2)) { // 2 chars in day 0, +1 every day
      return PlayerConstants::NO_CHAR_SLOTS_LEFT;
    } elseif (($allChars + 1) > $this->globalConfig->getMaxCharactersPerPlayer()) { // situation where all chars are alive is trapped earlier
      $slotBlockDays = Pipe::from($deadChars)->map(function($chId) {
        return Character::loadById($chId, $this->db);
      })->map(function(Character $char) use ($gameDay) {
        return $char->getSlotBlockedDays($gameDay);
      })->reduce("min"); // minimum of days left

      return $slotBlockDays;
    }
    return 0;
  }

  /**
   * @return Character[]
   */
  public function getAliveCharacters()
  {
    $stm = $this->db->prepare("SELECT id FROM chars WHERE player = :playerId AND status <= :active");
    $stm->bindInt("playerId", $this->getId());
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();
    $charsAlive = Pipe::from($stm->fetchScalars())->map(function($charId) {
      return Character::loadById($charId);
    })->toArray();
    return $charsAlive;
  }

  public function getCharacterCount()
  {
    $stm = $this->db->prepare("SELECT COUNT(*) FROM chars WHERE player = :playerId AND status <= :active");
    $stm->bindInt("playerId", $this->getId());
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    return $stm->executeScalar();
  }

  public function getUserName()
  {
    return $this->userName;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function setEmail($email)
  {
    $this->email = $email;
  }

  public function getPasswordHash()
  {
    return $this->password;
  }

  public function getAcceptor()
  {
    return $this->approval;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function setStatus($status)
  {
    $this->status = intval($status);
  }

  /**
   * @return bool true if the account is active or locked
   */
  public function isAlive()
  {
    return in_array($this->status, [PlayerConstants::APPROVED, PlayerConstants::ACTIVE, PlayerConstants::LOCKED]);
  }

  public function getLastLoginString()
  {
    return $this->lastLogin;
  }

  public function getLastGameDate()
  {
    return GameDate::fromDate($this->lastDate, $this->lastTime, 0, 0);
  }

  public function isTroublesome()
  {
    return $this->trouble;
  }

  public function getCountry()
  {
    return $this->country;
  }

  /**
   * @return string
   */
  public function getForumNick()
  {
    return $this->forumNick;
  }

  /**
   * @return string
   */
  public function getIrcNick()
  {
    return $this->ircNick;
  }

  /**
   * @return bool
   */
  public function isUnsubCountdownEnabled()
  {
    return $this->unsubCountdownEnabled > 0;
  }

  public function shouldAutosendTurnReports()
  {
    return $this->profileOptions == 1;
  }

  public function getAccessList()
  {
    $stm = $this->db->prepare("SELECT page FROM access WHERE player = :playerId");
    $stm->bindInt("playerId", $this->id);
    $stm->execute();
    return $stm->fetchScalars();
  }

  public function hasAccessTo($accessType)
  {
    $stm = $this->db->prepare("SELECT COUNT(*) AS count FROM access
      WHERE player = :playerId AND page = :type");
    $stm->bindInt("playerId", $this->getId());
    $stm->bindInt("type", $accessType);
    return $stm->executeScalar() > 0;
  }

  public function saveInDb()
  {
    $stm = $this->db->prepare("UPDATE `players` SET
        `username` = :userName,
        `firstname` = :firstName, 
        `lastname` = :lastName,
        `email` = :email,
        `nick` = :ircNick,
        `forumnick` = :forumNick,
        `age` = :age,
        `country` = :country,
        `language` = :language,
        `password` = :password,
        `register` = :register,
        `lastdate` = :lastDate,
        `lasttime` = :lastTime,
        `lastlogin` = :lastLogin,
        `approval` = :approval,
        `onleave` = :onleave,
        `trouble` = :trouble,
        `status` = :status,
        `unsub_countdown` = :countdownEnabled,
        `profile_options` = :profileOptions
        WHERE `id` = :id
    ");
    $stm->bindStr("userName", $this->userName, true);
    $stm->bindStr("firstName", $this->firstName);
    $stm->bindStr("lastName", $this->lastName);
    $stm->bindStr("email", $this->email);
    $stm->bindStr("ircNick", $this->ircNick);
    $stm->bindStr("forumNick", $this->forumNick);
    $stm->bindInt("age", $this->age);
    $stm->bindStr("country", $this->country);
    $stm->bindInt("language", $this->language);
    $stm->bindStr("password", $this->password);
    $stm->bindInt("register", $this->registerDay);
    $stm->bindInt("lastDate", $this->lastDate);
    $stm->bindInt("lastTime", $this->lastTime);
    $stm->bindStr("lastLogin", $this->lastLogin);
    $stm->bindInt("approval", $this->approval);
    $stm->bindInt("onleave", $this->onLeave);
    $stm->bindInt("trouble", $this->trouble);
    $stm->bindInt("status", $this->status);
    $stm->bindInt("countdownEnabled", intval($this->isUnsubCountdownEnabled()));
    $stm->bindInt("profileOptions", $this->profileOptions);
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }
}
