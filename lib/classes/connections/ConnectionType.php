<?php

class ConnectionType
{
  /** @var int */
  private $id;
  /** @var string */
  private $name;
  /** @var int */
  private $speedFactor;
  /** @var int */
  private $speedLimit;
  /** @var array */
  private $allowedVehicles;
  /** @var  int */
  private $deterPerDay;
  /** @var bool */
  private $destroyable;
  /** @var int */
  private $improvedFrom;

  private function __construct($id)
  {
    $this->id = $id;
  }

  public static function loadByName($name)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM connecttypes WHERE name = :name");
    $stm->bindStr("name", $name);
    $id = $stm->executeScalar();
    return self::loadById($id);
  }

  public static function loadById($id)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM connecttypes WHERE id = :id");
    $stm->bindInt("id", $id);
    $stm->execute();
    if ($fetchedType = $stm->fetchObject()) {
      $connectType = new self($id);
      $connectType->name = $fetchedType->name;
      $connectType->speedFactor = $fetchedType->speed_factor;
      $connectType->speedLimit = $fetchedType->speedlimit;
      $connectType->allowedVehicles = explode(",", $fetchedType->vehicles);
      $connectType->deterPerDay = $fetchedType->deter_rate_turn;
      $connectType->destroyable = false;  //!!$fetchedType->destroyable; // TODO!!!
      $connectType->improvedFrom = $fetchedType->improved_from;

      return $connectType;
    }
    throw new InvalidArgumentException("no connection type for id " . $id);
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return int
   */
  public function getSpeedFactor()
  {
    return $this->speedFactor;
  }

  /**
   * @return int
   */
  public function getSpeedLimit()
  {
    return $this->speedLimit;
  }

  /**
   * @return int[]
   */
  public function getAllowedVehicles()
  {
    return $this->allowedVehicles;
  }

  /**
   * @return int
   */
  public function getDeterPerDay()
  {
    return $this->deterPerDay;
  }

  /**
   * @return boolean
   */
  public function isDestroyable()
  {
    return $this->destroyable;
  }

  /**
   * @return bool true if this type is built as a new type, not improvement of the already existing one
   */
  public function isPrimaryType() {
    return $this->getImprovedFrom() == 0;
  }

  /**
   * @return int|0 for connection types for which isPrimaryType() is true.
   */
  public function getImprovedFrom()
  {
    if ($this->improvedFrom == 0) {
      return null;
    }
    return $this->improvedFrom;
  }


}