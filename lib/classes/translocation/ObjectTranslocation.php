<?php

/**
 * Responsible for moving objects from one location/char to another location/char.
 * It handles taking (Location -> Character), dropping (Character -> Location),
 * giving (Character -> Character) and dragging (Location -> Location)
 * It's possible to enable/disable checks for capacity, direct link between locations,
 * receiver char status (alive and not NDS), object setting (if such action is allowed for this object)
 * Performs translocation or throws an exception
 */

class ObjectTranslocation
{
  private $object;
  private $from;
  private $to;
  private $weight;

  private $fromLoc;
  private $toLoc;

  private $checkNearness = true;
  private $checkCapacity = true;
  private $checkReceiver = true;
  private $checkObjectSetting = true;

  private $logger;
  /** @var Db */
  private $db;

  /**
   * Creates translocation instance
   *
   * @param CObject $object single object being translocated
   * @param Location|Character $from place from which object is moved. Should be real location of object
   * @param Location|Character $to place where object is going to be moved
   * @param int $amount (optional) amount (for raws) or number (for stackable objects) which should be moved
   *
   * @throws InvalidArgumentException when location doesn't exist or is travelling
   */
  public function __construct(CObject $object, $from, $to, $amount = 0)
  {
    $this->object = $object;
    $this->from = $from;
    $this->fromLoc = ($this->from instanceof Location) ? $this->from
      : Location::loadById($this->from->getLocation());
    $this->to = $to;
    $this->toLoc = ($this->to instanceof Location) ? $this->to
      : ($this->to->getLocation() === 0 ? null : Location::loadById($this->to->getLocation()));

    if (($amount != 0) && ($object->getSetting() == ObjectConstants::SETTING_QUANTITY)) {
      $isRaw = ($object->getType() == ObjectConstants::TYPE_RAW);
      $this->weight = $isRaw ? $amount : $amount * ObjectConstants::WEIGHT_COIN; // raw : coin
    } else { // if amount unspecified or not alterable then everything is moved
      $this->weight = $object->getWeight();
    }

    $this->logger = Logger::getLogger(__CLASS__);
    $this->db = Db::get();
  }

