<?php

class FueledVehicle extends LandVehicle {

  private $engineSpeedMultiplier;
  private $engineObjectId;
  private $engineRulesArray;

  private $fuelBaseMultiplier;
  private $fuelChangeMultiplier;

  private $fuelReduceArray = array();


  public function __construct(stdClass $fetchedObject, stdClass $fetchedType, Db $db) {
    parent::__construct($fetchedObject, $fetchedType, $db);

    $vehicleRules = Parser::rulesToArray($fetchedType->rules);

    $engines = explode(",", $vehicleRules['engine']);

    $engine = CObject::locatedIn($this->getId())->names($engines)->find();

    if ($engine) {
      $engineRulesArray = Parser::rulesToArray($engine->getRules());

      $this->engineSpeedMultiplier = $engineRulesArray['speed'];
      $this->engineObjectId = $engine->getId();
      $this->engineRulesArray = $engineRulesArray;

      list ($baseMultiplier, $changeMultiplier) = explode("-", $vehicleRules['fuelmult']);

      $this->fuelBaseMultiplier = $baseMultiplier;
      $this->fuelChangeMultiplier = $changeMultiplier;
    }
  }

  // override
  public function getSpeed($wantedSpeed, $maxSpeed) {
    $fuels = Parser::rulesToArray($this->engineRulesArray['fuel'], ",>");

    $this->fuelReduceArray = array(); // there will be stored what fuel should be deleted
    $left = $wantedSpeed; // todo cost calculation
    foreach ($fuels as $fuelName => $fuelUsage) {
      if ($left > 0) {
        $difference = $this->fuelChangeMultiplier - $this->fuelBaseMultiplier;
        $usePerPixel = $fuelUsage * ($this->fuelBaseMultiplier + $difference * pow($wantedSpeed / $maxSpeed, 6));
        if ($usePerPixel > 0) {
          $rawtypeId = ObjectHandler::getRawIdFromName($fuelName);
          $amount = ObjectHandler::getRawFromContainer($this->engineObjectId, $rawtypeId);
          $required = $usePerPixel * $left * 8;
          $toUse = min($required, $amount);
          $this->fuelReduceArray[$rawtypeId] = $toUse;
          $left -= $toUse / $usePerPixel;
        }
      }
    }

    if ($left > 0) {
      $minimumSpeed = (_WALKING_SPEED / 2) / TravelConstants::TURNS_PER_DAY;
      $speed = max($wantedSpeed - $left, $minimumSpeed);
    } else {
      $speed = $wantedSpeed;
    }

    return $speed;
  }

  // override
  public function tryDrive(Character $char) {
    parent::tryDrive($char);

    if ($this->engineSpeedMultiplier == null) {
      throw new DisallowedActionException("error_vechicle_need_engine");
    }
    return true;
  }

  // override
  public function getMaxSpeedMultiplier() {
    $multiplier = 1;
    $multiplier *= $this->engineSpeedMultiplier;
    return $multiplier;
  }

  // override
  public function reduceFuel($speed, $wantedSpeed) {
    $fueledVehicleStats = new Statistic("fuel_drive", Db::get());
    foreach ($this->fuelReduceArray as $rawtypeId => $amount) {
      $amount = rand_round($amount);
      ObjectHandler::rawToContainer($this->engineObjectId, $rawtypeId, (-1) * $amount);
      $fueledVehicleStats->update((string)$rawtypeId, $this->getId(), $amount);
    }

    // report lack of fuel only when the last piece of it was consumed, to prevent spam
    $fuelConsumed = array_sum($this->fuelReduceArray);
    if ($speed < $wantedSpeed && $fuelConsumed > 0) {
      Event::createEventInLocation (235, "", $this->getId(), Event::RANGE_SAME_LOCATION);
    }
	}
}
