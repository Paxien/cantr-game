<?php

class Weapon
{
  private $object;
  private $rules;

  public function __construct(CObject $object)
  {
    $this->object = $object;
    $this->rules = Parser::rulesToArray($this->object->getRules());
    if (!array_key_exists("hit", $this->rules) && !array_key_exists("animal_hit", $this->rules)) {
      throw new InvalidArgumentException("");
    }
  }

  /**
   * @throws TooFarAwayException when weapon is too far away
   * @throws AnimalNotLoyalException when animal weapon is not loyal to perpetrator
   * @return true when check is successful
   */
  public function checkUseBy(Character $char)
  {
    if ($this->object->getPerson() != $char->getId()) {
      throw new TooFarAwayException($char->getId() . " can't access weapon " . $this->object->getId());
    }

    // animal-weapon can be used only if it's loyal to owner
    if ($this->object->getObjectCategory()->getId() == ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS) {
      $animal = DomesticatedAnimalObject::loadById($this->object->getId());
      if ($animal == null || !$animal->isLoyalTo($char)) {
        throw new AnimalNotLoyalException($this->object->getId() . " is not loyal to " . $char->getId());
      }
    }
    return true;
  }

  public function getNameTag()
  {
    return "<CANTR REPLACE NAME=item_" . $this->object->getUniqueName() . "_o>";
  }

  public function getSkillRelevance()
  {
    return (isset($this->rules['weapon_skill_relevance']) ? $this->rules['weapon_skill_relevance'] : 0.6);
  }

  public function getStrengthRelevance()
  {
    return (isset($this->rules['weapon_skill_relevance']) ? (1 - $this->rules['weapon_skill_relevance']) : 0.4);
  }

  /**
   * How much should 100% drunkenness theoretically increase (positive) or decrease (negative) attack value.
   * @return float increase/decrease multiplier. For example 0.5 means +50% to attack for 100% drunk.
   */
  public function getDrunkennessInfluence()
  {
    return -0.25;
  }

  public function getHit()
  {
    return (isset($this->rules['hit']) ? $this->rules['hit'] : 0);
  }

  public function getAnimalHit()
  {
    return (isset($this->rules['animal_hit']) ? $this->rules['animal_hit'] : $this->getHit());
  }

  public function getType()
  {
    return $this->object->getType();
  }

  public function applyHitDeterioration($force)
  {
    import_lib("func.expireobject.inc.php");
    $decayFactor = $force / 80;
    usage_decay_object($this->object->getId(), $decayFactor);
  }
}
