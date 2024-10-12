<?php

try {
  $locationToRepair = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

$locationRepairManager = new LocationRepairManager($locationToRepair, new ResourceRequirementsConsumer());

if (!$locationToRepair->isRepairable()) {
  CError::throwRedirectTag("char.buildings", "error_location_cannot_be_repaired");
}

try {
  $rawsForLocation = $locationRepairManager->getRawRequirementsForRepair(false);
  $rawsWithSublocations = $locationRepairManager->getRawRequirementsForRepair(true);
} catch (AmbiguousBuildRequirementsException $e) {
  $message = "Somebody tried to repair location for which it's impossible to calculate what raws are needed. ";
  $message .= "Location={$locationToRepair->getId()}";
  Logger::getLogger(__FILE__)->error($message);
  CError::throwRedirectTag("char.description", "error_location_cannot_be_repaired");
}

$availableOnGround = [];
foreach (array_keys($rawsWithSublocations) as $rawName) {
  $amountOnGround = ObjectHandler::getRawFromLocation($char->getLocation(), CObject::getRawIdFromName($rawName));
  $availableOnGround[$rawName] = $amountOnGround;
}

$leastAbundandRawFraction = 1;
foreach ($rawsForLocation as $rawName => $amountNeeded) {
  $amountOnGround = $availableOnGround[$rawName] ?: 0;
  if ($amountOnGround < $amountNeeded) {
    $leastAbundandRawFraction = min($amountOnGround / $amountNeeded, $leastAbundandRawFraction);
  }
}

$leastAbundandRawPercent = round($leastAbundandRawFraction * 100);

$smarty = new CantrSmarty();
$smarty->assign("raws", $rawsForLocation);
$smarty->assign("rawsWithSublocations", $rawsWithSublocations);
$smarty->assign("availableOnGround", $availableOnGround);
$smarty->assign("leastAbundandRawPercent", $leastAbundandRawPercent);
$smarty->assign("name", $locationToRepair->getName());

$smarty->displayLang("page.repair_location.tpl", $lang_abr);
