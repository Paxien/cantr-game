<?php

import_lib("func.genes.inc.php");

class CharacterStomach
{
  /** * @var Character */
  private $char;
  /** @var Db */
  private $db;

  public static function ofCharacter(Character $char)
  {
    return new CharacterStomach($char);
  }

  private function __construct($char)
  {
    $this->char = $char;
    $this->db = Db::get();
  }

  public function checkEating(Resource $food)
  {
    if (!$food->isEdible()) {
      throw new InvalidObjectPropertyException();
    }

    $notEatAfterNearDeath = Limitations::getLims($this->char->getId(), Limitations::TYPE_NOT_EAT_AFTER_NEAR_DEATH);
    if ($notEatAfterNearDeath > 0) {
      $secsLeft = Limitations::getTimeLeft($this->char->getId(), Limitations::TYPE_NOT_EAT_AFTER_NEAR_DEATH);
      $exception = new ExistingLimitationException("It's not yet possible to eat because of recent NDS!");
      $exception->setTimeLeft(GameDate::fromTimestamp($secsLeft));
      throw $exception;
    }
  }

  public function eat(Resource $food, $amount, EatingStrategy $strategy = null)
  {
    if ($strategy == null) {
      $strategy = new StandardEatingStrategy();
    }

    $this->checkEating($food);

    $spaceLeft = $this->getStomachMaxCapacity() - $this->getStomachContentsWeight();

    if (!Validation::isPositiveInt($amount) || $amount > $food->getWeight()) {
      throw new InvalidAmountException();
    }

    if ($amount > $spaceLeft) {
      throw new WeightCapacityExceededException();
    }

    /* ******* EAT ******** */

    if ($food->getPerson() > 0) {
      $successful = ObjectHandler::rawToPerson($food->getPerson(), $food->getRawType(), (-1) * $amount);
    } elseif ($food->getAttached() > 0) {
      $successful = ObjectHandler::rawToContainer($food->getAttached(), $food->getRawType(), (-1) * $amount);
    }
    if (!$successful) {
      throw new InvalidAmountException();
    }

    // all food except alcohol can be stored in stomach to be used later
    $delayableEffectParams = array_diff_key($food->getEatingEffects(), [StateConstants::DRUNKENNESS => 1]);

    //In this section we define how much of the eaten amount gets digested instantly
    //- the part that has useful effects in curing either hunger, damage or tiredness
    $digestNow = 0;

    if ($food->isIntoxicating()) { // alcohol should be drunk instantly
      $digestNow = $amount;
    } else { // other food - not necessarily
      foreach ($delayableEffectParams as $stateType => $efficiency) {
        $currentEfficiency = $efficiency * $strategy->getCurrentCoefficient($stateType);

        $digestNow = max($digestNow, $this->getMaxEdibleNow($stateType, $currentEfficiency, $amount));
      }
    }

    $digestLater = $amount - $digestNow;

    foreach ($food->getEatingEffects() as $stateType => $efficiency) {
      $currentEfficiency = $efficiency * $strategy->getCurrentCoefficient($stateType);
      $this->eatResults($digestNow, $currentEfficiency, $stateType);
    }

    // remember amount in stomach
    $turnInt = GameDate::NOW()->getIntInDbFormat();
    if ($digestNow > 0) {
      $consumptionStats = new Statistic("consumed", $this->db);
      $consumptionStats->update($food->getName(), 0, $digestNow);
      //digested food gets recorded as dung, typeid=294
      $stm = $this->db->prepare("INSERT INTO stomach (person, food, weight, eaten_date)
        VALUES (:charId, 294, :weight1, :date)
        ON DUPLICATE KEY UPDATE weight = weight + :weight2");
      $stm->bindInt("charId", $this->char->getId());
      $stm->bindInt("weight1", $digestNow);
      $stm->bindInt("weight2", $digestNow);
      $stm->bindInt("date", $turnInt);
      $stm->execute();
    }
    if ($digestLater > 0) {
      $stm = $this->db->prepare("INSERT INTO stomach (person, food, weight, eaten_date)
        VALUES (:charId, :rawType, :weight1, :date)
        ON DUPLICATE KEY UPDATE weight = weight + :weight2");
      $stm->bindInt("charId", $this->char->getId());
      $stm->bindInt("rawType", $food->getRawType());
      $stm->bindInt("weight1", $digestLater);
      $stm->bindInt("weight2", $digestLater);
      $stm->bindInt("date", $turnInt);
      $stm->execute();
    }
  }

  public function getMaxEdibleNow($paramType, $efficiency, $amount)
  {
    $edibleNow = 0;
    if ($efficiency > 0) {
      $maxUseful = $this->neededForMax($paramType, $efficiency);
      if ($maxUseful > 0) {
        $edibleNow = min($maxUseful, $amount);
      }
    } elseif ($efficiency < 0) {
      $edibleNow = $amount;
    }
    return $edibleNow;
  }

  private function eatResults($amount, $efficiency, $type)
  {
    $results = [
      StateConstants::HEALTH => EatingConstants::EATING_EFFICIENCY_HEALTH,
      StateConstants::TIREDNESS => EatingConstants::EATING_EFFICIENCY_TIREDNESS,
      StateConstants::DRUNKENNESS => EatingConstants::EATING_EFFICIENCY_DRUNKENNESS,
      StateConstants::HUNGER => EatingConstants::EATING_EFFICIENCY_HUNGER,
    ];
    $percUp = $amount * $efficiency / 100 * $results[$type];
    $percUp = floor(abs($percUp)) * ($percUp > 0 ? 1 : -1);
    $this->char->alterState($type, $percUp);
  }

  public function neededForMax($type, $efficiency)
  {
    switch ($type) {
      case StateConstants::HEALTH:
        $factor = EatingConstants::EATING_EFFICIENCY_HEALTH;
        $needed = _SCALESIZE_GSS - $this->char->getState(StateConstants::HEALTH);
        break;
      case StateConstants::TIREDNESS:
        $factor = EatingConstants::EATING_EFFICIENCY_TIREDNESS;
        $needed = -1 * $this->char->getState(StateConstants::TIREDNESS);
        break;
      case StateConstants::DRUNKENNESS:
        $factor = EatingConstants::EATING_EFFICIENCY_DRUNKENNESS;
        $needed = _SCALESIZE_GSS - $this->char->getState(StateConstants::DRUNKENNESS);
        break;
      case StateConstants::HUNGER:
        $factor = EatingConstants::EATING_EFFICIENCY_HUNGER;
        $needed = -1 * $this->char->getState(StateConstants::HUNGER);
        break;
    }
    return ceil($needed / $efficiency / $factor * 100);
  }

  public function removeDigestedContents()
  {
    $stm = $this->db->prepare("DELETE FROM stomach WHERE person = :charId AND food = 294");
    $stm->bindInt("charId", $this->char->getId());
    $stm->execute();
  }

  /**
   * Completely removes all contents of stomach
   */
  public function purge()
  {
    $stm = $this->db->prepare("DELETE FROM stomach WHERE person = :charId");
    $stm->bindInt("charId", $this->char->getId());
    $stm->execute();
  }

  public function getStomachContentsWeight()
  {
    $stm = $this->db->prepare("SELECT SUM(weight) FROM stomach WHERE person = :charId");
    $stm->bindInt("charId", $this->char->getId());
    return intval($stm->executeScalar());
  }

  public function getStomachMaxCapacity()
  {
    return EatingConstants::STOMACH_CAPACITY;
  }
}
