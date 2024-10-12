<?php

// SANITIZE INPUT
$objectId = HTTPContext::getInteger('object_id');
$target = HTTPContext::getInteger('target');
$amount = HTTPContext::getInteger('amount');

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

try {
  $storage = CObject::loadById($target);
  $storageWrapper = new Storage($storage);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_illegal_storage");
}

$objectInInventory = $char->hasInInventory($object);

$backLink = $objectInInventory ? "char.inventory" : "char.objects";

if ($object->isQuantity()) {
  if (!Validation::isPositiveInt($amount)) {
    CError::throwRedirectTag($backLink, "error_store_illegal_amount");
  }
}

$isRaw = $object->getType() == ObjectConstants::TYPE_RAW;

if (($object->isQuantity() && !$isRaw)) {
  CError::throwRedirectTag($backLink, "error_cant_store_coins");
}

$storageInInventory = $char->hasInInventory($storage);

// TODO!!! temporarily use location's lock as a lock for engine
if ($storage->getObjectCategory()->getId() == ObjectConstants::OBJCAT_ENGINES) {
  $lock = KeyLock::loadByLocationId($char->getLocation());
  if (!$lock->canAccess($char->getId())) {
    CError::throwRedirectTag($backLink, "error_not_right_key");
  }
}

try {
  $storageWrapper->store($object, $char, $amount);
} catch (TooFarAwayException $e) {
  CError::throwRedirectTag($backLink, "error_too_far_away");
} catch (WeightCapacityExceededException $e) {
  CError::throwRedirectTag($backLink, "error_you_has_too_much");
} catch (StorageCapacityExceededException $e) {
  CError::throwRedirectTag($backLink, "error_storage_cant_hold");
} catch (InvalidAmountException $e) {
  CError::throwRedirectTag($backLink, "error_store_more_than_available");
} catch (InvalidStorageType $e) {
  CError::throwRedirectTag($backLink, "error_invalid_storage_type");
} catch (InvalidObjectSettingException $e) {
  CError::throwRedirectTag($backLink, "error_incorrect_setting");
} catch (NoKeyToInnerLockException $e) {
  CError::throwRedirectTag($backLink, "error_no_key");
}

Event::create(_EVENT_STORE_SELF,
  "OBJECTID=". $object->getId() ." STORAGEID=". $storage->getId())
    ->forCharacter($char)->show();

// when we store something that we have in our inventory and container
// is in our inventory too, then others shouldn't see the event
if (!($storageInInventory && $objectInInventory)) {
  Event::create(_EVENT_STORE_OTHERS,
    "ACTOR=". $char->getId() ." OBJECTID=". $object->getId() ." STORAGEID=". $storage->getId())
      ->nearCharacter($char)->except($char)->show();
}

redirect($backLink);
