<?php

$objectId = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if ($object->getType() != ObjectConstants::TYPE_SEAL) {
  CError::throwRedirectTag("char.inventory", "error_not_seal");
}

if (!$char->hasInInventory($object)) {
  CError::throwRedirectTag("char.inventory", "error_char_not_owner_seal");
}

$db = Db::get();
$stm = $db->prepare(
  "SELECT ons.utf8title AS title, o.id
   FROM objects o
     INNER JOIN obj_notes ons ON o.type IN (1, 37) AND o.typeid = ons.id
   WHERE o.person = :charId
   ORDER BY ons.title");
$stm->bindInt("charId", $char->getId());
$stm->execute();
$notes = $stm->fetchAll();

$smarty = new CantrSmarty;
$smarty->assign ("notes", $notes);
$smarty->assign ("object_id", $objectId);
$smarty->displayLang ("page.seal.tpl", $lang_abr);
