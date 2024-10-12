<?php

// SANITIZE INPUT
$noteStorageId = HTTPContext::getInteger('envelop');
$note = $_REQUEST['note'];

$notes = explode(", ", $note);

try {
  $noteStorage = CObject::loadById($noteStorageId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "too_far_away");
}

if (!$noteStorage->hasProperty("NoteStorage")) {
  CError::throwRedirectTag("char.inventory", "error_no_envelop");
}

$sealsManager = new SealsManager($noteStorage);
if (count($sealsManager->getAll(true)) > 0) {
  CError::throwRedirectTag("char.inventory", "error_sealed_envelope");
}

// checking notes being transferred

$noteAndEnvelopeIdsInInventory = CObject::inInventoryOf($char)->ids($notes)
  ->types([ObjectConstants::TYPE_NOTE, ObjectConstants::TYPE_ENVELOPE])->findIds();

if (count($noteAndEnvelopeIdsInInventory) < count($notes)) {
  CError::throwRedirectTag("char.events", "error_no_note");
}

// Final update
$db = Db::get();
$stm = $db->prepareWithIntList("UPDATE objects SET location = 0, person = 0, attached = :storage, ordering = 0
  WHERE id IN (:ids)", [
    "ids" => $noteAndEnvelopeIdsInInventory,
]);
$stm->bindInt("storage", $noteStorageId);
$stm->execute();

redirect("char.inventory");
