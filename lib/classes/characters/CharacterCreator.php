<?php

class CharacterCreator
{
  /** @var Player */
  private $player;
  private $playerId;

  /** @var Character[] */
  private $excludedChars = [];

  /** @var Db */
  private $db;

  /** @var string */
  private $error;
  /** @var GlobalConfig */
  private $globalConfig;

  public static function forPlayer(Player $player, Db $db)
  {
    return new self($player, $player->getId(), $db);
  }

  public static function forPlayerId($playerId, Db $db)
  {
    return new self(null, $playerId, $db);
  }

  private function __construct($player, $playerId, Db $db)
  {
    $this->player = $player;
    $this->playerId = intval($playerId);

    $this->db = $db;
    $this->globalConfig = new GlobalConfig($db);
  }

  public function validate($name, $sex, $language)
  {
    if (!Validation::isPositiveInt($language)) {
      $this->error = "error_incorrect_data";
      return false;
    }

    $stm = $this->db->prepare("SELECT spawning_allowed FROM languages WHERE id = :language");
    $isSpawningAllowed = $stm->executeScalar([":language" => $language]);

    if (!$isSpawningAllowed) {
      $this->error = "error_language_unavailable";
      return false;
    }

    // Check that this player hasn't reached the max number of characters for this language, where the max is the total number of players who have a character in this language.
    $stm = $this->db->prepare("SELECT COUNT(*) FROM chars WHERE player = :player AND status IN (:status_active, :status_pending) AND language = :language");
    $charsOfPlayerInLanguage = $stm->executeScalar(["player" => $this->playerId, "language" => $language, "status_active" => CharacterConstants::CHAR_ACTIVE, ":status_pending" => CharacterConstants::CHAR_PENDING]);

    if ($charsOfPlayerInLanguage >= 2) {
      $stm = $this->db->prepare("SELECT COUNT(DISTINCT(player)) FROM chars WHERE status=:status AND language=:language");
      $playersInLanguage = $stm->executeScalar(["language" => $language, "status" => CharacterConstants::CHAR_ACTIVE]);
      if ($charsOfPlayerInLanguage >= $playersInLanguage) {
        $this->error = "error_max_lang_chars";
        return false;
      }
    }

    if (empty($name)) {
      $this->error = "error_no_name";
      return false;
    }

    if (!in_array($sex, array(CharacterConstants::SEX_MALE, CharacterConstants::SEX_FEMALE))) {
      $this->error = "error_no_sex";
      return false;
    }

    // check only if it's not a new player account
    if ($this->player != null) {
      $slotsBlockedDays = $this->player->getDaysToFreeCharacterSlot();

      if ($slotsBlockedDays == PlayerConstants::NO_CHAR_SLOTS_LEFT) {
        $this->error = "error_max_characters MAX_CHARACTERS={$this->globalConfig->getMaxCharactersPerPlayer()}";
        return false;
      } elseif ($slotsBlockedDays > 0) {
        $this->error = "error_no_free_character_slots NUM_DAYS=$slotsBlockedDays";
        return false;
      }
    }
    return true;
  }

  /**
   * Performs character creation validation and creates a character.
   * @param $name
   * @param $sex
   * @param $language
   * @return string|null character id if character is created, null if it failed
   */
  public function create($name, $sex, $language)
  {
    $date = GameDate::NOW()->getObject();

    if ($sex == CharacterConstants::SEX_MALE) {
      $description = "a man in his twenties";
    } else {
      $description = "a woman in her twenties";
    }

    $livingChars = $this->excludedChars;
    if ($this->player != null) {
      $livingChars = array_merge($livingChars, $this->player->getAliveCharacters());
    }

    $spawnPointGen = new SpawnPointGenerator($livingChars, $this->db);
    $isFirstChar = count($livingChars) == 0;
    $spawnPoint = $spawnPointGen->getRandomSpawnPoint($language, $isFirstChar);

    if (!$this->validate($name, $sex, $language)) {
      return null;
    }

    $stm = $this->db->prepare("INSERT INTO chars
      (player, language, name, description, sex, location,
        register, spawning_location, lastdate, lasttime, project, status) VALUES
      (:playerId, :language, :name, :description, :sex, :location, 
        :registerDay, :spawnLocation, :lastDay, :lastHour, :projectId, :status)");

    $stm->bindInt("playerId", $this->playerId);
    $stm->bindInt("language", $language);
    $stm->bindStr("name", $name);
    $stm->bindStr("description", $description);
    $stm->bindInt("sex", $sex);
    $stm->bindInt("location", $spawnPoint);
    $stm->bindInt("registerDay", $date->day);
    $stm->bindInt("spawnLocation", $spawnPoint);
    $stm->bindInt("lastDay", $date->day);
    $stm->bindInt("lastHour", $date->hour);
    $stm->bindInt("projectId", 0);
    $stm->bindInt("status", CharacterConstants::CHAR_PENDING);
    $stm->execute();
    $charId = $this->db->lastInsertId();

    $stm = $this->db->prepare("INSERT INTO newevents (person, new) VALUES (:charId, :eventStatus)");
    $stm->bindInt("charId", $charId);
    $stm->bindInt("eventStatus", 0);
    $stm->execute();

    // *** LOG ACTION
    $stm = $this->db->prepare("INSERT INTO pcstatistics (action, turn, actiondate, id)
      VALUES (:action, :day, NOW(), :charId)");
    $stm->bindStr("action", "csubscribed");
    $stm->bindInt("day", $date->day);
    $stm->bindInt("charId", $charId);
    $stm->execute();

    // If two random chars can be found on this location, inherit the genes.
    // If not, no gene will be generated, and will be randomly generated whenever used.
    $stm = $this->db->prepare("SELECT id FROM chars
      WHERE location = :spawnLocation AND id != :yourself ORDER BY RAND() LIMIT 2");
    $stm->bindInt("spawnLocation", $spawnPoint);
    $stm->bindInt("yourself", $charId);
    $stm->execute();
    $parents = $stm->fetchScalars();

    if (count($parents) == 2) {
      $genes = new CharacterGenes($charId, $this->db);
      $genes->inheritFrom($parents[0], $parents[1]);
    }

    return $charId;
  }

  public function getError()
  {
    return $this->error;
  }
}
