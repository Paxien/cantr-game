<?php

include_once(_LIB_LOC . "/func.expireobject.inc.php");

class CharacterDeath
{
  /**
   * @var Character
   */
  private $char;
  /** @var Db */
  private $db;

  public function __construct(Character $char, Db $db)
  {
    $this->char = $char;
    $this->db = $db;
  }

  /**
   * Kills the character and optionally displays death event.
   * $this->char->saveInDb() should be run to update chars table in the database.
   * @param $cause int One of constants in CharacterConstants::CHAR_DEATH_*
   * @param $weapon int if CHAR_DEATH_VIOLENCE, then weapon type,
   *                    if CHAR_DEATH_ANIMAL then animal type,
   *                    if CHAR_DEATH_EXPIRED then 0 is idleout, 1 is voluntary death of old age
   * @param $create_event bool Should death event be created
   */

  function dieCharacter($cause, $weapon, $create_event)
  {
    $wasAlive = $this->char->isAlive(); // it can be pending
    if ($create_event && $wasAlive) {
      Event::create(188, "ACTOR=" . $this->char->getId())
        ->nearCharacter($this->char)->andAdjacentLocations()->except($this->char)->show();
    }

    if ($this->char->isTravelling()) {
      $this->moveToTheCloserLocation();
    }

    $currentDate = GameDate::NOW();
    $this->char->setStatus(CharacterConstants::CHAR_DECEASED);
    $this->char->setProject(0);
    $this->char->setDeathCause($cause);
    $this->char->setDeathWeapon($weapon);
    $this->char->setDeathDate($currentDate->getDay());

    $bodyAlreadyExists = CObject::locatedIn($this->char->getLocation())
      ->type(ObjectConstants::TYPE_DEAD_BODY)
      ->typeid($this->char->getId())
      ->exists();

    if (!$bodyAlreadyExists && $wasAlive) {
      ObjectCreator::inLocation($this->char->getLocation(), ObjectConstants::TYPE_DEAD_BODY,
        ObjectConstants::SETTING_PORTABLE, 60000)
        ->typeid($this->char->getId())->create();
    }

    $playerOfCharacter = $this->char->getPlayerObject();

    $encodedName = urlencode($this->char->getName());
    $deathNotification = TagBuilder::forText("<CANTR REPLACE NAME=notification_char_death NAME=$encodedName>")
      ->build()->interpret();
    $messageManager = new MessageManager(Db::get());
    $messageManager->sendMessage(MessageManager::PQUEUE_SYSTEM_MESSAGE, $playerOfCharacter->getId(), $deathNotification, 1);

    $this->dropEverythingExceptClothes();

    // REMOVE DRAGGING TRIES WHERE CHAR IS ONLY PARTICIPANT OR VICTIM
    try {
      $dragging = Dragging::loadByVictim(DraggingConstants::TYPE_HUMAN, $this->char->getId());
      $dragging->remove();
      $dragging->saveInDb();
    } catch (InvalidArgumentException $e) {
    }

    try {
      $dragging = Dragging::loadByDragger($this->char->getId());
      $dragging->removeDragger($this->char->getId());
      $dragging->saveInDb();
    } catch (InvalidArgumentException $e) {
    }

    // *** REMOVE ENTRY FROM NEWEVENTS TABLE
    $stm = $this->db->prepare("DELETE FROM newevents WHERE person = :charId");
    $stm->bindInt("charId", $this->char->getId());
    $stm->execute();

    // *** LOG ACTION

    Report::saveInPcStatistics("cdied", $this->char->getId());

    $killed_by_pd = "";
    if ($cause == CharacterConstants::CHAR_DEATH_PD && $weapon > 0) {
      try {
        $pdMember = Player::loadById($weapon);
        $killed_by_pd = " (killed by PD member: " . $pdMember->getFullName() . " (ID: " . $pdMember->getId() . ")) ";
      } catch (InvalidArgumentException $e) {
        $killed_by_pd = " (killed by uknown PD member";
      }
    }

    $messageForPd = "The character " . $this->char->getName() . " of " . $playerOfCharacter->getFullNameWithId() . " died. $killed_by_pd";
    Report::saveInPlayerReport($messageForPd);

    $messageInEmail = "$deathNotification\n";
    $messageInEmail .= TagBuilder::forTag("email_character_death_info")->build()->interpret();
    $messageInEmail .= "\n\n";

    $eventList = new EventListView($this->char, false);
    $events = $eventList->interpret(0, -1, false);
    $messageInEmail .= implode("\n", $events);

    $mailService = new MailService("Cantr Accounts", $GLOBALS['emailSupport']);
    $mailService->sendPlaintext($playerOfCharacter->getEmail(), "Cantr II: $deathNotification", $messageInEmail);

    // REMOVE DISEASE ENTRIES
    $stm = $this->db->prepare("DELETE FROM diseases WHERE person = :charId");
    $stm->bindInt("charId", $this->char->getId());
    $stm->execute();
  }

