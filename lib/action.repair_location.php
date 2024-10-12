<?php

$recursively = $_REQUEST["recursively"];

try {
  $locationToRepair = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.description", "error_not_while_travel");
}

$locationRepairManager = new LocationRepairManager($locationToRepair, new ResourceRequirementsConsumer());

try {
  $locationRepairManager->perform($recursively);

  $locationId = $locationToRepair->getId();
  if ($recursively) {
    Event::create(382, "LOCATION_ID=$locationId")->forCharacter($char)->show();
    Event::create(383, "ACTOR={$char->getId()} LOCATION_ID=$locationId")
      ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
  } else {
    Event::create(380, "LOCATION_ID=$locationId")->forCharacter($char)->show();
    Event::create(381, "ACTOR={$char->getId()} LOCATION_ID=$locationId")
      ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
  }

} catch (ResourcesMissingException $e) {
  CError::throwRedirectTag("repair_location", "error_raw_requirements_missing_from_ground");
} catch (AmbiguousBuildRequirementsException $e) {
  $message = "Somebody tried to repair location for which it's impossible to calculate what raws are needed. ";
  $message .= "Location={$locationToRepair->getId()}";
  Logger::getLogger(__FILE__)->error($message);
  CError::throwRedirectTag("char.description", "error_location_cannot_be_repaired");
}

redirect("repair_location");