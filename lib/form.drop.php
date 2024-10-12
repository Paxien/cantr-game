<?php

// SANITIZE INPUT
$objectId = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_not_in_inventory");
}

if ($object->getPerson() != $char->getId()) {
  CError::throwRedirectTag("char.inventory", "error_not_in_inventory");
}

$isQuantityObject = ($object->getSetting() == ObjectConstants::SETTING_QUANTITY);

if (!$isQuantityObject) {
  CError::throwRedirectTag("char.objects", "error_incorrect_setting");
}

if ($object->getType() == ObjectConstants::TYPE_RAW) {
  $availableAmount = $object->getWeight();
} elseif (in_array($object->getType(), ObjectConstants::$TYPES_COINS)) {
  $availableAmount = floor($object->getWeight() / ObjectConstants::WEIGHT_COIN);
} else {
  CError::throwRedirectTag("char.objects", "error_incorrect_setting");
}

$smarty = new CantrSmarty();
$smarty->assign("WEIGHT", $availableAmount);
$smarty->assign("charload", $char->getInventoryWeight());

$smarty->assign("object_id", $object->getId());
$smarty->assign("OBJECT", "<CANTR OBJNAME ID=$objectId>");
$smarty->assign ("ACTION", "<CANTR REPLACE NAME=action_drop>");

$smarty->displayLang ("form.drop.tpl", $lang_abr); 

