<?php

// SANITIZE INPUT
$objectId = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_object_not_same_location");
}

if ($object->getLocation() != $char->getLocation()) {
  CError::throwRedirectTag("char.objects", "error_object_not_same_location");
}

$isQuantityObject = ($object->getSetting() == ObjectConstants::SETTING_QUANTITY);

if (!$isQuantityObject) {
  CError::throwRedirectTag("char.inventory", "error_incorrect_setting");
}

$inventorySpaceLeft = $char->getMaxInventoryWeight() - $char->getInventoryWeight();
if ($object->getType() == ObjectConstants::TYPE_RAW) {
  $availableAmount = $object->getWeight();
  $maxPossible = min($inventorySpaceLeft, $availableAmount);
} elseif (in_array($object->getType(), ObjectConstants::$TYPES_COINS)) {
  $availableAmount = floor($object->getWeight() / ObjectConstants::WEIGHT_COIN);
  $maxPossible = min(floor($inventorySpaceLeft / ObjectConstants::WEIGHT_COIN), $availableAmount);
} else {
  CError::throwRedirectTag("char.inventory", "error_incorrect_setting");
}

$smarty = new CantrSmarty();
$smarty->assign("WEIGHT", $availableAmount);
$smarty->assign("max_amount", $maxPossible);
$smarty->assign("charload", $char->getInventoryWeight());

$smarty->assign("object_id", $object->getId());
$smarty->assign("OBJECT", "<CANTR OBJNAME ID=$objectId>");
$smarty->assign ("ACTION", "<CANTR REPLACE NAME=action_take>");

$smarty->displayLang ("form.take.tpl", $lang_abr); 

