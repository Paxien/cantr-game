<?php

class LocationRepairManager
{
  /**
   * @var Location
   */
  private $subject;
  /**
   * @var ResourceRequirementsConsumer
   */
  private $resourceRequirementsConsumer;

  public function __construct(Location $subject, ResourceRequirementsConsumer $resourceRequirementsConsumer)
  {
    $this->subject = $subject;
    $this->resourceRequirementsConsumer = $resourceRequirementsConsumer;
  }

  /**
   * @param bool $withSublocations whether repairs of all sublocations should be taken into account
   * @return array $rawName => $amountNeeded
   * @throws AmbiguousBuildRequirementsException when it's not possible to calculate which raws are needed for repair
   */
  public function getRawRequirementsForRepair($withSublocations)
  {
    $locations = [$this->subject];
    if ($withSublocations) {
      $locations = array_merge($locations, Location::bulkLoadByIds($this->subject->getSublocationsRecursive()));
    }

    $rawsNeeded = [];
    foreach ($locations as $location) {
      foreach ($this->getBuildRequirementsNormalizedToRaws($location) as $rawName => $amount) {
        $amountForLocationRepair = ceil($amount * $this->getDeterioratedFraction($location));
        $rawsNeeded[$rawName] = ($rawsNeeded[$rawName] ?: 0) + $amountForLocationRepair;
      }
    }

    return $rawsNeeded;
  }

  public function perform($recurvisely)
  {
    if ($recurvisely) {
      $this->performFullRepairRecursively();
    } else {
      $this->performPartialRepair();
    }
  }

  /**
   * @throws ResourcesMissingException when not all required resources are on the ground
   */
  public function performFullRepairRecursively()
  {
    $rawsForRepair = $this->getRawRequirementsForRepair(true);
    $locations = Location::bulkLoadByIds($this->subject->getSublocationsRecursive());
    $locations[] = $this->subject;

    $resourceRequirementsConsumer = new ResourceRequirementsConsumer();
    if (!$resourceRequirementsConsumer->hasRawsOnTheGround($this->subject, $rawsForRepair)) {
      throw new ResourcesMissingException("Some of required raws are missing");
    }
    $resourceRequirementsConsumer->consumeRaws($this->subject, $rawsForRepair);
    foreach ($locations as $location) {
      $location->setDeterioration(0);
      $location->saveInDb();
    }
  }

  public function performPartialRepair()
  {
    $rawsForFullRepair = $this->getRawRequirementsForRepair(false);
    $resourceRequirementsConsumer = new ResourceRequirementsConsumer();
    $fractionToRepair = $resourceRequirementsConsumer->getFractionOfLeastAbundantResourceOnTheGround($this->subject, $rawsForFullRepair);

    $rawsForRepair = [];
    foreach ($rawsForFullRepair as $rawName => $amount) {
      $rawsForRepair[$rawName] = round($fractionToRepair * $amount);
    }
    $resourceRequirementsConsumer->consumeRaws($this->subject, $rawsForRepair);

    $deterioration = $this->subject->getDeterioration();
    $this->subject->setDeterioration((1 - $fractionToRepair) * $deterioration);
    $this->subject->saveInDb();
  }

  private function getDeterioratedFraction(Location $location)
  {
    return $location->getDeterioration() / 10000;
  }

  /**
   * @param Location $location
   * @return array of raws required to build the object
   * @throws AmbiguousBuildRequirementsException
   */
  private function getBuildRequirementsNormalizedToRaws(Location $location)
  {
    return $location->getObjectType()->getBuildRequirementsNormalizedToRaws();
  }
}
