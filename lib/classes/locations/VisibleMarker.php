<?php

class VisibleMarker
{
  /** @var Location|CObject */
  private $marker;
  /** @var int */
  private $direction;
  /** @var bool */
  private $exactDirection;
  /** @var int */
  private $distance;
  /** @var string */
  private $customTag;
  /** @var bool */
  private $detailedName;

  public function __construct($marker, $direction, $exactDirection, $distance, $customTag = null, $detailedName = true)
  {
    $this->marker = $marker;
    $this->direction = $direction;
    $this->exactDirection = $exactDirection;
    $this->distance = $distance;
    $this->customTag = $customTag;
    $this->detailedName = $detailedName;
  }

  /**
   * @return Location|CObject
   */
  public function getMarker()
  {
    return $this->marker;
  }

  public function getText()
  {
    $distanceTagName = "<CANTR REPLACE NAME=dist_" . MapUtil::getDistanceTagName($this->distance) . ">";
    $direction = MapUtil::getDirectionTag($this->direction);
    if ($this->exactDirection) {
      $direction = $this->direction;
    }

    if ($this->customTag) {
      return "<CANTR REPLACE NAME=$this->customTag DIRECTION=" . urlencode($direction) .
        " DISTANCE=" . intval($this->distance) . " DISTANCE_TAG=" . urlencode($distanceTagName) . ">";
    } else {
      if ($this->marker instanceof Location) {
        if ($this->detailedName) {
          $fullEntityName = "<CANTR LOCNAME ID={$this->marker->getId()}> (<CANTR LOCDESC ID={$this->marker->getId()}>)";
        } else {
          $fullEntityName = "<CANTR LOCDESC ID={$this->marker->getId()}>";
        }
      } else {
        $objType = $this->detailedName ? 0 : 1;
        $fullEntityName = "<CANTR OBJNAME ID={$this->marker->getId()} TYPE=$objType>";
      }

      return "<CANTR REPLACE NAME=visible_entity_template ENTITY_TAG=" . urlencode($fullEntityName) .
        " DIRECTION=" . urlencode($direction) . " DISTANCE=" . urlencode($distanceTagName) . ">";
    }
  }

  public function canSeeExactDirection()
  {
    return $this->exactDirection;
  }

  public function canSeeDetailedName()
  {
    return $this->detailedName;
  }
}
