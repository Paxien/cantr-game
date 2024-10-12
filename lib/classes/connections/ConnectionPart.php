<?php

class ConnectionPart
{
  /** @var int */
  private $deterioration;
  /** @var ConnectionType */
  private $type;

  public function __construct($deterioration, $typeId)
  {
    $this->deterioration = $deterioration;
    $this->type = ConnectionType::loadById($typeId);
  }

  /**
   * @return ConnectionType
   */
  public function getType()
  {
    return $this->type;
  }

  public function setType(ConnectionType $type)
  {
    $this->type = $type;
  }

  /**
   * @return int
   */
  public function getDeterioration()
  {
    return $this->deterioration;
  }

  public function setDeterioration($deterioration)
  {
    $this->deterioration = Measure::between($deterioration, [0, 10000]);
  }
}
