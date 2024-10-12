<?php

class StandardEatingStrategy implements EatingStrategy
{
  public function getCurrentCoefficient($stateName)
  {
    $states = [
      StateConstants::HUNGER => 1,
      StateConstants::HEALTH => 1,
      StateConstants::TIREDNESS => 1,
      StateConstants::DRUNKENNESS => 1,
    ];
    return $states[$stateName];
  }
} 