  public function intoNearDeath($cause, $weapon)
  {

    if ($this->char->isTravelling()) {
      $this->moveToTheCloserLocation();
    }

    try {
      $dragging = Dragging::loadByDragger($this->char->getId());
      $dragging->removeDragger($this->char->getId());
      $dragging->saveInDb();
    } catch (InvalidArgumentException $e) {
    }

    $this->dropEverythingExceptClothes();

    CharacterStomach::ofCharacter($this->char)->purge();

    $this->char->setProject(0);
    $this->char->setDeathCause($cause);
    $this->char->setDeathWeapon($weapon);
    $this->char->setState(StateConstants::HEALTH, 1);
    $this->char->saveInDb();

    $currentDate = GameDate::NOW();
    $deathDay = $currentDate->getDay() + CharacterConstants::NEAR_DEATH_DAYS;
    $stm = $this->db->prepare("INSERT INTO char_near_death (char_id, state, day, hour)
      VALUES (:charId, :state, :day, :hour)");
    $stm->bindInt("charId", $this->char->getId());
    $stm->bindInt("state", CharacterConstants::NEAR_DEATH_NOT_HEALED);
    $stm->bindInt("day", $deathDay);
    $stm->bindInt("hour", $currentDate->getHour());
    $stm->execute();
  }

  public function dropEverythingExceptClothes()
  {
    $stm = $this->db->prepare("SELECT obj.* FROM objects obj
      INNER JOIN objecttypes ot ON ot.id = obj.type
      INNER JOIN objectcategories oc ON oc.id = ot.objectcategory
      WHERE obj.person = :charId
        AND (( oc.parent IS NULL OR oc.parent != :clothesCategory)
        OR ( obj.specifics IS NULL OR obj.specifics NOT LIKE '%wearing:1%' ))
    ");
    $stm->bindInt("charId", $this->char->getId());
    $stm->bindInt("clothesCategory", ObjectConstants::OBJCAT_CLOTHES);
    $stm->execute();

    foreach ($stm->fetchAll() as $object_info_source) {
      if ($object_info_source->setting == ObjectConstants::SETTING_QUANTITY) {
        // move resource to the location
        if ($object_info_source->type == ObjectConstants::TYPE_RAW) {
          ObjectHandler::rawToLocation($this->char->getLocation(),
            $object_info_source->typeid, $object_info_source->weight);
        } else { // coin
          ObjectHandler::coinsToLocation($this->char->getLocation(), $object_info_source->type,
            $object_info_source->specifics, $object_info_source->weight / ObjectConstants::WEIGHT_COIN);
        }
        expire_object($object_info_source->id);
      } else {
        $stm = $this->db->prepare("UPDATE objects SET location = :locationId, person = 0 WHERE id = :objectId");
        $stm->bindInt("locationId", $this->char->getLocation());
        $stm->bindInt("objectId", $object_info_source->id);
        $stm->execute();
      }
    }

    $stm = $this->db->prepare("SELECT obj.id, ot.project_weight AS type_weight, obj.specifics FROM objects obj
      INNER JOIN objecttypes ot ON ot.id = obj.type
      WHERE obj.person = :charId
        AND EXISTS (SELECT * FROM obj_properties op WHERE op.objecttype_id = obj.type AND op.property_type = 'Storage')
        AND obj.specifics LIKE '%wearing:1%'
    ");
    $stm->bindInt("charId", $this->char->getId());
    $stm->execute();

    foreach ($stm->fetchAll() as $worn_storage) {
      $newSpecifics = str_replace("wearing:1", "wearing:0", $worn_storage->specifics);
      $stm = $this->db->prepare("UPDATE objects SET location = :locationId,
        person = 0, specifics = :specifics, weight = weight + :weightChange
        WHERE id = :objectId");
      $stm->bindInt("locationId", $this->char->getLocation());
      $stm->bindStr("specifics", $newSpecifics);
      $stm->bindInt("weightChange", $worn_storage->type_weight);
      $stm->bindInt("objectId", $worn_storage->id);
      $stm->execute();
    }
  }

  protected function moveToTheCloserLocation()
  {
    $travel = Travel::loadByParticipant($this->char);
    $isTheOnlyParticipantOfTravel = count($travel->getParticipatingCharacterIds()) === 1;
    if ($isTheOnlyParticipantOfTravel) {
      $travel->moveToTheCloserLocation();
      if ($travel->isVehicle()) {
        $this->char->setLocation($travel->getVehicle()->getRegion());
      }
    }
    $travel->saveInDb();
  }
}
