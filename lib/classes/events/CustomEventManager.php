<?php

/**
 * @author Aleksander Chrabaszcz
 */
class CustomEventManager
{
  private $eventName;

  private $subject;
  private $char;
  private $customEventProp;

  public function __construct(Character $char, CObject $object, $eventName)
  {
    $this->eventName = $eventName;
    $this->subject = $object;
    $this->char = $char;
    $this->customEventProp = $this->subject->getProperty("CustomEvent")[$this->eventName];
  }

  public function validate()
  {
    if (!$this->customEventProp) {
      return "error_not_authorized";
    }

    if (!$this->subject->hasAccessToAction($this->getFullActionName())) {
      return "error_not_authorized";
    }

    if ($this->subject->getPerson() > 0) {
      if (!$this->char->hasInInventory($this->subject)) {
        return "error_too_far_away";
      }
    } elseif ($this->subject->getLocation() > 0) {
      if (!$this->char->isInSameLocationAs($this->subject)) {
        return "error_too_far_away";
      }
    } else {
      return "error_too_far_away";
    }

    if ($this->customEventProp["onlyOutside"]) {
      if (!$this->char->isTravelling()) {
        $location = Location::loadById($this->char->getLocation());
        if ($location->getType() == LocationConstants::TYPE_BUILDING) {
          return "error_disallowed_in_building";
        }
      }
    }

    return true;
  }

  private function getFullActionName()
  {
    return "custom_event/" . $this->eventName;
  }

  public function callEvent()
  {
    $gender = $this->char->getSex();

    if ($this->customEventProp["actorEventTag"]) {
      $eventTagName = $this->customEventProp["actorEventTag"];
      Event::create(362, "OBJECT=" . $this->subject->getId() . " TEXT=$eventTagName GENDER=$gender")
        ->forCharacter($this->char)->show();
    }

    if ($this->customEventProp["othersEventTag"]) {
      $eventTagName = $this->customEventProp["othersEventTag"];
      Event::create(361, "ACTOR=" . $this->char->getId() . " OBJECT=" . $this->subject->getId() . " TEXT=$eventTagName GENDER=$gender")
        ->nearCharacter($this->char)->andAdjacentLocations()->except($this->char)->show();
    }

    if ($this->customEventProp["distantEventTag"] && $this->customEventProp["distantRange"]) {
      $eventTagName = $this->customEventProp["distantEventTag"];
      $range = $this->customEventProp["distantRange"];

      $pos = $this->char->getPos();
      Event::create(363, "ACTOR=" . $this->char->getId() . " OBJECT=" . $this->subject->getId() . " TEXT=$eventTagName GENDER=$gender")
        ->inRadius($pos["x"], $pos["y"], $range, false)->show();

      $stat = new Statistic('area_event', Db::get());
      $stat->store($this->subject->getUniqueName(), $this->char->getId(), 1);
    }

    $this->applyUseDeterioration();
    $this->subject->saveInDb();

    if ($this->subject->getDeterioration() >= 10000) {
      include_once( _LIB_LOC ."/func.expireobject.inc.php");
      expire_object($this->subject->getId());
    }
  }

  public function applyUseDeterioration() {
    $deterPerUse = $this->subject->getDeterRatePerUse();
    $this->subject->alterDeterioration($deterPerUse);
  }
}
