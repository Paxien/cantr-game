<?php

class Steed extends LandVehicle {

  private $fullness; // [0, 10000]
  private $loyalTo;  // owner id
  private $loyalty;  //[0,10000];

  public function __construct(stdClass $fetchedObject, stdClass $fetchedTypeData, stdClass $fetchedAnimalDetails, Db $db)
  {
    parent::__construct($fetchedObject, $fetchedTypeData, $db);

    $this->fullness = $fetchedAnimalDetails->fullness;
    $this->loyalTo = $fetchedAnimalDetails->loyal_to;
    $this->loyalty = $fetchedAnimalDetails->loyalty;
  }
  
  public function tryDrive(Character $char) {
    if ($char->getId() != $this->loyalTo) {
      throw new DisallowedActionException("error_travel_steed_no_owner");
    }

    return true;
  }

  public function getFullness()
  {
    return $this->fullness;
  }

  public function getLoyalTo()
  {
    return $this->loyalTo;
  }

  public function isLoyalTo(Character $char)
  {
    return $this->getLoyalTo() == $char->getId();
  }

  public function getLoyalty()
  {
    return $this->loyalty;
  }
}