  /**
   * Checks if translocation is possible (based on >>check<< flags), performs the translocation
   * and immediately saves result in db or throws an exception when it's not possible.
   * For quantity objects, translocated object is NEVER moved to a new location.
   * New pile/stack can be created in $to, while $object can lose weight or disappear (expire)
   * @throws TooFarAwayException (checkNearness == true) and there's no direct connection between $toLoc and $fromLoc
   * or $to is Character which is dead
   * @throws BadInitialLocationException $object is not in $from, or transfer is done from location 0 (travelling)
   * @throws InvalidAmountException amount is not > 0 or greater than max weight
   * @throws WeightCapacityExceededException (checkCapacity == true) and moving $object would exceed weight capacity in $toLoc
   * @throws InvalidObjectSettingException (checkObjectSetting == true) and object is of type which can't be moved
   * @throws CharacterStatusException (checkReceiver == true) and receiver is in near death state
   */
  public function perform()
  {
    // check if giver is location "travelling"
    if ($this->fromLoc->getId() == 0) {
      throw new BadInitialLocationException("is travelling");
    }

    if ($this->isDragging() || $this->isTaking()) { // initial place is of class Location
      if ($this->object->getLocation() != $this->from->getId()) {
        throw new BadInitialLocationException("");
      }
    } else { // initial place is of class Character
      if ($this->object->getPerson() != $this->from->getId()) {
        throw new BadInitialLocationException("");
      }
    }
    // moving NaN grams or more than possible (fails only for quantity objects)
    if (!Validation::isNonNegativeInt($this->weight) || ($this->weight > $this->object->getWeight())) {
      throw new InvalidAmountException("");
    }

    if ($this->checkNearness) { // we check if $from and $to are near enough
      if ($this->isTaking() || $this->isDropping()) {
        if (!$this->isInSameLoc()) {
          throw new TooFarAwayException("not in same location");
        }
      } elseif ($this->isDragging()) {
        if (!($this->isFromInnerToOuter() || $this->isFromOuterToInner())) {
          throw new TooFarAwayException("not to adjacent location");
        }
      } elseif ($this->isGiving()) {
        if (!$this->from->isNearTo(($this->to))) {
          throw new TooFarAwayException("char not near");
        }
      }
    }

    if ($this->isGiving() || $this->isDragging()) {
      if ($this->from->getId() == $this->to->getId()) {
        return; // it's already in target location, nothing left to do
      }
    }

    if ($this->checkReceiver) {
      if ($this->isGiving() || $this->isTaking()) {
        if ($this->to->isNearDeath()) {
          throw new CharacterStatusException("receiver is in NDS");
        }
      }
    }

    if ($this->checkObjectSetting) { // check if object setting is correct for this action
      if ($this->isDragging()) {
        $canBeDragged = array(ObjectConstants::SETTING_PORTABLE,
          ObjectConstants::SETTING_QUANTITY, ObjectConstants::SETTING_HEAVY);
        if (!in_array($this->object->getSetting(), $canBeDragged)) {
          throw new InvalidObjectSettingException("");
        }
      } elseif ($this->isTaking() || $this->isDropping() || $this->isGiving()) {
        $canBeTaken = array(ObjectConstants::SETTING_PORTABLE, ObjectConstants::SETTING_QUANTITY);
        if (!in_array($this->object->getSetting(), $canBeTaken)) {
          throw new InvalidObjectSettingException("");
        }
      }
    }

    if ($this->checkCapacity) {

      if ($this->isGiving() || $this->isTaking()) { // $to is Character
        $inventoryWeight = $this->to->getInventoryWeight();
        if ($inventoryWeight + $this->weight > $this->to->getMaxInventoryWeight()) { // character capacity
          throw new WeightCapacityExceededException("Char " . $this->to->getId() . " is full");
        }
      }

      if (!$this->isInSameLoc() && $this->toLoc !== null) { // transfer in one location is always correct
        $locationWeight = $this->toLoc->getTotalWeight();
        if ($locationWeight + $this->weight > $this->toLoc->getMaxWeight()) { // locaton capacity
          throw new WeightCapacityExceededException("Location " . $this->toLoc->getId() . " is full");
        }
      }
    }

    $newLoc = ($this->isDragging() || $this->isDropping()) ? $this->to->getId() : 0;
    $newPerson = ($this->isTaking() || $this->isGiving()) ? $this->to->getId() : 0;
    if ($this->object->getSetting() == ObjectConstants::SETTING_QUANTITY) { // quantity objects
      // queries are done outside, not using Object's methods to keep us save from race conditions
      if ($this->weight == $this->object->getWeight()) {
        $this->object->remove();
        import_lib("func.expireobject.inc.php");
        $reallyRemoved = expire_object($this->object->getId());
      } else {
        $stm = $this->db->prepare("UPDATE objects SET weight = weight - :weightDecrease
          WHERE id = :objectId AND weight = :weight");
        $stm->bindInt("weightDecrease", $this->weight);
        $stm->bindInt("objectId", $this->object->getId());
        $stm->bindInt("weight", $this->object->getWeight());
        $stm->execute();
        $reallyRemoved = ($stm->rowCount() > 0);

        $this->object->setWeight($this->object->getWeight() - $this->weight);
      }
      if (!$reallyRemoved) {
        throw new BadInitialLocationException("");
      }
      $this->createQuantityObjects($newLoc, $newPerson); // it never uses same object in new location
    } else { // normal objects
      $this->object->setLocation($newLoc);
      $this->object->setPerson($newPerson);
      $stm = $this->db->prepare("UPDATE objects SET location = :locationId, person = :charId, ordering = 0 WHERE id = :objectId");
      $stm->bindInt("locationId", $newLoc);
      $stm->bindInt("charId", $newPerson);
      $stm->bindInt("objectId", $this->object->getId());
      $stm->execute();
      // TODO, There should be function Object::saveInDb()
      if ($this->object->getType() == ObjectConstants::TYPE_DEAD_BODY) {
        $stm = $this->db->prepare("UPDATE chars SET location = :locationId WHERE id = :charId");
        $stm->bindInt("locationId", $newLoc);
        $stm->bindInt("charId", $this->object->getTypeid());
        $stm->execute();
      }
      try {
        $translocationMonitor = new TranslocationMonitor();
        $translocationMonitor->recordObjectTranslocation($this->from, $this->to, $this->object);
      } catch (Exception $e) {
        $this->logger->warn("Exception when trying to record translocation of {$this->object->getId()}", $e);
      }
    }
  }

  private function createQuantityObjects($newLoc, $newPerson)
  {
    if ($this->object->getType() == ObjectConstants::TYPE_RAW) { // raw materials
      if ($newLoc > 0) {
        ObjectHandler::rawToLocation($newLoc, $this->object->getTypeid(), $this->weight);
      } else {
        ObjectHandler::rawToPerson($newPerson, $this->object->getTypeid(), $this->weight);
      }
    } elseif (in_array($this->object->getType(), ObjectConstants::$TYPES_COINS)) { // coins
      if ($newLoc > 0) {
        ObjectHandler::coinsToLocation($newLoc, $this->object->getType(),
          $this->object->getSpecifics(), $this->weight / ObjectConstants::WEIGHT_COIN);
      } else {
        ObjectHandler::coinsToPerson($newPerson, $this->object->getType(),
          $this->object->getSpecifics(), $this->weight / ObjectConstants::WEIGHT_COIN);
      }
    } // there are no quantity objects which are not raws or coins
  }

  public function isDragging()
  {
    return $this->from instanceof Location && $this->to instanceof Location;
  }

  public function isGiving()
  {
    return $this->from instanceof Character && $this->to instanceof Character;
  }

  public function isDropping()
  {
    return $this->from instanceof Character && $this->to instanceof Location;
  }

  public function isTaking()
  {
    return $this->from instanceof Location && $this->to instanceof Character;
  }

  /**
   * @return true when translocation is going to move object from outer location to inner location
   */
  public function isFromOuterToInner()
  {
    return $this->toLoc !== null && $this->fromLoc->getId() == $this->toLoc->getRegion();
  }

  public function isFromInnerToOuter()
  {
    return $this->toLoc !== null && $this->toLoc->getId() == $this->fromLoc->getRegion();
  }

  public function isInSameLoc()
  {
    return $this->toLoc !== null && $this->fromLoc->getId() == $this->toLoc->getId();
  }

  public function getInnerLocation()
  {
    return $this->isFromInnerToOuter() ? $this->fromLoc : $this->toLoc;
  }

  public function getOuterLocation()
  {
    return $this->isFromOuterToInner() ? $this->fromLoc : $this->toLoc;
  }

  public function setCheckCapacity($checkCapacity)
  {
    $this->checkCapacity = $checkCapacity;
    return $this;
  }

  public function setCheckNearness($checkNearness)
  {
    $this->checkNearness = $checkNearness;
    return $this;
  }

  public function setCheckReceiver($checkReceiver)
  {
    $this->checkReceiver = $checkReceiver;
    return $this;
  }

  public function setCheckObjectSetting($checkObjectSetting)
  {
    $this->checkObjectSetting = $checkObjectSetting;
    return $this;
  }

}
