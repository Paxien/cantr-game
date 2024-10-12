<?php
include_once("func.expireobject.inc.php");

// SANITIZE INPUT
$sealId = HTTPContext::getInteger('object_id');
$targetId = HTTPContext::getInteger('note_id');

// READ NECESSARY INFO ABOUT SEAL

try {
  $seal = CObject::loadById($sealId);
  $target = CObject::loadById($targetId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if ($seal->getType() != ObjectConstants::TYPE_SEAL) {
  CError::throwRedirectTag("char.inventory", "error_not_seal");
}

if (!$char->hasInInventory($seal)) {
  CError::throwRedirectTag("char.inventory", "error_char_not_owner_seal");
}

if (!$targetId) {
  redirect("seal", ["object_id" => $sealId]);
  exit();
}

$db = Db::get();
$stm = $db->prepare("SELECT o.person, o.type, o.typeid, obn.setting FROM objects o
  INNER JOIN obj_notes obn ON obn.id = o.typeid WHERE o.id = :objectId");
$stm->bindInt("objectId", $targetId);
$stm->execute();
$note_info = $stm->fetchObject();

if (!$char->hasInInventory($target)) {
  CError::throwRedirectTag("char.inventory", "error_char_not_owner_note_to_seal");
}

if (!in_array($target->getType(), array(ObjectConstants::TYPE_NOTE, ObjectConstants::TYPE_ENVELOPE))) {
  CError::throwRedirectTag("char.inventory", "error_char_not_owner_note_to_seal");
}

// is enough wax available?

$waxAmount = ObjectHandler::getRawFromPerson($character, ObjectHandler::getRawIdFromName("sealing wax"));
if ($waxAmount < Note::AMOUNT_WAX_PER_SEAL) {
  CError::throwRedirectTag("char.inventory", "error_not_enough_wax_for_seal");
}

ObjectHandler::rawToPerson($character, ObjectHandler::getRawIdFromName("sealing wax"), -1 * Note::AMOUNT_WAX_PER_SEAL);

// we need to remove all broken seals

$sealsManager = new SealsManager($target);
$brokenSealsNames = $sealsManager->getAll(false, true);

foreach ($brokenSealsNames as $brokenSealName) {
  $sealsManager->remove($brokenSealName);
}

if ($target->getType() == ObjectConstants::TYPE_NOTE) { // notes become uneditable
  $stm = $db->prepare("UPDATE obj_notes SET setting = :setting WHERE id = :id");
  $stm->bindInt("setting", Note::NOTE_SETTING_UNEDITABLE);
  $stm->bindInt("id", $target->getTypeid());
  $stm->execute();
}

$sealsManager->addSeal($seal->getSpecifics());

redirect("char.inventory");
