<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');
$retr_obj = HTTPContext::getInteger('retr_obj');
$amount = HTTPContext::getInteger('amount');
$destination = HTTPContext::getString("destination", null);
$retrieveEverything = HTTPContext::getString("retrieve_all", null);

$allowedDestinations = [
  Storage::RETRIEVE_TO_INVENTORY,
  Storage::RETRIEVE_TO_GROUND,
];

if (!in_array($destination, $allowedDestinations)) {
  $destination = Storage::RETRIEVE_TO_GROUND;
}

try {
  $storage = CObject::loadById($object_id);
  $storageWrapper = new Storage($storage);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_retrieve_from_illegal_storage");
}

if ($retrieveEverything) {
  $objectsToRetrieve = CObject::storedIn($storage)->setting(ObjectConstants::SETTING_PORTABLE)->findAll();
} else {
  $objectsToRetrieve = [CObject::loadById($retr_obj)]; // single element list for a single object
}



$rootStorage = $storage->getRoot();
$isStorageInInventory = $char->hasInInventory($rootStorage);

$backLink = $isStorageInInventory ? "char.inventory" : "char.objects";

// special case - you need vehicle key to access engine of a locked vehicle - should be temporary
if ($storage->getObjectCategory()->getId() == ObjectConstants::OBJCAT_ENGINES) {
  $lock = KeyLock::loadByLocationId($char->getLocation());
  if (!$lock->canAccess($char->getId())) {
    CError::throwRedirectTag($backLink, "error_no_key");
  }
}

// if storage is in inventory
//then contents can't be dropped onto the ground
if ($char->hasInInventory($rootStorage)) {
  $destination = Storage::RETRIEVE_TO_INVENTORY;
}

foreach ($objectsToRetrieve as $retrieved) {
  try {
    $storageWrapper->retrieve($retrieved, $char, $destination, $amount);
  } catch (TooFarAwayException $e) {
    CError::throwRedirectTag($backLink, "error_too_far_away");
  } catch (InvalidObjectSettingException $e) {
    CError::throwRedirectTag($backLink, "error_incorrect_setting");
  } catch (InvalidAmountException $e) {
    CError::throwRedirectTag($backLink, "error_retrieve_illegal_amount");
  } catch (NoKeyToInnerLockException $e) { // storage's inner lock
    $lock = KeyLock::loadByObjectId($storage->getId());
    $lock->redirectToLockpicking(); // todo! When called by ajax it doesn't show any error
  } catch (WeightCapacityExceededException $e) {
    CError::throwRedirectTag($backLink, "error_you_has_too_much");
  }
}

$isInventoryEvent = $isStorageInInventory || $destination == Storage::RETRIEVE_TO_INVENTORY;
$eventId = $isInventoryEvent ? 263 : 28;

if (!$isStorageInInventory) {

  if (!$storage->hasProperty("NoteStorage")) {
    foreach ($objectsToRetrieve as $retrieved) {
      Event::create($eventId, "OBJECTID=" . $retrieved->getId() . " STORAGEID=" . $rootStorage->getId())
        ->forCharacter($char)->show();
    }
  }

  // storage on the ground - show public event for other people
  $eventId = ($destination == Storage::RETRIEVE_TO_INVENTORY) ? 262 : 27;
  foreach ($objectsToRetrieve as $retrieved) {
    Event::create($eventId, "ACTOR=" . $char->getId()
      . " OBJECTID=" . $retrieved->getId() . " STORAGEID=" . $rootStorage->getId())
        ->nearCharacter($char)->except($char)->show();
  }
}

if (CObject::storedIn($storage)->exists()) { // there's something left inside
  redirect("retrieve", ["object_id" => $storage->getId()]);
} else {
  redirect($backLink);

}
