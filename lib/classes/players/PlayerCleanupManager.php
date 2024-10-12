<?php

class PlayerCleanupManager
{
  const WARN_DAYS_BEFORE_REMOVAL = 4;

  /**
   * @var Db
   */
  private $db;
  /**
   * @var MailService
   */
  private $mailService;
  /**
   * @var Environment
   */
  private $environment;

  public function __construct(Db $db, MailService $mailService, Environment $environment)
  {
    $this->db = $db;
    $this->mailService = $mailService;
    $this->environment = $environment;
  }

  public function processAll($removalDays, $newPlayerRemovalDays, $newPlayerReminderDays)
  {
    $currentDay = GameDate::NOW();

    $this->processPlayers($currentDay, $removalDays);
    $this->processNewPlayers($currentDay, $newPlayerRemovalDays);

    $this->updateOnLeaveStatus($currentDay);
    $this->sendRemindersToNewPlayers($currentDay, $newPlayerReminderDays);
  }

  private function remindNewPlayer(Player $playerInfo)
  {
    $oneTimePassword = SecurityUtil::generateNewRandomPassword();
    $oneTimePasswordHash = SecurityUtil::generatePasswordHash($oneTimePassword);
    $stm = $this->db->prepare("INSERT INTO onetime_passwords (player, password)
      VALUES (:id, :passwordHash)");
    $stm->bindInt("id", $playerInfo->getId());
    $stm->bindStr("passwordHash", $oneTimePasswordHash);

    $domainUrl = $this->environment->getConfig()->domainUrl();
    $loginLink = urlencode($domainUrl . "/index.php?page=login&data=yes" .
      "&id=" . $playerInfo->getId() . "&password=" . $oneTimePassword . "&onetime=1");
    $tag = new Tag();
    $tag->content = "<CANTR REPLACE NAME=mail_new_player_reminder CODE=$oneTimePassword LOGIN_LINK=" . $loginLink . ">";
    $tag->language = $playerInfo->getLanguage();
    $reminderMsg = $tag->interpret();

    $tag->content = "<CANTR REPLACE NAME=mail_new_player_reminder_title>";
    $reminderTitle = $tag->interpret();

    $this->mailService->send($playerInfo->getEmail(), $reminderTitle, $reminderMsg);

    $playerInfo->saveInDb();
    Report::saveInPcStatistics('pwarned', $playerInfo->getId());
  }

  private function removeCharactersFromIntro(Player $removedPlayer, Db $introDb)
  {
    $stm = $introDb->prepare("SELECT id, location FROM chars WHERE player = :playerId
       AND status IN (:pending, :active)");
    $stm->bindInt("playerId", $removedPlayer->getId());
    $stm->bindInt("pending", CharacterConstants::CHAR_PENDING);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();
    foreach ($stm->fetchAll() as $charToRemove) {
      $stm = $introDb->prepare("UPDATE `objects` SET location = :location, person = 0
        WHERE person = :charId"); // should be replaced by CharacterDeath::drop_everything_unweared, but now it's impossible to use a different db
      $stm->bindInt("location", $charToRemove->location);
      $stm->bindInt("charId", $charToRemove->id);
      $stm->execute();
      $stm = $introDb->prepare("UPDATE `chars` SET status = :status WHERE id = :charId");
      $stm->bindInt("status", CharacterConstants::CHAR_DECEASED);
      $stm->bindInt("charId", $charToRemove->id);
      $stm->execute();
    }
  }

  private function idleoutPlayer(Player $playerInfo)
  {
    $stm = $this->db->prepare("SELECT council, status FROM assignments WHERE player = :id");
    $stm->bindInt("id", $playerInfo->getId());
    $stm->execute();
    $importantStaffMember = Pipe::from($stm->fetchAll())
        ->filter(function($assignment) {
          return $assignment->council == _COUNCIL_GAB || $assignment->status == _ASSIGN_CHAIR;
        })->count() > 0;

    if ($importantStaffMember) {
      $message = "Idling out of player {$playerInfo->getFullNameWithId()}) blocked due to staff assignment.";
      Report::saveInDb("personnel", $message);
      return;
    }
    foreach ($playerInfo->getAliveCharacters() as $charToDie) {
      $charToDie->dieCharacter(CharacterConstants::CHAR_DEATH_EXPIRED, 0, true);
      $charToDie->saveInDb();
    }

    if ($this->environment->introExists()) {
      $this->removeCharactersFromIntro($playerInfo, Db::get("intro"));
    }

    $playerInfo->setStatus(PlayerConstants::IDLEDOUT);

    $message = "The player {$playerInfo->getFullNameWithId()} idled out.";
    Report::saveInPlayerReport($message);

    $tag = new Tag();
    $tag->content = "<CANTR REPLACE NAME=mail_idleout>";
    $tag->language = $playerInfo->getLanguage();
    $idleoutMsg = $tag->interpret();

    $tag->content = "<CANTR REPLACE NAME=mail_idleout_title>";
    $idleoutTitle = $tag->interpret();

    $this->mailService->send($playerInfo->getEmail(), $idleoutTitle, $idleoutMsg);

    $playerInfo->saveInDb();
    Report::saveInPcStatistics("pidledout", $playerInfo->getId());
  }

  private function warnPlayer(Player $playerInfo, $daysSinceLastLogin)
  {
    $charNames = Pipe::from($playerInfo->getAliveCharacters())
      ->map(function(Character $char) {
        return $char->getName();
      })->toArray();
    $charNames = implode(", ", $charNames);

    $warnMsg = "<CANTR REPLACE NAME=mail_idleout_warn CHARS=" . urlencode($charNames)
      . " IDLE_DAYS=" . $daysSinceLastLogin . ">";
    $tag = new Tag();
    $tag->content = $warnMsg;
    $tag->language = $playerInfo->getLanguage();
    $warnMsg = $tag->interpret();

    $tag->content = "<CANTR REPLACE NAME=mail_idleout_warn_title>";
    $warnTitle = $tag->interpret();
    $this->mailService->send($playerInfo->getEmail(), $warnTitle, $warnMsg);

    Report::saveInPcStatistics("pwarned", $playerInfo->getId());
  }

  /**
   * Sends emails with login reminder for players who never logged in after the day of their registration
   * @param GameDate $currentDate
   * @param $remindDays int days of inactivity after which player gets a reminder
   * @throws Exception
   */
  private function sendRemindersToNewPlayers(GameDate $currentDate, $remindDays)
  {
    $stm = $this->db->prepareWithIntList("SELECT id FROM players WHERE status IN (:statuses)
      AND register = lastdate AND lastdate = :lastDate", [
      "statuses" => [PlayerConstants::PENDING, PlayerConstants::APPROVED, PlayerConstants::ACTIVE],
    ]);
    $stm->bindInt("lastDate", $currentDate->getDay() - $remindDays);
    $stm->execute();
    $players = $this->mapIdsToPlayers($stm->fetchScalars());
    foreach ($players as $newPlayer) {
      try {
        $this->remindNewPlayer($newPlayer);
      } catch (InvalidArgumentException $e) {
        Logger::getLogger("server.cleanup", "Unable to send reminder to account $newPlayerId");
      }
    }
  }

  /**
   * @param $currentDate
   */
  private function updateOnLeaveStatus(GameDate $currentDate)
  {
    $stm = $this->db->prepare("UPDATE players SET lastdate = :day WHERE onleave = 1");
    $stm->bindInt("day", $currentDate->getDay());
    $stm->execute();
    $this->db->query("UPDATE players SET onleave = onleave - 1 WHERE onleave > 0");
  }

  /**
   * @param $players Player[]
   * @param $currentDate
   * @param $warnDays
   * @param $removalDays
   */
  private function processPlayerCleanup($players, GameDate $currentDate, $warnDays, $removalDays)
  {
    foreach ($players as $playerInfo) {
      $daysSinceLastLogin = $currentDate->minus($playerInfo->getLastGameDate())->getDay();
      if ($daysSinceLastLogin >= $removalDays) {
        print "Player {$playerInfo->getFullNameWithId()}: will be removed\n";
        $this->idleoutPlayer($playerInfo);
      } elseif ($daysSinceLastLogin >= $warnDays) {
        print "Player {$playerInfo->getFullNameWithId()} ($daysSinceLastLogin): will be warned\n";
        $this->warnPlayer($playerInfo, $daysSinceLastLogin);
      }
    }
  }

  /**
   * @param $playerIds int[]
   * @return Player[]
   */
  private function mapIdsToPlayers($playerIds)
  {
    return array_map(function($playerId) {
      return Player::loadById($playerId);
    }, $playerIds);
  }

  /**
   * @param GameDate $currentDate
   * @param $removalDays int
   */
  protected function processPlayers(GameDate $currentDate, $removalDays)
  {
    $warnDays = $removalDays - self::WARN_DAYS_BEFORE_REMOVAL;
    $stm = $this->db->prepare("SELECT * FROM players WHERE (players.lastdate  > :warnDay)
      AND (players.onleave = 0 OR players.onleave IS NULL)  AND `status` IN (:active, :approved) ORDER BY lastdate");
    $stm->bindInt("approved", PlayerConstants::APPROVED);
    $stm->bindInt("active", PlayerConstants::ACTIVE);
    $stm->bindInt("warnDay", $warnDays);
    $stm->execute();
    $players = $this->mapIdsToPlayers($stm->fetchScalars());
    $this->processPlayerCleanup($players, $currentDate, $warnDays, $removalDays);
  }

  /**
   * @param GameDate $currentDate
   * @param $newPlayerRemovalDays int
   */
  protected function processNewPlayers(GameDate $currentDate, $newPlayerRemovalDays)
  {
    $newPlayerWarnDays = $newPlayerRemovalDays - self::WARN_DAYS_BEFORE_REMOVAL;
    $stm = $this->db->prepare("SELECT * FROM players WHERE 
        (lastdate > :warnDay) AND lastdate = register
        AND (players.onleave = 0 OR players.onleave IS NULL)  AND `status` IN (:active, :approved) ORDER BY lastdate");
    $stm->bindInt("approved", PlayerConstants::APPROVED);
    $stm->bindInt("active", PlayerConstants::ACTIVE);
    $stm->bindInt("warnDay", $newPlayerWarnDays);
    $stm->execute();
    $players = $this->mapIdsToPlayers($stm->fetchScalars());
    $this->processPlayerCleanup($players, $currentDate, $newPlayerWarnDays, $newPlayerRemovalDays);
  }
}