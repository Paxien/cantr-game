<?php

class Resource
{

  private $name;
  private $subject;

  private $nutrition;
  private $strengthening;
  private $energy;
  private $drunkenness;
  /** @var Db */
  private $db;

  public function __construct(CObject $raw)
  {
    $this->db = Db::get();
    if ($raw->getType() != ObjectConstants::TYPE_RAW) {
      throw new InvalidArgumentException($raw->getId() . " is not a raw material");
    }
    $this->subject = $raw;

    $stm = $this->db->prepare("SELECT name, nutrition, strengthening, energy, drunkenness FROM rawtypes WHERE id = :id");
    $stm->bindInt("id", $raw->getTypeid());
    $stm->execute();
    $resourceInfo = $stm->fetchObject();

    $this->name = $resourceInfo->name;
    $this->nutrition = $resourceInfo->nutrition;
    $this->strengthening = $resourceInfo->strengthening;
    $this->energy = $resourceInfo->energy;
    $this->drunkenness = $resourceInfo->drunkenness;
  }

  public function isEdible()
  {
    return $this->isNutritious() || $this->isStrengthening() || $this->isEnergetic() || $this->isIntoxicating();
  }

  public function isNutritious()
  {
    return $this->getNutrition() != 0;
  }

  public function isStrengthening()
  {
    return $this->getStrengthening() != 0;
  }

  public function isEnergetic()
  {
    return $this->getEnergy() != 0;
  }

  public function isIntoxicating()
  {
    return $this->getDrunkenness() != 0;
  }

  public function getNutrition()
  {
    return $this->nutrition;
  }

  public function getStrengthening()
  {
    return $this->strengthening;
  }

  public function getEnergy()
  {
    return $this->energy;
  }

  public function getDrunkenness()
  {
    return $this->drunkenness;
  }

  /**
   * Returns array of key-value pairs, where
   * key is id of state and value efficiency of altering the state.
   * Returns only states for which efficiency is non-zero
   */
  public function getEatingEffects()
  {
    return Pipe::from([
      StateConstants::HUNGER => $this->getNutrition(),
      StateConstants::HEALTH => $this->getStrengthening(),
      StateConstants::TIREDNESS => $this->getEnergy(),
      StateConstants::DRUNKENNESS => $this->getDrunkenness(),
    ])->filter(function($effect) { return $effect != 0; })->toArray();
  }

  public function getEfficienciesPerGram() {
    $baseEfficiency = [
      StateConstants::HEALTH      => EatingConstants::EATING_EFFICIENCY_HEALTH,
      StateConstants::TIREDNESS   => EatingConstants::EATING_EFFICIENCY_TIREDNESS,
      StateConstants::HUNGER      => EatingConstants::EATING_EFFICIENCY_HUNGER,
      StateConstants::DRUNKENNESS => EatingConstants::EATING_EFFICIENCY_DRUNKENNESS,
    ];
    $result = [];
    foreach ($this->getEatingEffects() as $stateType => $efficiency) {
      $result[$stateType] = $efficiency / 100 * $baseEfficiency[$stateType];
    }
    return $result;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getUniqueName()
  {
    return str_replace(" ", "_", $this->getName());
  }

  public function getRawType()
  {
    return $this->subject->getTypeid();
  }

  public function getWeight()
  {
    return $this->subject->getWeight();
  }

  public function getLocation()
  {
    return $this->subject->getLocation();
  }

  public function getPerson()
  {
    return $this->subject->getPerson();
  }

  public function getAttached()
  {
    return $this->subject->getAttached();
  }
}
