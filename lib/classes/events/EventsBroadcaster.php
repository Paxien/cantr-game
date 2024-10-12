<?php

/**
 * @author Wiktor Obrębski
 */
class EventsBroadcaster
{
  const MODE_CHARACTER = 'character';
  const MODE_LOCATION = 'location';
  const MODE_LANGUAGE_GROUP = 'language_group';

  const MANUAL_EVENT_TYPE = 272;

  protected $mode = null;
  protected $characterId = null;
  protected $locationId = null;
  protected $targetLanguage = null;
  protected $affectedChars = 0;
  protected $recursively;
  /** @var Player */
  private $player;
  /** @var Db */
  private $db;

  public function __construct(Player $player, $options = null)
  {
    $this->player = $player;
    $this->db = Db::get();
    if (is_array($options)) {
      foreach ($options as $key => $value) {
        $methodName = 'set' . ucfirst($key);

        if (method_exists($this, $methodName)) {
          call_user_func(array($this, $methodName), $value);
        }
      }
    }
  }

  #region setters

  public function setMode($mode)
  {
    $mode = strval($mode);
    //validate
    if ($mode == EventsBroadcaster::MODE_CHARACTER ||
      $mode == EventsBroadcaster::MODE_LANGUAGE_GROUP ||
      $mode == EventsBroadcaster::MODE_LOCATION) {
      $this->mode = $mode;
    }

    return $this;
  }

  public function setTargetLanguage($lang_id)
  {

    //defined in stddef, have all using languages
    global $langcode;

    if (!is_numeric($lang_id)) return $this;

    $lang_id = intval($lang_id);
    if ($lang_id == 0 || array_key_exists($lang_id, $langcode)) {
      $this->targetLanguage = $lang_id;
    }

    return $this;
  }

  public function setTargetCharacter($charid)
  {
    $charid = intval($charid);
    if ($charid) {
      $this->characterId = $charid;
    }
    return $this;
  }

  public function setTargetLocation($locationId)
  {
    $locationId = intval($locationId);
    if ($locationId) {
      $this->locationId = $locationId;
    }
    return $this;
  }

  public function setRecursively($value)
  {
    if (is_bool($value)) {
      $this->recursively = $value;
    }
    return $this;
  }

  #endregion

  public function broadcast($text)
  {
    $this->affectedChars = 0;
    $text = htmlentities(strval($text), ENT_COMPAT | ENT_HTML401, 'utf-8');
    $text = urlencode($text);

    if ($this->dataIsValid()) {
      switch ($this->mode) {
        case EventsBroadcaster::MODE_LOCATION:
          $this->broadcastInLocation($text);
          break;
        case EventsBroadcaster::MODE_LANGUAGE_GROUP:
          $this->broadcastToLanguageGroup($text);
          break;
        case EventsBroadcaster::MODE_CHARACTER:
          $this->broadcastToCharacter($text);
          break;
      }
    }
  }

  private function dataIsValid()
  {
    if ($this->mode == null) return false;

    $valid = true;

    switch ($this->mode) {
      case EventsBroadcaster::MODE_CHARACTER:
        $valid = $this->characterId != null && is_numeric($this->characterId);
        break;
      case EventsBroadcaster::MODE_LOCATION:
        $valid = $this->locationId != null && is_numeric($this->locationId) && is_bool($this->recursively);
        break;
      case EventsBroadcaster::MODE_LANGUAGE_GROUP:
        $valid = $valid && $this->targetLanguage !== null && is_numeric($this->targetLanguage);
        break;
      default:
        $valid = false;
        break;
    }
    return $valid;
  }

  private function broadcastInLocation($text)
  {
    $targets = $this->charsFromLocation($this->locationId);

    if (count($targets) == 0) return;
    Event::create(EventsBroadcaster::MANUAL_EVENT_TYPE, 'TEXT=' . $text)->forCharacters($targets)->show();

    $this->affectedChars = count($targets);

    $this->log("Broadcast to location: '$this->locationId'. Chars affected: '$this->affectedChars'. Text: '$text'");
  }

  /**
   * to be sure that we don't have locations loop
   * @var array
   */
  protected $checkedLocations;

  private function charsFromLocation($locationId)
  {
    $this->checkedLocations[$locationId] = true;

    $stm = $this->db->prepare("SELECT id FROM chars WHERE location = :locationId AND
      status = :active AND (language = :targetLanguage1 OR :targetLanguage2 = 0)");
    $stm->bindInt("locationId", $locationId);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->bindInt("targetLanguage1", $this->targetLanguage);
    $stm->bindInt("targetLanguage2", $this->targetLanguage);
    $stm->execute();

    $chars = $stm->fetchScalars();

    if ($this->recursively) {
      $stm = $this->db->prepare("SELECT id FROM locations WHERE region = :locationId");
      $stm->bindInt("locationId", $locationId);
      $stm->execute();
      foreach ($stm->fetchScalars() as $id) {
        if (array_key_exists($id, $this->checkedLocations)) continue;
        $chars = array_merge($chars, $this->charsFromLocation($id));
      }
    }

    return $chars;
  }

  private function broadcastToCharacter($text)
  {
    $stm = $this->db->prepare("SELECT COUNT(*) FROM chars WHERE id = :charId AND status = :active");
    $stm->bindInt("charId", $this->characterId);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $hCheck = $stm->executeScalar();
    if ($hCheck == 0) {
      return;
    }

    Event::create(EventsBroadcaster::MANUAL_EVENT_TYPE, 'TEXT=' . $text)
      ->forCharacter($this->characterId)->show();

    $this->affectedChars = 1;
    $this->log("Broadcast to character: '$this->characterId'. Chars affected: '$this->affectedChars'. Text: '$text'");
  }

  private function broadcastToLanguageGroup($text)
  {
    $stm = $this->db->prepare("SELECT id FROM chars WHERE
      status = :active AND (language = :targetLanguage1 OR :targetLanguage2 = 0)");
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->bindInt("targetLanguage1", $this->targetLanguage);
    $stm->bindInt("targetLanguage2", $this->targetLanguage);
    $stm->execute();
    $targets = $stm->fetchScalars();

    if (count($targets) == 0) {
      return;
    }

    Event::create(EventsBroadcaster::MANUAL_EVENT_TYPE, 'TEXT=' . $text)
      ->forCharacters($targets)->show();

    $this->affectedChars = count($targets);
    $this->log("Broadcast to language group: '$this->targetLanguage'. Chars affected: '$this->affectedChars'. Text: '$text'");
  }

  public function affectedChars()
  {
    return $this->affectedChars;
  }

  private function log($logMessage)
  {
    $message = "{$this->player->getFullNameWithId()}: " . $logMessage;
    Report::saveInDb("events_broadcaster", $message, $GLOBALS['emailPlayers'], "trace who and to what using events_broadcaster");
  }

}
