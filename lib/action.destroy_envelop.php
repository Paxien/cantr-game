<?php

$object_id = HTTPContext::getInteger('object_id');
$id = HTTPContext::getInteger('id');

$envelope = CObject::inInventoryOf($char)->id($object_id)
  ->type(ObjectConstants::TYPE_ENVELOPE)->typeid($id)->find();
if ($envelope == null) {
  CError::throwRedirectTag("char.inventory", "error_no_envelop");
}

$isNotEmpty = CObject::storedIn($envelope)->exists();
if ($isNotEmpty) {
  CError::throwRedirectTag("char.inventory", "error_envelope_not_empty");
}

$envelope->remove();
$envelope->saveInDb();

$db = Db::get();
$stm = $db->prepare("DELETE FROM obj_notes WHERE id = :id LIMIT 1");
$stm->bindInt("id", $id);
$stm->execute();

$stm = $db->prepare("DELETE FROM seals WHERE note = :objectId");
$stm->bindInt("objectId", $object_id);
$stm->execute();

redirect("char.inventory");
