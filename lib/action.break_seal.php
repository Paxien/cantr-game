<?php

$objectId = HTTPContext::getInteger('object_id');

try {
  $sealableObject = CObject::loadById($objectId);
  $objectSeals = new SealsManager($sealableObject);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasInInventory($sealableObject) && !$sealableObject->isAccessibleInStorage($char, true, true)) {
  CError::throwRedirectTag("char.inventory", "error_object_not_in_inventory");
}

$sealableProperty = $sealableObject->getProperty("Sealable");
if (!$sealableProperty["canBeBroken"]) {
  CError::throwRedirectTag("char.inventory", "error_cannot_break_seal");
}

$seals = $objectSeals->getAll(true);

if (count($seals) == 0) {
  CError::throwRedirect("char.events", "cannot be broken because not sealed");
}

$objectSeals->breakAll();

redirect("retrieve", ["object_id" => $sealableObject->getId()]);
