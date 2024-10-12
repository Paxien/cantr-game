<?php

$objectId = HTTPContext::getInteger('object');

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("", "error_too_far_away");
}

if (!$char->hasInInventory($object)) {
  CError::throwRedirectTag("", "error_too_far_away");
}

if ($object->getRepairRate() > 0) {
  $fullRepair = ceil($object->getDeterioration() / $object->getRepairRate());
} else {
  CError::throwRedirectTag("", "error_cant_be_repaired");
}

echo json_encode(
  array("fullRepair" => $fullRepair)
);
