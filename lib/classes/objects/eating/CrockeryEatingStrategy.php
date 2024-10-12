<?php

class CrockeryEatingStrategy implements EatingStrategy
{
  public function getCurrentCoefficient($stateName)
  {
    $states = [
      StateConstants::HUNGER => 1,
      StateConstants::HEALTH => 1,
      StateConstants::TIREDNESS => 1,
      StateConstants::DRUNKENNESS => 10,
    ];
    return $states[$stateName];
  }
} 
