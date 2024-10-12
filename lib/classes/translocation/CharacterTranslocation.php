<?php

/**
 * Responsible for moving characters from one location to another.
 * It's possible to enable/disable checks for key, capacity, direct link between locations
 */

class CharacterTranslocation
{

  private $char;
  private $from;
  private $to;

  private $checkNearness = true;
  private $checkInnerLock = true;
  private $checkOuterLock = false;
  private $checkCapacity = true;
  
  public function __construct(Character $char, Location $from, Location $to)
  {
    $this->char = $char;
    $this->from = $from;
    $this->to = $to;
  }

  /**
   * Checks if translocation is possible (based on >>check<< flags), performs the translocation
   * and immediately saves result in db or throws an exception when it's not possible.
   * Automatically removes participation in project/dragging
   * @throws BadInitialLocationException when char initial location is not $from
   * @throws TooFarAwayException when checkNearness is true and there's no direct connection between $to and $from
   * @throws NoKeyToInnerLockException when checkInnerLock is true and $char doesn't own key to lock of inner location
   * @throws NoKeyToOuterLockException when checkOuterLock is true and $char doesn't own key to lock of outer location
   * @throws WeightCapacityExceededException (checkCapacity==true) and moving $char would exceed weight capacity in $to
   * @throws PeopleCapacityExceededException (checkCapacity==true) and moving $char would exceed people capacity in $to
   */
  public function perform()
  {
    if ($this->char->getLocation() != $this->from->getId()) {
      throw new BadInitialLocationException("");
    }
    
    if ($this->checkNearness) { // we check if one of locations must be sublocation of the second one
      
      if (($this->to->getId() == 0) || !($this->isFromInnerToOuter() || $this->isFromOuterToInner())) {
        throw new TooFarAwayException($this->to->getId() ." not near of ". $this->from->getId());
      }

      // in case of double lock, exception about nearer one should be thrown first
      if ($this->isFromInnerToOuter()) {
        $this->tryCheckInnerLock();
        $this->tryCheckOuterLock();
      } else {
        $this->tryCheckOuterLock();
        $this->tryCheckInnerLock();
      }
    }
    
    if ($this->checkCapacity) {
      
      $charWeight = $this->char->getTotalWeight();
      $contentsWeight = $this->to->getTotalWeight();
      if ($charWeight + $contentsWeight > $this->to->getMaxWeight()) {
        throw new WeightCapacityExceededException("Location ". $this->to->getId() ." is full");
      }

      $charsInside = $this->to->getCharacterCount();
      if ($charsInside >= $this->to->getMaxCharacters()) {
        throw new PeopleCapacityExceededException("Location ". $this->to->getId() ." is overcrowded");
      }
    }
    
    $this->char->setLocation($this->to->getId());
    
    $this->char->setProject(0); // remove character from participation in project
    $this->char->saveInDb();

    try { // remove character from dragging sb else
      $dragging = Dragging::loadByDragger($this->char->getId());
      $dragging->removeDragger($this->char->getId());
      $dragging->saveInDb();
    } catch (InvalidArgumentException $e) {}
    try { // remove being victim of dragging
      $dragging = Dragging::loadByVictim(DraggingConstants::TYPE_HUMAN, $this->char->getId());
      $dragging->remove();
      $dragging->saveInDb();
    } catch (InvalidArgumentException $e) {}
    
  }

  private function tryCheckInnerLock()
  {
    if ($this->checkInnerLock) {
      $lock = KeyLock::loadByLocationId($this->getInnerLocation()->getId());
      if (!$lock->canAccess($this->char->getId())) {
        throw new NoKeyToInnerLockException("");
      }
    }
  }

  private function tryCheckOuterLock()
  {
    if ($this->checkOuterLock) {
      $lock = KeyLock::loadByLocationId($this->getOuterLocation()->getId());
      if (!$lock->canAccess($this->char->getId())) {
        throw new NoKeyToOuterLockException("");
      }
    }
  }

  /**
   * @return true when translocation is going to move char from outer location to inner extension
   */
  public function isFromOuterToInner()
  {
    return $this->from->getId() == $this->to->getRegion();
  }

  public function isFromInnerToOuter()
  {
    return $this->to->getId() == $this->from->getRegion();
  }

  public function getInnerLocation()
  {
    return $this->isFromInnerToOuter() ? $this->from : $this->to;
  }

  public function getOuterLocation()
  {
    return $this->isFromOuterToInner() ? $this->from : $this->to;
  }

  /**
   * Check if $char has keys to lock of inner location. Doesn't make sense when checkNearness == false
   * @return $this
   */
  public function setCheckInnerLock($checkInnerLock)
  {
    $this->checkInnerLock = $checkInnerLock;
    return $this;
  }

  /**
   * Check if $char has keys to lock of outer location. Doesn't make sense when checkNearness == false
   * @return $this
   */
  public function setCheckOuterLock($checkOuterLock)
  {
    $this->checkOuterLock = $checkOuterLock;
    return $this;
  }

  /**
   * Check if $char has keys to lock of inner and outer location. Doesn't make sense when checkNearness == false
   * @return $this
   */
  public function setCheckLocks($checkOuter, $checkInner)
  {
    $this->setCheckOuterLock($checkOuter);
    $this->setCheckInnerLock($checkInner);
    return $this;
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
}
