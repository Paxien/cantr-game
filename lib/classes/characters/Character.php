<?php

class Character
{
  /**
   * @var DbObjectRegistry registry for cached character objects
   */
  private static $registry;

  // Private members mapping to database 'chars' table.
  private $id = 0;
  private $player = 0;
  private $language = 0;
  private $name = "";
  private $sex = 0;
  private $location = 0;
  private $register = 0;
  private $spawning_location = 0;
  private $spawning_age = 0;
  private $lastdate = 0;
  private $lasttime = 0;
  private $death_cause = 0;
  private $death_weapon = 0;
  private $death_date = 0;
  private $project = 0;
  private $activity = 0;
  private $description = "";
  private $status = 0;
  private $newbie = 0;
  private $custom_desc = "";
  /** @var Db */
  private $db;
  /** @var Logger */
  private $logger;

  public static function staticInit()
  {
    self::$registry = new DbObjectRegistry();
  }

  // object should be constructed through static factory method
  private function __construct()
  {
    $this->db = Db::get();
    $this->logger = Logger::getLogger(__FILE__);
  }

  /**
   * @param $characterId
   * @return Character object defined by its ID
   */
  public static function loadById($characterId, $db = null)
  {
    if (self::$registry->contains($characterId)) {
      return self::$registry->get($characterId);
    }

    if ($db === null) {
      $db = Db::get();
    }

    $stm = $db->prepare("SELECT * FROM chars WHERE id = :charId LIMIT 1");
    $stm->bindInt("charId", $characterId);
    $stm->execute();
    if ($charInfo = $stm->fetchObject()) {
      $obj = self::loadFromFetchObject($charInfo);
      self::$registry->put($characterId, $obj);
      return $obj;
    }
    throw new InvalidArgumentException("Character $characterId doesn't exist");
  }

  public static function loadFromFetchObject($mysqlRow)
  {
    $char = new Character();
    $char->setID($mysqlRow->id);
    $char->setPlayer($mysqlRow->player);
    $char->setLanguage($mysqlRow->language);
    $char->setName($mysqlRow->name);
    $char->setSex($mysqlRow->sex);
    $char->setLocation($mysqlRow->location);
    $char->setRegister($mysqlRow->register);
    $char->setSpawningLocation($mysqlRow->spawning_location);
    $char->setSpawningAge($mysqlRow->spawning_age);
    $char->setLastDate($mysqlRow->lastdate);
    $char->setLastTime($mysqlRow->lasttime);
    $char->setDeathCause($mysqlRow->death_cause);
    $char->setDeathWeapon($mysqlRow->death_weapon);
    $char->setDeathDate($mysqlRow->death_date);
    $char->setProject($mysqlRow->project);
    $char->setActivity($mysqlRow->activity);
    $char->setDescription($mysqlRow->description);
    $char->setStatus($mysqlRow->status);
    $char->setNewbie($mysqlRow->newbie);
    $char->setCustomDesc($mysqlRow->custom_desc);
    return $char;
  }

