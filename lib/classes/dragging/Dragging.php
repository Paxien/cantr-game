<?php

/**
 * Responsible for handling dragging quasi-projects. Responsible ONLY for tables `dragging` and `draggers`.
 * @author Aleksander Chrabaszcz
 */
class Dragging
{
  private $id = null; // null == not stored in db
  private $victimType; // in DraggingConstants
  private $victim;
  private $goal; // location or -1 when dragging from project
  private $weight;
  /** @var int[] */
  private $draggers = array();

  /** @var Db */
  private $db;

  private $finished = false; // when true then object is going to be removed

  /**
   * object should be constructed by a static factory method
   */
  private function __construct(Db $db)
  {
    $this->db = $db;
  }

  /**
   * Static factory method
   * @param $type int of victim, DraggingConstants::TYPE_HUMAN or DraggingConstants::TYPE_OBJECT
   * @param $victim int id of victim (object or character)
   * @return Dragging based on DraggingConstants type and victim id.
   * @throws InvalidArgumentException when there's no such dragging
   */
  public static function loadByVictim($type, $victim)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM dragging WHERE victimtype = :type AND victim = :victim");
    $stm->bindInt("type", $type);
    $stm->bindInt("victim", $victim);
    $draggingId = $stm->executeScalar();
    if (!$draggingId) {
      throw new InvalidArgumentException("there is no dragging victim $victim of type $type");
    }
    return self::loadById($draggingId);
  }

  /**
   * Static factory method
   * @return Dragging based on dragger id from `chars`.
   * @throws InvalidArgumentException when there's no such dragging
   */
  public static function loadByDragger($draggerId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT dragging_id FROM draggers WHERE dragger = :draggerId");
    $stm->bindInt("draggerId", $draggerId);
    $draggingId = $stm->executeScalar();
    if (!$draggingId) {
      throw new InvalidArgumentException("there is no dragging with dragger $draggerId");
    }
    return self::loadById($draggingId);
  }

  /**
   * Static factory method
   * @return Dragging based on dragging id
   * @throws InvalidArgumentException when there's no such dragging
   */
  public static function loadById($id)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM dragging WHERE id = :id");
    $stm->bindInt("id", $id);
    $stm->execute();
    if ($drag = $stm->fetchObject()) {
      $dragging = new Dragging($db);
      $dragging->id = $drag->id;
      $dragging->victimType = $drag->victimtype;
      $dragging->victim = $drag->victim;
      $dragging->goal = $drag->goal;
      $dragging->weight = $drag->weight;
      $dragging->draggers = $dragging->calculateDraggers();

      return $dragging;
    } else {
      throw new InvalidArgumentException("no dragging of id $id");
    }
  }

  /**
   * Creates a new dragging project for specified victim.
   * Checks if this victim is already subject of dragging project. Doesn't save data in db
   * @param $type int specified in DraggingConstants
   * @param $victim int id of subject of dragging project. Table may be different for different types
   * @param $goal int id of location, -1 is "drag character from a project"
   * @return Dragging|null object on success, null otherwise
   */
  public static function newInstance($type, $victim, $goal, $weight = 0)
  {
    if (!in_array($type, array(DraggingConstants::TYPE_HUMAN, DraggingConstants::TYPE_OBJECT))) {
      throw new InvalidArgumentException("$type is not valid dragging type");
    }

    if (!Validation::isPositiveInt($victim)) {
      throw new InvalidArgumentException("$victim is not positive int, can't be id");
    }

    if (!Validation::isPositiveInt($goal) && $goal != DraggingConstants::GOAL_FROM_PROJECT) {
      throw new InvalidArgumentException("$goal is not a valid goal id and not dragging from project");
    }
    $db = Db::get();
    $dragging = new Dragging($db);

    $dragging->victimType = $type;
    $dragging->victim = $victim;
    $dragging->goal = $goal;
    $dragging->weight = $weight;

    return $dragging;
  }

  /**
   * Gets list of draggers from db
   * @return array of draggers ids, empty array when no participants
   */
  private function calculateDraggers()
  {
    $stm = $this->db->prepare("SELECT dragger FROM draggers WHERE dragging_id = :id");
    $stm->bindInt("id", $this->id);
    $stm->execute();
    return $stm->fetchScalars();
  }

  /**
   * Marks a dragging as 'to be removed' in next call of saveInDb() function
   */
  public function remove()
  {
    $this->finished = true;
  }

  /**
   * @return true when strength of draggers is enough to drag victim
   */
  public function isEnoughStrength()
  {
    return $this->getFractionDone() >= 1;
  }

  /**
   * @return float strength of draggers/ required strength ratio
   */
  public function getFractionDone()
  {
    return $this->getStrengthOfDraggers() / $this->getStrengthNeededToDrag();
  }

  public function getStrengthOfDraggers()
  {
    import_lib("func.genes.inc.php");
    $strengthSum = 0;

    foreach ($this->draggers as $draggerId) {
      $health = read_state($draggerId, _GSS_HEALTH) / _SCALESIZE_GSS;
      $martial = (read_state($draggerId, _GSS_FIGHTING) / 16667) + (read_state($draggerId, _GSS_STRENGTH) / 25000);
      $tiredness = read_state($draggerId, _GSS_TIREDNESS) / _SCALESIZE_GSS;
      $drunkenness = (read_state($draggerId, _GSS_DRUNKENNESS) / _SCALESIZE_GSS) * 0.5;

      $strengthSum += floor($health * $martial * (1 - $tiredness) * (1 - $drunkenness) * 150);

      if ($this->victimType == DraggingConstants::TYPE_OBJECT) { // wheelbarrows work only for objects
        // TODO move to properties
        $stm = $this->db->prepare("SELECT 1 FROM objects WHERE person = :draggerId AND type = :type LIMIT 1");
        $stm->bindInt("draggerId", $draggerId);
        $stm->bindInt("type", ObjectConstants::TYPE_IMPROVED_WHEELBARROW);
        $hasIwb = $stm->executeScalar();
        $stm->bindInt("draggerId", $draggerId);
        $stm->bindInt("type", ObjectConstants::TYPE_WHEELBARROW);
        $hasRwb = $stm->executeScalar();

        if ($hasIwb) {
          $strengthSum += DraggingConstants::IMPROVED_WHEELBARROW_DRAGGING_EFFECT;
        } elseif ($hasRwb) {
          $strengthSum += DraggingConstants::WHEELBARROW_DRAGGING_EFFECT;
        }
      }
    }

    return $strengthSum;
  }

  public function getStrengthNeededToDrag()
  {
    import_lib("func.genes.inc.php");
    $locationMultiplier = $this->getStrengthByLocationMultiplier();
    if ($this->victimType == DraggingConstants::TYPE_OBJECT) {

      $stm = $this->db->prepare("SELECT weight, setting FROM objects WHERE id = :objectId");
      $stm->bindInt("objectId", $this->victim);
      $stm->execute();
      list($weight, $objSetting) = $stm->fetch(PDO::FETCH_NUM);

      if ($objSetting == ObjectConstants::SETTING_QUANTITY && $this->weight > 0) { // can drag less than all
        $weight = min($this->weight, $weight); // try to drag as much as possible
      }

      $weight *= $this->getStrengthForObjectMultiplier();

      $strengthNeeded = 200 * ($weight / CharacterConstants::BODY_WEIGHT);

      return max(10, round($locationMultiplier * $strengthNeeded));
    } else { // TYPE_HUMAN
      $martial = (read_state($this->victim, _GSS_FIGHTING) / 16667) + (read_state($this->victim, _GSS_STRENGTH) / 25000);
      $tiredness = read_state($this->victim, _GSS_TIREDNESS) / _SCALESIZE_GSS;
      $drunkenness = (read_state($this->victim, _GSS_DRUNKENNESS) / _SCALESIZE_GSS) * 0.5;

      $strengthNeeded = ($martial * (1 - $tiredness) * (1 - $drunkenness) * 300);

      return max(60, floor($locationMultiplier * $strengthNeeded));
    }
  }

  private function getStrengthForObjectMultiplier() // just for objects
  {
    // check if dragged object is animal and loyal to any of draggers
    $stm = $this->db->prepare("SELECT loyal_to FROM animal_domesticated
      WHERE from_object = :objectId AND from_animal = 0 AND from_location = 0");
    $stm->bindInt("objectId", $this->victim);
    $loyalTo = $stm->executeScalar();
    if (($loyalTo > 0) && in_array($loyalTo, $this->draggers)) {
      return 1 / 4;
    }
    return 1;
  }

  private function getStrengthByLocationMultiplier()
  {
    $location = $this->getDraggingStartLocation();
    // check if char/object is dragged from back of animal which is loyal to any of draggers
    $stm = $this->db->prepare("SELECT loyal_to FROM animal_domesticated
      WHERE from_location = :locationId AND from_object = 0 AND from_animal = 0");
    $stm->bindInt("locationId", $location);
    $loyalTo = $stm->executeScalar();
    if (($loyalTo > 0) && in_array($loyalTo, $this->draggers)) {
      return 1 / 4;
    }

    $isVictimHuman = $this->getVictimType() == DraggingConstants::TYPE_HUMAN;
    if (($loyalTo > 0) && $isVictimHuman && ($loyalTo == $this->getVictim())) {
      return 4;
    }

    return 1;
  }

  private function getDraggingStartLocation()
  {
    if ($this->victimType == DraggingConstants::TYPE_OBJECT) {
      $stm = $this->db->prepare("SELECT location FROM objects WHERE id = :objectId");
      $stm->bindInt("objectId", $this->victim);
      return $stm->executeScalar();
    } else {
      $stm = $this->db->prepare("SELECT location FROM chars WHERE id = :charId");
      $stm->bindInt("charId", $this->victim);
      return $stm->executeScalar();
    }
  }

  public static function getMaxWeightPossibleToDrag($draggerId)
  {
    import_lib("func.genes.inc.php");
    $health = read_state($draggerId, _GSS_HEALTH) / _SCALESIZE_GSS;
    $martial = (read_state($draggerId, _GSS_FIGHTING) / 16667) + (read_state($draggerId, _GSS_STRENGTH) / 25000);
    $tiredness = read_state($draggerId, _GSS_TIREDNESS) / _SCALESIZE_GSS;

    $strengthSum = floor($health * $martial * (1 - $tiredness) * 150);

    return $strengthSum * (CharacterConstants::BODY_WEIGHT / 200);
  }

  /**
   * Adds a new dragger for specified dragging project.
   * Dragger shouldn't be participant of dragging project or regular project.
   */
  public function addDragger($draggerId)
  {
    $draggerId = intval($draggerId);
    $this->draggers[] = $draggerId;
    $this->draggers = array_values(array_unique($this->draggers));
  }

  public function removeDragger($draggerId)
  {
    $draggerId = intval($draggerId);
    $this->draggers = array_values(array_diff($this->draggers, array($draggerId)));
  }

  /**
   * Inserts, updates or deletes dragging data from db.
   * Calling saveInDb() after remove() or when there are no draggers deletes dragging
   */
  public function saveInDb()
  {
    if ($this->id) {
      $this->deleteDraggers();
      // project should be updated, because not finished and are active draggers
      if (!$this->finished && count($this->draggers) > 0) {
        $this->saveDraggersInDb();
      } else { // project exists but should disappear
        $stm = $this->db->prepare("DELETE FROM dragging WHERE id = :id");
        $stm->bindInt("id", $this->id);
        $stm->execute();
        $this->id = null;
      }
    } elseif (!$this->finished) { // id is null, this dragging was never saved in db
      if (count($this->draggers) > 0) {
        $stm = $this->db->prepare("INSERT INTO dragging (victimtype, victim, goal, weight)
          VALUES (:victimType, :victimId, :goal, :weight)");
        $stm->bindInt("victimType", $this->victimType);
        $stm->bindInt("victimId", $this->victim);
        $stm->bindInt("goal", $this->goal);
        $stm->bindInt("weight", $this->weight);
        $stm->execute();
        $this->id = $this->db->lastInsertId();
        $this->saveDraggersInDb();
      }
    }
  }

  private function deleteDraggers()
  {
    $stm = $this->db->prepare("DELETE FROM draggers WHERE dragging_id = :id");
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }

  private function saveDraggersInDb()
  {
    $stm = $this->db->prepare("INSERT INTO draggers (dragging_id, dragger) VALUES (:id, :draggerId)");
    foreach ($this->draggers as $dragger) {
      $stm->bindInt("id", $this->id);
      $stm->bindInt("draggerId", $dragger);
      $stm->execute();
    }
  }

  public function getId()
  {
    return $this->id;
  }

  /**
   * @return true if dragging is currently stored in db, false otherwise
   */
  public function hasId()
  {
    return $this->id != null;
  }

  public function getVictim()
  {
    return $this->victim;
  }

  public function getGoal()
  {
    return $this->goal;
  }

  public function getWeight()
  {
    return $this->weight;
  }

  public function getVictimType()
  {
    return $this->victimType;
  }

  public function getDraggers()
  {
    return $this->draggers;
  }
}
