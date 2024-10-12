<?php

class DraggingManager
{
  private $involvedChars;
  private $logger;
  /** @var Db */
  private $db;

  public function __construct($involvedChars)
  {
    if (!is_array($involvedChars)) {
      $involvedChars = array($involvedChars);
    }

    // validate if all char ids are positive integers
    foreach ($involvedChars as $charId) {
      if (!Validation::isPositiveInt($charId)) {
        throw new InvalidArgumentException("char id $charId is not a positive int");
      }
    }
    $this->involvedChars = $involvedChars;
    $this->logger = Logger::getLogger(__CLASS__);
    $this->db = Db::get();
  }

  public function tryFinishingAll()
  {
    $stm = $this->db->prepareWithIntList("SELECT id FROM dragging
      WHERE victimtype = :victimType AND victim IN (:victims)", [
      "victims" => $this->involvedChars,
    ]);
    $stm->bindInt("victimType", DraggingConstants::TYPE_HUMAN);
    $stm->execute();
    foreach ($stm->fetchScalars() as $draggingId) {
      $this->logger->debug("trying to finish $draggingId");
      try {
        $this->tryFinishDragging($draggingId);
      } catch (DraggingNotCompletedException $e) {
        $this->logger->debug("Dragging $draggingId not completed", $e);
      } catch (Exception $e) {
        $this->logger->warn("Unexpected exception when trying to finish dragging $draggingId (victim part)", $e);
      }
    }

