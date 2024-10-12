<?php

class Fists extends Weapon
{
  
  public function __construct() {}
  
  public function checkUseBy(Character $char)
  {
    return; // fists can always be used
  }
  
  public function getNameTag()
  {
    return "<CANTR REPLACE NAME=weapon_bare_fist>";
  }
  
  public function getSkillRelevance()
  {
    return 0.6;
  }

  public function getStrengthRelevance()
  {
    return 0.4;
  }

  /**
   * How much should 100% drunkenness theoretically increase (positive) or decrease (negative) attack value.
   * @return float increase/decrease multiplier. For example 0.5 means +50% to attack for 100% drunk.
   */
  public function getDrunkennessInfluence()
  {
    return 1.0;
  }

  public function getHit()
  {
    import_lib("stddef.inc.php");
    return _HIT_PERCENT_DOWN;
  }
  
  public function getAnimalHit()
  {
    import_lib("stddef.inc.php");
    return _HIT_PERCENT_DOWN;
  }

  public function getType()
  {
    return 0;
  }
  
  public function applyHitDeterioration($force)
  {
    // intentionally do nothing, because no deterioration should be applied
  }
} 
