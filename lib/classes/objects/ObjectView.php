<?php

class ObjectView
{


  /** @var stdClass[] */
  private static $charData = [];

  /** @var CObject */
  private $subject;
  /** @var Character */
  private $observer;
  /** @var Db */
  private $db;

  /**
   * @param CObject $subject object that's being shown
   * @param Character $observer character that is observer or its id
   */
  public function __construct(CObject $subject, Character $observer)
  {
    $this->subject = $subject;
    $this->observer = $observer;
    $this->db = Db::get();
  }

  public function show($stype, $weight = null)
  {
    if (!in_array($stype, array('object', 'inventory', 'transfer', 'transfer_long'))) { // input data check
      throw new InvalidArgumentException("Should never occur. stype is $stype which is not allowed"); // should never occur
    }

    if ($weight === null) {
      $weight = $this->subject->getWeight();
    }
    $uniqueName = $this->subject->getUniqueName();

    $bottom = "";

    $result = new stdClass();
    if ($this->subject->getType() == ObjectConstants::TYPE_RAW) { // raw
      $this->updateResultForRawMaterial($result, $weight);
    } else {
      $uniqueName = $this->updateUniqueNameIfSealed($uniqueName);


      if ($this->subject->names['unique_name'] != $uniqueName) { // cache is outdated, needs to be updated
        $stm = $this->db->prepare("SELECT content, grammar FROM texts WHERE name = :name
          AND language IN (1, :language) ORDER BY language DESC LIMIT 1");
        $stm->bindStr("name", "item_{$uniqueName}_o");
        $stm->bindInt("language", $this->observer->getLanguage());
        $stm->execute();
        list ($transfer_long, $gender) = $stm->fetch(PDO::FETCH_NUM);

        $stm = $this->db->prepare("SELECT content FROM texts WHERE name = :name
          AND language IN (1, :language) ORDER BY language DESC LIMIT 1");
        $stm->bindStr("name", "item_{$uniqueName}_t");
        $stm->bindInt("language", $this->observer->getLanguage());
        $transfer = $stm->executeScalar();
      } else {
        $transfer_long = $this->subject->names['transfer_long'];
        $gender = $this->subject->names['grammar'];
        $transfer = $this->subject->names['transfer'];
      }

      if ($this->subject->getObjectCategory()->getId() == ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS) {
        $stm = $this->db->prepare("SELECT fullness, loyal_to FROM animal_domesticated WHERE from_object = :objectId");
        $stm->bindInt("objectId", $this->subject->getId());
        $stm->execute();
        list ($fullness, $loyal_to) = $stm->fetch(PDO::FETCH_NUM);
        $transfer_long .= " (" . Animal::getFedTagFromValue($fullness, $gender) . ")";

        if ($loyal_to > 0) {
          $bottom .= "<p class=\"sign\"><CANTR REPLACE NAME=animal_loyal_to GENDER=$gender OWNER={$loyal_to}></p>";
        }
      }

      $transfer_long = $this->updateTransferLongForLock($transfer_long);

      $result->transfer_long = $this->replace_strings($transfer_long);
      $result->transfer = $transfer ? $this->replace_strings($transfer) : $result->transfer_long;
    }

    $this->addDeteriorationStateToResult($result, $gender);

    if ($stype == 'inventory' || $stype == 'object') {

      if (($this->subject->getType() != ObjectConstants::TYPE_RAW) && $this->subject->isQuantity()) {
        $number = $this->subject->getAmount();
        $result->text = $number . " <CANTR REPLACE NAME=stacked_o_disp> " . $result->transfer_long;
      } else {
        $result->text = $result->transfer_long;
      }

      if ($this->subject->hasProperty("Storage") && !$this->subject->hasProperty("NoteStorage")) {
        $contentNames = $this->subject->printContainerContent();
        $bottom .= "<p class=\"sign\">$contentNames</p>";
      }

      $projectWorkedOn = $this->subject->getProjectWorkedOn();
      if ($projectWorkedOn != null) {
        $result->text .= " <i>(<CANTR REPLACE NAME=object_in_use>)</i>";
        $bottom .= $this->displayWorkersList($projectWorkedOn);
      }

      if ($stype == 'object') {
        $buttonActions = $this->subject->getObjectType()->getActionsOnGround();
      } elseif ($stype == 'inventory') {
        $buttonActions = $this->subject->objecttype->getActionsInInventory();
      }

      if (Pipe::from($buttonActions)->filter(function(ObjectAction $a) {
          return $a->getAction() == "setfreq";
        })->count() > 0) {
        $result->text .= " <i>(<CANTR REPLACE NAME=obj_freq_info FREQ=" . $this->subject->getSpecifics() . ">)</i>";
      }

      if (isset($buttonActions)) {
        $result->buttons = $this->getActionButtons($stype, $buttonActions);
      }

      $result->text .= $this->subject->getDescription($this->subject->getId());

      $result->text .= $bottom;
    }


    return $result;
  }

  public function getInUseText()
  {
    if ($this->subject->isInUse()) {
      return " <i>(<CANTR REPLACE NAME=object_in_use>)</i>";
    }
    return "";
  }

  /**
   * @param $page_type
   * @param ObjectAction[] $input
   * @return array
   */
  private function getActionButtons($page_type, array $input)
  {

    $charData = self::getCharData();
    $buttons = [];

    $pointAction = new ObjectAction("pointat", "pointat", "alt_point_object");
    $buttons[] = $pointAction->asArray($this->subject);

    if ($page_type == 'object') {

      if (!in_array($this->subject->getSetting(), [ObjectConstants::SETTING_FIXED, ObjectConstants::SETTING_HEAVY])) { // if object can be taken
        $takeAction = new ObjectAction("take", "take", "alt_take_object");
        $buttons[] = $takeAction->asArray($this->subject);
      }

      if (!in_array($this->subject->getSetting(), [ObjectConstants::SETTING_FIXED])) { // if object can be dragged
        if (!$charData['work']) { // if character is not working and dragging
          $dragAction = new ObjectAction("drag", "pull", "alt_pull");
          $buttons[] = $dragAction->asArray($this->subject);
        }
      }

      if ($this->subject->getObjectCategory()->getId() == ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS) {
        $stm = $this->db->prepare("SELECT can_be_loyal FROM animal_domesticated_types WHERE of_object_type = :objectTypeId");
        $stm->bindInt("objectTypeId", $this->subject->getType());
        $can_be_loyal = $stm->executeScalar();
        if ($can_be_loyal) {
          $stm = $this->db->prepare("SELECT loyal_to FROM animal_domesticated WHERE from_object = :objectId");
          $stm->bindInt("objectId", $this->subject->getId());
          $loyal_to = $stm->executeScalar();
          if ($loyal_to != $this->observer->getId()) {
            $adoptAction = new ObjectAction("animal_adopt", "adopt_animal", "alt_adopt_animal");
            $buttons[] = $adoptAction->asArray($this->subject);
          }
        }
      }
    }

    if ($page_type == 'inventory') {
      if (!$charData['travel']) {
        $dropAction = new ObjectAction("drop", "drop", "alt_drop");
        $buttons[] = $dropAction->asArray($this->subject);

        $giveAction = new ObjectAction("give", "give", "alt_give");
        $buttons[] = $giveAction->asArray($this->subject);

        if (!in_array($this->subject->getType(), ObjectConstants::$NON_USABLE_TYPES)
          && !in_array($this->subject->getObjectCategory()->getId(), ObjectConstants::$NON_USABLE_CATEGORIES)
        ) {
          // if that object is not a note or a resource then it can be used for a project
          $useAction = new ObjectAction("useobject", "use", "alt_use_objects");
          $buttons[] = $useAction->asArray($this->subject);
        }
      }
    }

    $isRaw = $this->subject->getType() == ObjectConstants::TYPE_RAW;
    $isFixedObject = $this->subject->getSetting() == ObjectConstants::SETTING_FIXED;
    if (!($this->subject->isQuantity() && !$isRaw) && !$isFixedObject) {
      $storeAction = new ObjectAction("store", "store", "alt_store_object");
      $buttons[] = $storeAction->asArray($this->subject);
    }

    foreach (array_reverse($input) as $action) {
      //we want to hide 'writenote' button when note is not editable
      if ($action->getAction() == 'writenote') {
        $obj_note_id = $this->subject->getTypeid();
        $stm = $this->db->prepare("SELECT setting FROM obj_notes WHERE id = :id");
        $stm->bindInt("id", $obj_note_id);
        if ($stm->executeScalar() == Note::NOTE_SETTING_UNEDITABLE) {
          continue;
        }
      }

      // hide 'delete envelope' or 'empty envelope'
      if ($action->getAction() == 'destroy_envelop') {
        $stm = $this->db->prepare("SELECT id FROM objects WHERE attached = :objectId LIMIT 1");
        $stm->bindInt("objectId", $this->subject->getId());
        if ($stm->executeScalar() != null) {
          continue;
        }
      }
      if ($action->getAction() == 'retrieve' && in_array($action->getImg(), ["book", "empty_envelop"])) {
        $stm = $this->db->prepare("SELECT id FROM objects WHERE attached = :objectId LIMIT 1");
        $stm->bindInt("objectId", $this->subject->getId());
        if ($stm->executeScalar() == null) {
          continue;
        }
      }

      // hide eat buttons of non-edible raws
      if ($action->getAction() == 'eatraw') {
        $stm = $this->db->prepare("SELECT nutrition + strengthening + energy + drunkenness FROM rawtypes WHERE id = :objectId");
        $stm->bindInt("objectId", $this->subject->getTypeid());
        $isEdible = $stm->executeScalar();
        if (!$isEdible) { // if resource is non edible and code wants to add that button -> force next iteration to avoid it
          continue;
        }
      }

      if ($action->getAction() == 'ingest_all') {
        $stm = $this->db->prepare("SELECT SUM(nutrition + strengthening + energy + drunkenness) as isedible
            FROM objects o INNER JOIN rawtypes rt ON rt.id = o.typeid
            WHERE o.attached = :objectId AND o.type = :type");
        $stm->bindInt("objectId", $this->subject->getId());
        $stm->bindInt("type", ObjectConstants::TYPE_RAW);
        $isContentEdible = $stm->executeScalar();
        if (!$isContentEdible) { // hide "eat" button for containers which don't have any food inside
          continue;
        }
      }

      if (in_array($action->getAction(), ["seal_object", "bind_book", "alter_book_title"])) {
        $sealsManager = new SealsManager($this->subject);
        if (count($sealsManager->getAll(true)) > 0) {
          continue;
        }
      }

      if ($action->getAction() == "copy_book") {
        $sealsManager = new SealsManager($this->subject);
        if (count($sealsManager->getAll(true)) == 0) { // not bound yet
          continue;
        }
      }

      if ($action->getAction() == 'animal_saddling') {
        $stm = $this->db->prepare("SELECT loyal_to FROM animal_domesticated WHERE from_object = :objectId");
        $stm->bindInt("objectId", $this->subject->getId());
        $loyal_to = $stm->executeScalar();
        if ($loyal_to != $this->observer->getId()) {
          continue; // hide saddling button for non-owner
        }
      }

      if (StringUtil::contains($action->getAction(), '/')) {
        $args = explode('/', $action->getAction());

        $actionParams = $args[0];
        for ($i = 1; $i < count($args); $i++) {
          $actionParams .= "&custom_arg$i=" . urlencode($args[$i]);
        }
        $action = new ObjectAction($actionParams, $action->getImg(), $action->getCaption());
      }

      $buttons[] = $action->asArray($this->subject, ["id" => $this->subject->getTypeid()]);
    }

    if ($page_type == 'inventory') {
      if (!$charData['travel'] && $this->subject->getRepairRate() > 0) {
        $stm = $this->db->prepare("SELECT id FROM projects WHERE location = :locationId AND type = :type AND subtype = :subtype");
        $stm->bindInt("locationId", $charData['location']);
        $stm->bindInt("type", ProjectConstants::TYPE_REPAIRING);
        $stm->bindInt("subtype", $this->subject->getId());
        $beingRepaired = $stm->executeScalar();
        if (!$beingRepaired) {
          $repairAction = new ObjectAction("repair", "repair", "alt_repair");
          $buttons[] = $repairAction->asArray($this->subject);
        }
      }
    }

    return $buttons;
  }


  /**
   * @deprecated it shouldn't be so easy to get any column in any name, it should be reduced to a few well-known cases
   * @param $text
   * @return mixed
   */
  public function replace_strings($text)
  {

    $result = $text;

    preg_match_all("/#([^;]+?)#/", $text, $desc, PREG_SET_ORDER);

    foreach ($desc as $entry) {
      $matches = explode(".", $entry[1]);
      if ($matches[0] == 'subtable') {
        $stm = $this->db->prepare("SELECT $matches[1] FROM {$this->subject->getSubtable()} WHERE id = :id");
        $stm->bindInt("id", $this->subject->getTypeid());
        $replacement = $stm->executeScalar();
      } else {
        $stm = $this->db->prepare("SELECT $matches[0] FROM objects WHERE id = :id");
        $stm->bindInt("id", $this->subject->getId());
        $replacement = $stm->executeScalar();
      }
      $result = str_replace($entry[0], TextFormat::getDistinctHtmlText($replacement), $result);
    }

    return $result;
  }

  /**
   * Function for character properties caching
   * @return array with keys: 'work', 'location', 'travel'
   */
  private function getCharData()
  {
    if (!array_key_exists($this->observer->getId(), self::$charData)) {
      self::$charData[$this->observer->getId()] = [
        "work" => $this->observer->isBusy(),
        "travel" => $this->observer->getLocation() == 0,
        "location" => $this->observer->getLocation(),
      ];
    }
    return self::$charData[$this->observer->getId()];
  }

  public function updateUniqueNameIfSealed($uniqueName)
  {
    // check if envelope is sealed
    if (in_array($this->subject->getType(), [ObjectConstants::TYPE_NOTE, ObjectConstants::TYPE_ENVELOPE])) {
      $sealsManager = new SealsManager($this->subject);
      if (count($sealsManager->getAll()) > 0) {
        $uniqueName .= "_sealed";
      }
    }
    return $uniqueName;
  }

  public function updateResultForRawMaterial($result, $weight)
  {
    $rawName = str_replace(" ", "_", ObjectHandler::getRawNameFromId($this->subject->getTypeid()));

    $result->transfer_long = $weight . " <CANTR REPLACE NAME=grams_of> <CANTR REPLACE NAME=raw_$rawName>";
    $result->transfer = "<CANTR REPLACE NAME=some> <CANTR REPLACE NAME=raw_$rawName>";

    return $result;
  }

  /**
   * Replaces "#lock#" to lock status (lock id and if is locked/unlocked), shows "no lock" otherwise
   * @param $transfer_long
   * @return mixed new value of $transfer_long
   */
  public function updateTransferLongForLock($transfer_long)
  {
    if (StringUtil::contains($transfer_long, '#lock#')) {
      $keyLock = KeyLock::loadByObjectId($this->subject->getId());
      if ($keyLock->exists()) {
        $lockId = "<CANTR REPLACE NAME=number_abbr> " . $keyLock->getId() . " ";
        $lockText = " (" . $lockId . ($keyLock->isLocked() ? "locked" : "unlocked") . ")";
      } else {
        $lockText = " (<CANTR REPLACE NAME=text_no_lock>)";
      }
      $transfer_long = str_replace('#lock#', $lockText, $transfer_long);
    }

    if (StringUtil::contains($transfer_long, '#open#')) {
      $open = StringUtil::contains($this->subject->getSpecifics(), 'open') ? '<CANTR REPLACE NAME=window_open>' : '<CANTR REPLACE NAME=window_closed>';
      $transfer_long = str_replace('#open#', $open, $transfer_long);
    }
    return $transfer_long;
  }

  public function addDeteriorationStateToResult($result, $gender)
  {
    if ($this->subject->getDeterPerDay() && $this->subject->isDeteriorationVisible()) {
      $deteriorationView = (new DeteriorationViewFactory(DeteriorationViewFactory::VISIBLE, true))->language($this->observer->getLanguage());
      $deter_descr = $deteriorationView->show($this->subject->getDeterioration(), $gender, $result->transfer);

      $result->transfer = str_replace("<ITEM>", $result->transfer, $deter_descr);
      $result->transfer_long = str_replace("<ITEM>", $result->transfer_long, $deter_descr);
    }
  }

  /**
   * @param $projectWorkedOn
   * @return string formatted html text with list of people working on the project associated with the object
   */
  private function displayWorkersList($projectWorkedOn)
  {
    if ($this->subject->getLocation() > 0 && $this->subject->hasProperty("DisplayWorkers")) {
      $stm = $this->db->prepare("SELECT location FROM projects WHERE id = :projectId");
      $stm->bindInt("projectId", $projectWorkedOn);
      $projectLocation = $stm->executeScalar();
      if ($projectLocation == $this->subject->getLocation()) {
        $stm = $this->db->prepare("SELECT id FROM chars WHERE project = :projectId");
        $stm->bindInt("projectId", $projectWorkedOn);
        $stm->execute();
        $workerIds = $stm->fetchScalars();
        if (count($workerIds) > 0) {
          $workersList = Pipe::from($workerIds)->map(function($charId) {
            return "<CANTR CHARNAME ID=$charId>";
          })->implode(", ");
          return "<p class=\"sign\"><CANTR REPLACE NAME=used_by_characters> $workersList</p>";
        }
      }
    }
    return "";
  }
}
 