    $stm = $this->db->prepareWithIntList("SELECT dragging_id FROM draggers WHERE dragger IN (:draggers)", [
      "draggers" => $this->involvedChars,
    ]);
    $stm->execute();
    foreach ($stm->fetchScalars() as $draggingId) {
      $this->logger->debug("finish $draggingId");
      try {
        $this->tryFinishDragging($draggingId);
      } catch (DraggingNotCompletedException $e) {
        $this->logger->debug("Dragging $draggingId not completed", $e);
      } catch (Exception $e) {
        $this->logger->warn("Unexpected exception when trying to finish dragging $draggingId", $e);
      }
    }
  }

  private function tryFinishDragging($draggingId)
  {
    /*
     * SETTING UP VARIABLES
     */

    $dragging = Dragging::loadById($draggingId); // throws InvalidArgumentException

    $this->logger->debug("############# It's dragging of $draggingId #############");

    $isVictimHuman = ($dragging->getVictimType() == DraggingConstants::TYPE_HUMAN);
    $isVictimObject = ($dragging->getVictimType() == DraggingConstants::TYPE_OBJECT);
    $isDraggingFromProject = ($dragging->getGoal() == DraggingConstants::GOAL_FROM_PROJECT);

    if ($isVictimHuman) {
      $victimChar = Character::loadById($dragging->getVictim()); // throws InvalidArgumentException

      // check if victim human is alive
      if (!$victimChar->isAlive()) {
        $this->finishWithEvent($dragging, 321, "VICTIM=" . $victimChar->getId());
        throw new DraggingNotCompletedException("victim " . $dragging->getVictim() . " dead");
      }
    } elseif ($isVictimObject) {
      $victimObject = CObject::loadById($dragging->getVictim()); // throws InvalidArgumentException
    } else {
      $this->finishDragging($dragging);
      $this->logger->error("dragging " . $dragging->getId() . " has incorrect victimtype: " . $dragging->getVictimType());
    }

    if ($isVictimHuman) {
      $fromLocId = $victimChar->getLocation();
    } else {
      $fromLocId = $victimObject->getLocation();
    }

    $this->logger->debug("fromLocId: $fromLocId");

    try {
      if ($fromLocId == 0) {
        $fromLocId = -1; // to trigger error, because 0 is correct location!
      }
      $fromLoc = Location::loadById($fromLocId);
    } catch (InvalidArgumentException $e) {
      if ($isVictimHuman) {
        $this->finishWithEvent($dragging, 321, "VICTIM=" . $victimChar->getId());
      } elseif ($isVictimObject) {
        $this->finishWithEvent($dragging, 320, "OBJECT=" . $victimObject->getId());
      }
      throw new IllegalStateException("invalid initial loc $fromLocId");
    }

    /*
     * DRAGGING PRELIMINARIES
     */

    $toLocId = $dragging->getGoal();
    if (!$isDraggingFromProject) {
      try {
        if ($toLocId == 0) {
          $toLocId = -1; // to trigger error, because 0 is correct location!
        }
        $toLoc = Location::loadById($toLocId);
      } catch (InvalidArgumentException $e) {
        $this->finishWithEvent($dragging, 322, "");
        throw new IllegalStateException("invalid goal location");
      }
    }

    if ($isDraggingFromProject && !$isVictimHuman) {
      $this->logger->error("SHOULD NEVER HAPPEN. Dragging " . $dragging->getId()
        . " of object " . $victimObject->getId() . " from a project");
      throw new IllegalStateException("trying to drag non-human from project");
    }

    if (!$isDraggingFromProject) {
      $isFromOuterToInner = $fromLoc->getId() == $toLoc->getRegion();
      $isFromInnerToOuter = $fromLoc->getRegion() == $toLoc->getId();

      if (!($isFromInnerToOuter || $isFromOuterToInner)) {
        $this->finishWithEvent($dragging, 322, "");
        throw new DraggingNotCompletedException("goal " . $toLoc->getId() . " too far away");
      }
    }

    // remove draggers who are not in location of victim or goal location
    foreach ($dragging->getDraggers() as $draggerId) {
      $draggerLoc = Character::loadById($draggerId)->getLocation();
      if (!in_array($draggerLoc, array($fromLocId, $toLocId))) {
        $dragging->removeDragger($draggerId);
        $this->logger->debug("removing $draggerId from dragging, because too far away");
      }
    }

    $dragging->saveInDb();
    if (!$dragging->hasId()) { // last dragger was removed
      throw new DraggingNotCompletedException("no draggers"); // so there's nobody to notify
    }

    /*
     * CHECKING STRENGTH REQUIREMENT
     */

    if (!$dragging->isEnoughStrength()) {
      throw new DraggingNotCompletedException("not enough str"); // wait for more draggers
    }
    $this->logger->debug("it's enough strength");

    if ($isDraggingFromProject && $isVictimHuman) {
      $this->logger->debug("dragging from a project");
      if ($victimChar->getProject() > 0) {
        $victimChar->setProject(0);
        $victimChar->saveInDb();

        $this->eventForDraggingFromProjectSuccess($dragging, $victimChar);
      } else {
        $this->logger->info("dragging " . $dragging->getId() . " of char " . $victimChar->getId()
          . " from a project was about dragging a char who doesn't take part in any project");
      }
      $this->finishDragging($dragging);
    } else { // dragging to another location
      $this->logger->debug("dragging to different location");
      if ($isVictimHuman) {
        $translocation = new CharacterTranslocation($victimChar, $fromLoc, $toLoc);
      } elseif ($isVictimObject) {
        $maxAmount = ($victimObject->getUnitWeight() > 0) ? ($dragging->getWeight() / $victimObject->getUnitWeight()) : 0;
        $toTransfer = min($victimObject->getAmount(), floor($maxAmount)); // 0 is correct, means "everything"
        $translocation = new ObjectTranslocation($victimObject, $fromLoc, $toLoc, $toTransfer);
      }

      $victim = ($isVictimHuman) ? $victimChar : $victimObject;
      // check if draggers have all required keys
      list ($canAccessInnerLock, $canAccessOuterLock) =
        $this->checkRequiredKeys($dragging, $fromLoc, $toLoc, $translocation, $victim);

      if (!($canAccessInnerLock && $canAccessOuterLock)) {
        if ($isVictimHuman) {
          $this->finishWithEvent($dragging, 103, "VICTIM=" . $victimChar->getId());
        } else {
          $this->finishWithEvent($dragging, 132, "TYPE=1 OBJECT=" . $victimObject->getId());
        }
        throw new DraggingNotCompletedException("no key");
      }

      if ($isVictimHuman) {
        // nearness is checked earlier, only draggers need a key
        $translocation->setCheckNearness(false)->setCheckCapacity(true)->setCheckLocks(false, false);
        try {

          // workaround to always have space for at least one person with a key
          $this->checkSpaceForAtLeastOneChar($dragging, $toLoc, $victimChar);

          $translocation->perform();
          $this->logger->debug("translocation to " . $toLoc->getId() . " performed successfully");

          PlayersMonitoring::reportDragging($dragging, $victimChar, $fromLoc, $toLoc, Db::get());
          $this->eventForDraggingHumanSuccess($dragging, $fromLoc, $toLoc, $victimChar);

          $this->finishDragging($dragging);

        } catch (BadInitialLocationException $e) {
          $this->finishWithEvent($dragging, 321, "VICTIM=" . $victimChar->getId());
          throw new DraggingNotCompletedException("bad initial location");
        } catch (WeightCapacityExceededException $e) {
          $this->finishWithEvent($dragging, 101, "VICTIM=" . $victimChar->getId());
          throw new DraggingNotCompletedException("no weight capacity");
        } catch (PeopleCapacityExceededException $e) {
          $this->finishWithEvent($dragging, 100, "VICTIM=" . $victimChar->getId());
          throw new DraggingNotCompletedException("no people capacity");
        }
      } elseif ($isVictimObject) {
        // nearness is checked earlier, receiver is never human
        $translocation->setCheckNearness(false)->setCheckCapacity(true);
        $translocation->setCheckObjectSetting(true)->setCheckReceiver(false);
        try {

          // workaround to always have space for at least one person with a key
          if ($toLoc->getCharacterCount() == 0) { // only if there's nobody inside
            $this->checkSpaceForAtLeastOneChar($dragging, $toLoc, $victimObject);
          }

          $translocation->perform();
          $this->logger->debug("translocation to " . $toLoc->getId() . " performed successfully");

          PlayersMonitoring::reportDragging($dragging, $victimObject, $fromLoc, $toLoc, Db::get());
          $this->eventForDraggingObjectSuccess($dragging, $fromLoc, $toLoc, $victimObject);

          $this->finishDragging($dragging);

        } catch (BadInitialLocationException $e) {
          $this->finishWithEvent($dragging, 320, "OBJECT=" . $victimObject->getId());
          throw new DraggingNotCompletedException("bad initial location");
        } catch (WeightCapacityExceededException $e) {
          $this->finishWithEvent($dragging, 131, "OBJECT=" . $victimObject->getId() . " TYPE=1");
          throw new DraggingNotCompletedException("no weight capacity");
        } catch (InvalidObjectSettingException $e) {
          $this->finishWithEvent($dragging, 319, "OBJECT=" . $victimObject->getId());
          $this->logger->error("Dragging " . $dragging->getId()
            . " of (probably) a fixed object: " . $dragging->getVictim(), $e);
          throw new DraggingNotCompletedException("can't move this object");
        }
      }
    }
  }

  private function checkRequiredKeys($dragging, $fromLoc, $toLoc, $translocation, $victim)
  {

    // notes and envelopes are special - they can be dragged without a key
    if ($dragging->getVictimType() == DraggingConstants::TYPE_OBJECT) {
      if (in_array($victim->getType(), array(ObjectConstants::TYPE_NOTE, ObjectConstants::TYPE_ENVELOPE))) {
        return array(true, true);
      }
    }

    $canAccessInnerLock = false;
    $shipTypes = Location::getShipTypeArray();
    $canAccessOuterLock = !((in_array($fromLoc->getArea(), $shipTypes)) &&
      (in_array($toLoc->getArea(), $shipTypes))); // dragging from ship to ship, both keys needed

    $this->logger->debug("initial lock access: inner $canAccessInnerLock outer $canAccessOuterLock");

    $outerLock = KeyLock::loadByLocationId($translocation->getOuterLocation()->getId());
    $innerLock = KeyLock::loadByLocationId($translocation->getInnerLocation()->getId());
    foreach ($dragging->getDraggers() as $draggerId) {
      if ($outerLock->canAccess($draggerId)) {
        $canAccessOuterLock = true;
      }
      if ($innerLock->canAccess($draggerId)) {
        $canAccessInnerLock = true;
      }
    }
    $this->logger->debug("final lock access: inner $canAccessInnerLock outer $canAccessOuterLock");

    $access = array($canAccessInnerLock, $canAccessOuterLock);
    return $access;
  }

  private function eventForDraggingFromProjectSuccess($dragging, $victimChar)
  {
    $draggersList = $this->prepareEventDraggersString($dragging->getDraggers());

    // event for victim
    Event::createPersonalEvent(211, "$draggersList", $victimChar->getId()); // victim

    // event for other people in location
    Event::createPublicEvent(212, "VICTIM=" . $victimChar->getId() . " $draggersList", $victimChar->getId(), Event::RANGE_SAME_LOCATION, array($victimChar->getId()));
  }

  private function eventForDraggingHumanSuccess($dragging, $fromLoc, $toLoc, $victimChar)
  {
    $isFromOutside = ($fromLoc->getType() == LocationConstants::TYPE_OUTSIDE);
    $fromLocTag = urlencode("<CANTR LOCNAME ID=" . $fromLoc->getId() . ">");
    $toLocTag = urlencode("<CANTR LOCNAME ID=" . $toLoc->getId() . ">");

    $draggersList = $this->prepareEventDraggersString($dragging->getDraggers());

    // used both for events in initial and target location
    $eventVars = "VICTIM=" . $victimChar->getId() . " START=" . $fromLocTag
      . " DESTINATION=" . $toLocTag . " $draggersList";

    // events for people in initial location
    $eventId = ($isFromOutside) ? 149 : 152;
    Event::createEventInLocation($eventId, $eventVars, $fromLoc->getId(),
      Event::RANGE_SAME_LOCATION, array($victimChar->getId()));

    // events for people in target location
    $eventId = ($isFromOutside) ? 150 : 153;
    Event::createEventInLocation($eventId, $eventVars, $toLoc->getId(),
      Event::RANGE_SAME_LOCATION, array($victimChar->getId()));

    // events for victim
    $eventId = ($isFromOutside) ? 148 : 151;
    $eventVars = "COUNT=" . ($toLoc->getCharacterCount() - 1) . " START=" . $fromLocTag
      . " DESTINATION=" . $toLocTag . " $draggersList";
    Event::createPersonalEvent($eventId, $eventVars, $victimChar->getId());
  }

  private function eventForDraggingObjectSuccess($dragging, $fromLoc, $toLoc, $victimObject)
  {
    $isFromOutside = ($fromLoc->getType() == LocationConstants::TYPE_OUTSIDE);
    $fromLocTag = urlencode("<CANTR LOCNAME ID=" . $fromLoc->getId() . ">");
    $toLocTag = urlencode("<CANTR LOCNAME ID=" . $toLoc->getId() . ">");

    $draggersList = $this->prepareEventDraggersString($dragging->getDraggers());

    $eventVars = "OBJECT=" . $victimObject->getId() . " TYPE=1 START=" . $fromLocTag
      . " DESTINATION=" . $toLocTag . " $draggersList";

    // events for people in initial location
    $eventId = ($isFromOutside) ? 134 : 137;
    Event::createEventInLocation($eventId, $eventVars, $fromLoc->getId(), Event::RANGE_SAME_LOCATION);

    // events for people in initial location
    $eventId = ($isFromOutside) ? 135 : 138;
    Event::createEventInLocation($eventId, $eventVars, $toLoc->getId(), Event::RANGE_SAME_LOCATION);
  }

  private function checkSpaceForAtLeastOneChar($dragging, $toLoc, $victim)
  {
    $CHAR_WITH_KEY_WEIGHT = 60000 + 10;
    if ($victim instanceof Character) {
      $victimWeight = $victim->getTotalWeight();
    } else {
      if (!$victim->isQuantity() || $dragging->getWeight() == 0) {
        $victimWeight = $victim->getWeight();
      } else {
        $victimWeight = $dragging->getWeight();
      }
    }
    if (($toLoc->getTotalWeight() + $victimWeight + $CHAR_WITH_KEY_WEIGHT) > $toLoc->getMaxWeight()) {
      throw new WeightCapacityExceededException("there won't be enough space inside for another char with the key");
    }
  }

  private function eventForDraggers($dragging, $eventId, $vars)
  {
    foreach ($dragging->getDraggers() as $draggerId) {
      Event::createPersonalEvent($eventId, $vars, $draggerId);
    }
  }

  private function finishDragging($dragging)
  {
    $dragging->remove();
    $dragging->saveInDb();
  }

  private function finishWithEvent($dragging, $eventId, $vars)
  {
    $this->eventForDraggers($dragging, $eventId, $vars);
    $this->finishDragging($dragging);
  }

  private function prepareEventDraggersString($draggers)
  {
    $draggersWithPrefix = array();
    foreach ($draggers as $draggerId) {
      $draggersWithPrefix[] = "ACTORS=" . $draggerId;
    }
    return implode(" ", $draggersWithPrefix);
  }
}
