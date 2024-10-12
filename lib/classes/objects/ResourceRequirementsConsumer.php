<?php

class ResourceRequirementsConsumer
{
  /**
   * @param Location $location location from which raws should be taken
   * @param array $raws array where key is raw name and value is required amount
   * @return bool true if there is enough of every raw, false otherwise
   */
  public function hasRawsOnTheGround(Location $location, array $raws)
  {
    $rawTypeByName = $this->getRawTypesByNames(array_keys($raws));
    foreach ($raws as $rawName => $amountNeeded) {
      $pileWeight = ObjectHandler::getRawFromLocation($location->getId(), $rawTypeByName[$rawName]);
      if ($pileWeight < $amountNeeded) {
        return false;
      }
    }
    return true;
  }

  /**
   * Removes specified amounts of resources
   * @param Location $location
   * @param array $raws
   */
  public function consumeRaws(Location $location, array $raws)
  {
    $rawTypeByName = $this->getRawTypesByNames(array_keys($raws));
    if (!Validation::isNonNegativeIntArray($raws)) {
      throw new InvalidArgumentException("Amounts in " . print_r($raws, true) . " should be positive, reflecting amount to be removed");
    }
    $alreadyRemoved = [];
    foreach ($raws as $rawName => $amount) {
      $removalSuccessful = ObjectHandler::rawToLocation($location->getId(), $rawTypeByName[$rawName], -1 * $amount);
      if (!$removalSuccessful) {
        throw new CannotConsumeMultipleRawsException(
          "Failure when trying to remove multiple raws from {$location->getId()}", $alreadyRemoved);
      }
      $alreadyRemoved[$rawName] = $amount;
    }
  }

  private function getRawTypesByNames(array $rawNames)
  {
    $rawTypes = [];
    foreach ($rawNames as $rawName) {
      $rawTypes[$rawName] = CObject::getRawIdFromName($rawName);
    }
    return $rawTypes;
  }

  public function getFractionOfLeastAbundantResourceOnTheGround(Location $location, array $raws)
  {
    $rawTypeByName = $this->getRawTypesByNames(array_keys($raws));
    $minPercentAvailable = 100;
    foreach ($raws as $rawName => $amountNeeded) {
      $pileWeight = ObjectHandler::getRawFromLocation($location->getId(), $rawTypeByName[$rawName]);
      $percentAvailable = 100;
      if ($amountNeeded > 0) {
        $percentAvailable = min(100, floor(100 * $pileWeight / $amountNeeded));
      }
      $minPercentAvailable = min($minPercentAvailable, $percentAvailable);
    }
    return $minPercentAvailable / 100;
  }
}