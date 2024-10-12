<?php

$vehicleId = HTTPContext::getInteger('vehicle');

try {
  $vehicle = Location::loadById($vehicleId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if (($char->getLocation() == 0) || ($vehicle->getRegion() != $char->getLocation())) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if (!$vehicle->isDisassemblable()) {
  CError::throwRedirectTag("char.buildings", "error_not_disassemblable");
}

$smarty = new CantrSmarty();
$smarty->assign("vehicleId", $vehicleId);

$smarty->displayLang("page.disassemble_vehicle.tpl", $lang_abr);