  // Getters and setters
  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = intval($id);
  }

  public function getPlayer()
  {
    return $this->player;
  }

  public function setPlayer($playerId)
  {
    $this->player = intval($playerId);
  }

  public function getLanguage()
  {
    return $this->language;
  }

  public function setLanguage($languageId)
  {
    $this->language = intval($languageId);
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getSex()
  {
    return $this->sex;
  }

  public function setSex($sexId)
  {
    $this->sex = intval($sexId);
  }

  public function getLocation()
  {
    return $this->location;
  }

  public function setLocation($locationId)
  {
    $this->location = intval($locationId);
  }

  public function getRegister()
  {
    return $this->register;
  }

  public function setRegister($register)
  {
    $this->register = intval($register);
  }

  public function getSpawningLocation()
  {
    return $this->spawning_location;
  }

  public function setSpawningLocation($spawningLocation)
  {
    $this->spawning_location = intval($spawningLocation);
  }

  public function getSpawningAge()
  {
    return $this->spawning_age;
  }

  public function setSpawningAge($spawningAge)
  {
    intval($this->spawning_age = $spawningAge);
  }

  public function getLastDate()
  {
    return $this->lastdate;
  }

  public function setLastDate($lastDate)
  {
    $this->lastdate = intval($lastDate);
  }

  public function getLastTime()
  {
    return $this->lasttime;
  }

  public function setLastTime($lastTime)
  {
    $this->lasttime = intval($lastTime);
  }

  public function getDeathCause()
  {
    return $this->death_cause;
  }

  public function setDeathCause($deathCause)
  {
    $this->death_cause = intval($deathCause);
  }

  public function getDeathWeapon()
  {
    return $this->death_weapon;
  }

  public function setDeathWeapon($deathWeapon)
  {
    $this->death_weapon = intval($deathWeapon);
  }

  public function getDeathDate()
  {
    return $this->death_date;
  }

  public function setDeathDate($deathDate)
  {
    $this->death_date = intval($deathDate);
  }

  public function getProject()
  {
    return $this->project;
  }

  public function setProject($projectId)
  {
    $this->project = intval($projectId);
  }

  public function getActivity()
  {
    return $this->activity;
  }

  public function setActivity($activity)
  {
    $this->activity = intval($activity);
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function setDescription($description)
  {
    $this->description = $description;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function setStatus($status)
  {
    $this->status = intval($status);
  }

  public function getNewbie()
  {
    return $this->newbie;
  }

  public function setNewbie($newbie)
  {
    $this->newbie = intval($newbie);
  }

  public function getCustomDesc()
  {
    return $this->custom_desc;
  }

  public function setCustomDesc($customDesc)
  {
    $this->custom_desc = $customDesc;
  }

  public function getPlayerObject()
  {
    return Player::loadById($this->player);
  }

  public function isMale()
  {
    return $this->getSex() == CharacterConstants::SEX_MALE;
  }

  public function updateLastDateAndTime(GameDate $date)
  {
    $this->setLastDate($date->getDay());
    $this->setLastTime($date->getHour());
  }

  /**
   * Days since spawnday
   */
  public function getAgeInDays()
  {
    return GameDate::NOW()->getDay() - $this->getRegister();
  }

  public function getAgeInYears()
  {
    return floor($this->getAgeInDays() / GameDateConstants::DAYS_PER_YEAR) + $this->getSpawningAge();
  }

  // If the character is dead, this will return the number of days an eventual block is in effect
  public function getSlotBlockedDays($turn)
  {

    if (($this->status > CharacterConstants::CHAR_ACTIVE)
      and ($this->register >= ($turn - CharacterConstants::DEAD_CLOSE_SLOT_AGE))
      and ($this->getDeathDate() >= ($turn - CharacterConstants::DEAD_CLOSE_SLOT_DAYS))) {

      return max(min((CharacterConstants::DEAD_CLOSE_SLOT_DAYS - ($turn - $this->getDeathDate())),
        (CharacterConstants::DEAD_CLOSE_SLOT_AGE - ($turn - $this->getRegister()))), 0);
    }
    return 0;
  }

  public function isTravelling()
  {
    $charLocation = new char_location($this->id);
    return $charLocation->istravelling;
  }

  /**
   * Returns position of character
   * @return array coordinates with keys "x", "y"
   */
  public function getPos()
  {
    if ($this->isTravelling()) { // if travelling then possible to get pos from travel data
      try {
        $travel = Travel::loadByParticipant($this);
      } catch (InvalidArgumentException $e) {
        throw new InvalidArgumentException("Char is travelling but impossible to init travel data", 0, $e);
      }
      return $travel->getPos();
    } else { // else then get pos from location data
      $stm = $this->db->prepare("SELECT x, y FROM locations WHERE id = :locationId");
      $stm->bindInt("locationId", $this->location);
      $stm->execute();
      $pair = $stm->fetchObject();
      return array("x" => $pair->x, "y" => $pair->y);
    }
  }

  /**
   * Returns total weight of items carried in inventory
   */

  public function getInventoryWeight()
  {
    $stm = $this->db->prepare("SELECT SUM(weight) FROM objects WHERE person = :charId");
    $stm->bindInt("charId", $this->id);
    $weight = $stm->executeScalar();
    $weight = ($weight != null) ? $weight : 0;
    return $weight;
  }

  public function getState($stateId)
  {
    import_lib("func.genes.inc.php");
    return read_state($this->getId(), intval($stateId));
  }

  public function alterState($stateId, $byValue)
  {
    import_lib("func.genes.inc.php");
    if (!Validation::isInt($byValue)) {
      $this->logger->warn("Altering state $stateId: $byValue is not an int");
    }
    alter_state($this->getId(), intval($stateId), intval($byValue));
  }

  public function setState($stateId, $value)
  {
    import_lib("func.genes.inc.php");
    set_state($this->getId(), intval($stateId), intval($value));
  }

  public function isDragging()
  {
    $stm = $this->db->prepare("SELECT COUNT(*) FROM draggers WHERE dragger = :charId");
    $stm->bindInt("charId", $this->id);
    $dragging = $stm->executeScalar();
    return $dragging > 0;
  }

  public function isBusy()
  {
    return ($this->getProject() > 0) || $this->isDragging();
  }

  public function hasWithinReach(CObject $object)
  {
    return $this->hasInInventory($object) || $this->isInSameLocationAs($object);
  }

  public function hasInInventory(CObject $object)
  {
    return ($object->getPerson() == $this->getId());
  }

  public function isInSameLocationAs($anything)
  {
    // never true when wandering
    return ($this->getLocation() > 0) && ($anything->getLocation() == $this->getLocation());
  }

  public function getTotalWeight()
  {
    return CharacterConstants::BODY_WEIGHT + $this->getInventoryWeight();
  }

  public function getMaxInventoryWeight()
  {
    return CharacterConstants::INVENTORY_WEIGHT_MAX;
  }

  public function activateSpawnedCharacter()
  {
    if (!$this->isPending()) {
      return;
    }

    $location = $this->location;
    $projCount = Project::locatedIn($location)->count();
    $stm = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE location = :locationId1 and id IN
      (SELECT project FROM chars WHERE location = :locationId2 AND status = :active
        AND project !='' AND project IS NOT NULL)");
    $stm->bindInt("locationId1", $location);
    $stm->bindInt("locationId2", $location);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $actProjCount = $stm->executeScalar();
    Event::createPersonalEvent(191, "NUMPROJECTS=$projCount NUMACTIVE=$actProjCount", $this->id);

    $stm = $this->db->prepare("SELECT COUNT(*) FROM objects WHERE location = :locationId");
    $stm->bindInt("locationId", $location);
    $objCount = $stm->executeScalar();

    $stm = $this->db->prepare("SELECT COUNT(*) FROM objects WHERE location = :locationId AND type = :type");
    $stm->bindInt("locationId", $location);
    $stm->bindInt("type", ObjectConstants::TYPE_NOTE);
    $noteCount = $stm->executeScalar();
    Event::createPersonalEvent(192, "NUMOBJECTS=$objCount NUMNOTES=$noteCount", $this->id);

    $stm = $this->db->prepare("SELECT COUNT(*) FROM chars WHERE status = :active AND location = :locationId");
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->bindInt("locationId", $location);
    $charCount = $stm->executeScalar();
    Event::createPersonalEvent(193, "NUMCHARS=$charCount", $this->id);

    $stm = $this->db->prepare("SELECT COUNT(*) FROM locations WHERE type IN (2, 3) and region = :locationId");
    $stm->bindInt("locationId", $location);
    $sublocCount = $stm->executeScalar();
    Event::createPersonalEvent(194, "NUMBUILDINGS=$sublocCount", $this->id);
    Event::createPersonalEvent(195, "", $this->id);

    $stm = $this->db->prepare("SELECT COUNT(*) FROM raws WHERE location = :locationId");
    $stm->bindInt("locationId", $location);
    $rawCount = $stm->executeScalar();

    $stm = $this->db->prepare("SELECT COUNT(*) FROM animals WHERE location = :locationId");
    $stm->bindInt("locationId", $location);
    $animalCount = $stm->executeScalar();
    Event::createPersonalEvent(196, "LOCATION=$location NUMRAW=$rawCount NUMANIMALS=$animalCount", $this->id);

    $stm = $this->db->prepare("SELECT count(*) FROM chars WHERE player = :playerId");
    $stm->bindInt("playerId", $this->player);
    $charsCount = $stm->executeScalar();
    if ($charsCount < 3) {
      Event::createPersonalEvent(197, "", $this->id);
    }
    Event::createPublicEvent(198, "ACTOR=$this->id", $this->id, Event::RANGE_NEAR_LOCATIONS, array($this->id));

    // change character status
    $this->status = CharacterConstants::CHAR_ACTIVE;
  }

  public function isAlive()
  {
    return $this->status == CharacterConstants::CHAR_ACTIVE;
  }

  public function isPending()
  {
    return $this->status == CharacterConstants::CHAR_PENDING;
  }

  /**
   * Check if character is seeing another character, including the situation when both are travelling.
   * @param Character $otherChar
   * @param bool $strict true if should disallow seeing through an open window
   * @return bool is near
   */
  public function isNearTo(Character $otherChar, $strict = false)
  {
    $charLoc = new char_location($this->getId());
    return $charLoc->char_isnear($otherChar->getId(), _PEOPLE_NEAR, $strict);
  }

  public function isNearDeath()
  {
    return CharacterHandler::isNearDeath($this->id);
  }

  public function getNearDeathState()
  {
    return CharacterHandler::getNearDeathState($this->id);
  }

  public function hasPassedOut()
  {
    return $this->getState(StateConstants::DRUNKENNESS) >= CharacterConstants::PASSOUT_LIMIT;
  }

  public function dieCharacter($cause, $weapon, $createEvent)
  {
    $char = new CharacterDeath($this, $this->db);
    $char->dieCharacter($cause, $weapon, $createEvent);
  }

  public function intoNearDeath($cause, $weapon)
  {
    $char = new CharacterDeath($this, $this->db);
    $char->intoNearDeath($cause, $weapon);
  }

  public function dropEverythingExceptClothes()
  {
    $char = new CharacterDeath($this, $this->db);
    $char->dropEverythingExceptClothes();
  }

  /**
   * Char data can only be updated, because chars are never deleted
   * and character creation is handled by class Char
   */
  public function saveInDb()
  {
    $stm = $this->db->prepare("
    UPDATE chars SET player = :playerId, language = :language,
      name = :name, sex = :sex, location = :locationId,
      register = :register, spawning_location = :spawningLocation, spawning_age = :spawningAge,
      lastdate = :lastDate, lasttime = :lastTime,
      death_cause = :deathCause, death_weapon = :deathWeapon, death_date = :deathDate,
      project = :project, activity = :activity, description = :description, status = :status,
      newbie = :newbie, custom_desc = :customDesc
    WHERE id = :charId");
    $stm->bindInt("playerId", $this->player);
    $stm->bindInt("language", $this->language);
    $stm->bindStr("name", $this->name);
    $stm->bindInt("sex", $this->sex);
    $stm->bindInt("locationId", $this->location);
    $stm->bindInt("register", $this->register);
    $stm->bindInt("spawningLocation", $this->spawning_location);
    $stm->bindInt("spawningAge", $this->spawning_age);
    $stm->bindInt("lastDate", $this->lastdate);
    $stm->bindInt("lastTime", $this->lasttime);
    $stm->bindInt("deathCause", $this->death_cause);
    $stm->bindInt("deathWeapon", $this->death_weapon);
    $stm->bindInt("deathDate", $this->death_date);
    $stm->bindInt("project", $this->project);
    $stm->bindInt("activity", $this->activity);
    $stm->bindStr("description", $this->description);
    $stm->bindInt("status", $this->status);
    $stm->bindInt("newbie", $this->newbie);
    $stm->bindStr("customDesc", $this->custom_desc, true);
    $stm->bindInt("charId", $this->id);
    $stm->execute();
  }
}

Character::staticInit();
