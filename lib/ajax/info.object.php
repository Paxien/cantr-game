<?php

$object_id = HTTPContext::getInteger("object_id");

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("", "error_too_far_away");
}

$isInInventory = $char->hasInInventory($object);
$isInSameLoc = $char->isInSameLocationAs($object);
$isInNearbyStorage = $object->isAccessibleInStorage($char, false, false);


if (!($isInInventory || $isInSameLoc || $isInNearbyStorage)) {
  CError::throwRedirectTag("", "error_too_far_away");
}

if ($isInInventory) {
  $type = "inventory";
} elseif ($isInSameLoc || $isInNearbyStorage) {
  $type = "object";
}


$objectView = new ObjectView($object, $char);
$view = $objectView->show($type);

$tag = new Tag();
$tag->content = $view->text;
$objectName = $tag->interpret();

$objectData = array("id" => $object->getId(), "name" => $objectName, "amount" => $object->getAmount());

echo json_encode($objectData);
