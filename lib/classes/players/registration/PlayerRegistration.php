<?php

class PlayerRegistration
{
  /** @var Db */
  private $db;

  public function __construct()
  {
    $this->db = Db::get();
  }

  public function perform(User $user)
  {
    $env = Request::getInstance()->getEnvironment();
    $day = GameDate::NOW()->getDay();

    $passwordHash = SecurityUtil::generatePasswordHash($user->password);
    $stm = $this->db->prepare("INSERT INTO players (username, firstname,lastname, email, age, country, language,
      password, register, lastdate, lasttime, admin, lastlogin, approval, status, refplayer, referrer)
    VALUES (:userName, :firstName, :lastName, :email, :age, :country, :language, :password,
      :register, :lastDate, :lastTime, 0, '', :approval, :status, 0, :referrer)");
    $stm->bindStr("userName", $user->username, true);
    $stm->bindStr("firstName", $user->firstname);
    $stm->bindStr("lastName", $user->lastname);
    $stm->bindStr("email", $user->email);
    $stm->bindInt("age", $user->age);
    $stm->bindStr("country", $user->country);
    $stm->bindInt("language", $user->language);
    $stm->bindStr("password", $passwordHash);
    $stm->bindInt("register", $day);
    $stm->bindInt("lastDate", $day);
    $stm->bindInt("lastTime", 0);
    $stm->bindInt("approval", 0);
    $stm->bindInt("status", PlayerConstants::PENDING);
    $stm->bindStr("referrer", $user->referrer);
    $stm->execute();

    $newPlayerId = $this->db->lastInsertId();

    $stm = $this->db->prepare("INSERT INTO message_seen SELECT :playerId, m.id FROM messages m");
    $stm->bindInt("playerId", $newPlayerId);
    $stm->execute();

    $user->add($newPlayerId);

    // update the player with values calculated during $user->add. It should be refactored in the future
    $stm = $this->db->prepare("UPDATE players SET lastlogin = :lastLogin, refplayer = :refPlayer WHERE id = :playerId");
    $stm->bindStr("lastLogin", $user->ipinfo);
    $stm->bindInt("refPlayer", $user->refPlayerId);
    $stm->bindInt("playerId", $newPlayerId);
    $stm->execute();

    $newPlayerNotification = TagBuilder::forText("<CANTR REPLACE NAME=message_new_player>")
      ->build()->interpret();
    $messageManager = new MessageManager(Db::get());
    $messageManager->sendMessage(MessageManager::PQUEUE_SYSTEM_MESSAGE, $newPlayerId, $newPlayerNotification, 1);

    // security management - make life of trolls harder
    $newPlayer = (object)["id" => $newPlayerId, "ipinfo" => $user->ipinfo, "email" => $user->email];

    $securityManager = new RegistrationSecurityManager($newPlayer); // todo
    $securityManager->performSecurityMeasures();
    if ($env->introExists()) {
      $introDb = $env->getDbNameFor("intro");
      $stm = $this->db->prepare("INSERT INTO `$introDb`.players SELECT * FROM players WHERE id = :playerId");
      $stm->bindInt("playerId", $newPlayerId);
      $stm->execute();
      $this->createIntroCharacter($user, $newPlayerId, $day);
    }
  }

  /**
   * @param User $user
   * @param $newPlayerId
   */
  private function createIntroCharacter(User $user, $newPlayerId, $day)
  {
    $sex = $user->sex1;

    $introDb = Db::get("intro");
    $introCharCreator = CharacterCreator::forPlayerId($newPlayerId, $introDb);
    if ($introCharCreator->validate($user->charname1, $sex, $user->language)) {
      $testCharId = $introCharCreator->create($user->charname1, $sex, $user->language);
      $this->makeIntroCharKnownToMentors($testCharId, $user->language, $day, $introDb);
    } else {
      Logger::getLogger(__CLASS__)->error("Unable to create character on intro server with parameters:
        name: '$user->charname1', sex: '$sex', language: '$user->language' for player $newPlayerId, reason: " . $introCharCreator->getError());
    }
  }

  private function makeIntroCharKnownToMentors($testCharId, $language, $day, Db $introDb)
  {
    $stm = $introDb->prepare("SELECT id FROM chars WHERE register < :minimumMentorAgeInDays AND status = :active");
    $stm->bindInt("minimumMentorAgeInDays", $day - 400);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();
    $mentorIds = $stm->fetchScalars();

    $stm = $introDb->prepare("INSERT INTO charnaming (observer, observed, name, type, description)
      VALUES (:observer, :observed, :name, :type, '')");
    foreach ($mentorIds as $mentorId) {
      $stm->bindInt("observer", $mentorId);
      $stm->bindInt("observed", $testCharId);
      $stm->bindStr("name", "Newbie - " . LanguageConstants::$LANGUAGE[$language]["en_name"]);
      $stm->bindInt("type", NamingConstants::TYPE_CHAR);
      $stm->execute();
    }
  }
}
