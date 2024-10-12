<?php

// TODO should be named "Readable"
class NoteHolder
{
  private $object;

  private $noteId;
  private $setting;
  private $title;
  private $contents;

  /** @var bool */
  private $remove;

  /** @var Db */
  private $db;

  /**
   * Decorate $object with a NoteHolder class. $object must have ability of containing text.
   *
   * @param CObject $object to be decorated
   *
   * @throws InvalidArgumentException if $object doesn't have property "Readable"
   * @throws IllegalStateException if $object doesn't have associated row in table obj_notes
   */
  public function __construct(CObject $object)
  {
    if (!$object->hasProperty("Readable")) {
      throw new InvalidArgumentException("It can't hold a note");
    }
    $this->object = $object;
    $this->db = Db::get();

    $stm = $this->db->prepare("SELECT * FROM obj_notes WHERE id = :id");
    $stm->bindInt("id", $this->object->getTypeid());
    $stm->execute();
    if ($noteData = $stm->fetchObject()) {
      $this->loadFromFetchObject($noteData);
    } else {
      $this->setNoteDefaults();
    }
  }

  private function loadFromFetchObject($noteData)
  {
    $this->noteId = $noteData->id;
    $this->setting = $noteData->setting;
    $this->title = $noteData->utf8title;
    $this->contents = $noteData->utf8contents;
  }

  private function setNoteDefaults()
  {
    $this->noteId = null;
    $this->setting = Note::NOTE_SETTING_EDITABLE;
    $this->title = "";
    $this->contents = "";
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function getContents()
  {
    return $this->contents;
  }

  public function setTitleAndContents($title, $contents)
  {
    $this->title = $title;
    $this->contents = $contents;
  }

  public function isEditable()
  {
    if ($this->object->getType() == ObjectConstants::TYPE_NOTE) {
      return $this->setting == Note::NOTE_SETTING_EDITABLE;
    }
    return true;
  }

  public function setEditable($editability)
  {
    if ($this->object->getType() == ObjectConstants::TYPE_NOTE) {
      if (!in_array($editability, [Note::NOTE_SETTING_EDITABLE, Note::NOTE_SETTING_UNEDITABLE])) {
        throw new InvalidArgumentException("'$editability' is not a valid state of editability");
      }
      $this->setting = $editability;
      return;
    }
    throw new IllegalStateException("only notes can change state of editability");
  }

  public function removeContents()
  {
    $this->remove = true;
  }

  public function saveInDb()
  {
    if ($this->remove) {
      if ($this->noteId) {
        $stm = $this->db->prepare("DELETE FROM obj_notes WHERE id = :id");
        $stm->bindInt("id", $this->noteId);
        $stm->execute();
      }
    } elseif ($this->noteId == null) {
      $stm = $this->db->prepare("INSERT INTO obj_notes (setting, utf8title, utf8contents)
        VALUES (:setting, :title, :contents)");
      $stm->bindInt("setting", $this->setting);
      $stm->bindStr("title", $this->title);
      $stm->bindStr("contents", $this->contents);
      $stm->execute();
      $this->noteId = $this->db->lastInsertId();

      $this->object->setTypeid($this->noteId);
      $this->object->saveInDb();
    } else {
      $stm = $this->db->prepare("UPDATE obj_notes SET utf8title = :title, utf8contents = :contents, setting = :setting
        WHERE id = :id");
      $stm->bindInt("setting", $this->setting);
      $stm->bindStr("title", $this->title);
      $stm->bindStr("contents", $this->contents);
      $stm->bindInt("id", $this->noteId);
      $stm->execute();
    }
  }
}
