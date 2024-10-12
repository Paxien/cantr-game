<?php

// SANITIZE INPUT
$notes = $_REQUEST['notes'];
$storageId = HTTPContext::getInteger('storage');

try {
  /** @var $notes CObject[] */
  $notes = Pipe::from(explode(",", $notes))->map(function($noteId) {
    return CObject::loadById($noteId);
  })->toArray();
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

try {
  $storage = CObject::loadById($storageId);
  $storageWrapper = new Storage($storage);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_illegal_storage");
}

$backLink = "char.inventory";

foreach ($notes as $note) {
  if (!($note->hasProperty("Readable") || $note->hasProperty("NoteStorage")) || $note->isQuantity()) {
    CError::throwRedirect($backLink, "Only notes and note storages");
  }
}

$storageInInventory = $char->hasInInventory($storage);

// TODO!!! temporarily use location's lock as a lock for engine
if ($storage->getObjectCategory()->getId() == ObjectConstants::OBJCAT_ENGINES) {
  $lock = KeyLock::loadByLocationId($char->getLocation());
  if (!$lock->canAccess($char->getId())) {
    CError::throwRedirectTag($backLink, "error_not_right_key");
  }
}

foreach ($notes as $note) {
  try {
    $storageWrapper->store($note, $char, $amount);
  } catch (TooFarAwayException $e) {
    CError::throwRedirectTag($backLink, "error_too_far_away");
  } catch (WeightCapacityExceededException $e) {
    CError::throwRedirectTag($backLink, "error_you_has_too_much");
  } catch (StorageCapacityExceededException $e) {
    CError::throwRedirectTag($backLink, "error_storage_cant_hold");
  } catch (InvalidAmountException $e) {
    CError::throwRedirectTag($backLink, "error_store_more_than_available");
  } catch (InvalidObjectSettingException $e) {
    CError::throwRedirectTag($backLink, "error_incorrect_setting");
  } catch (NoKeyToInnerLockException $e) {
    CError::throwRedirectTag($backLink, "error_no_key");
  }

  Event::create(_EVENT_STORE_SELF,
    "OBJECTID=" . $note->getId() . " STORAGEID=" . $storage->getId())
    ->forCharacter($char)->show();

  // when we store something that we have in our inventory and container
  // is in our inventory too, then others shouldn't see the event
  if (!($storageInInventory && $objectInInventory)) {
    Event::create(_EVENT_STORE_OTHERS,
      "ACTOR=" . $char->getId() . " OBJECTID=" . $note->getId() . " STORAGEID=" . $storage->getId())
      ->nearCharacter($char)->except($char)->show();
  }
}

redirect($backLink);
