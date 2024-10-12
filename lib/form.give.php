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

if ($object->getType() == ObjectConstants::TYPE_RAW) {
  $availableAmount = $object->getWeight();
} elseif (in_array($object->getType(), ObjectConstants::$TYPES_COINS)) {
  $availableAmount = floor($object->getWeight() / ObjectConstants::WEIGHT_COIN);
}

$charLoc = new char_location($char->getId());
$charsRec = $charLoc->chars_near(_PEOPLE_NEAR);

$tag = new tag();
$tag->html = false;
$receivers = array();
foreach ($charsRec as $receiver) {
  if ($receiver != $character) {
    $tag->content = "<CANTR CHARNAME ID=$receiver>";
    $charName = $tag->interpret();
    $receivers[] = array("id" => $receiver, "name" => $charName);
  }
}


$smarty = new CantrSmarty();
$smarty->assign("WEIGHT", $availableAmount);
$smarty->assign("is_quantity", $isQuantityObject);
$smarty->assign("receivers", $receivers);

$smarty->assign("object_id", $object->getId());
$smarty->assign("OBJECT", "<CANTR OBJNAME ID=$objectId>");
$smarty->assign("ACTION", "<CANTR REPLACE NAME=action_give>");

$smarty->displayLang ("form.give.tpl", $lang_abr); 

