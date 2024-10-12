<?php

// SANITIZE INPUT
$bodyCharId = HTTPContext::getInteger('id');
$clothid = HTTPContext::getInteger('clothid');


try {
  $bodyObject = CObject::locatedIn($char->getLocation())
    ->typeid(ObjectConstants::TYPE_DEAD_BODY)->typeid($bodyCharId)->find();
  if ($bodyObject === null) {
    throw new InvalidArgumentException("Body doesn't exist");
  }
  $cloth = CObject::loadById($clothid);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_loot_away");
}

if (!$char->isInSameLocationAs($bodyObject) || $bodyObject->getType() != ObjectConstants::TYPE_DEAD_BODY) {
  CError::throwRedirectTag("char.objects", "error_loot_away");
}

if ($cloth->getPerson() != $bodyCharId) {
  CError::throwRedirectTag("char.objects", "error_loot_away");
}

$cloth_weight = $cloth->getTypeWeight();

if ($char->getInventoryWeight() + $cloth_weight > $char->getMaxInventoryWeight()) {
  CError::throwRedirectTag("char.objects", "error_you_has_too_much");
}

$cloth->setPerson($char->getId());
$cloth->setSpecifics(str_replace('wearing:1', 'wearing:0', $cloth->getSpecifics()));
$cloth->setWeight($cloth->getTypeWeight());
$cloth->saveInDb();

$clothes = urlencode("<CANTR REPLACE NAME=item_" . $cloth->getUniqueName() . "_o>");

Event::create(87, "ITEM=$clothes VICTIM=$bodyCharId")->forCharacter($char)->show();
Event::create(88, "ACTOR=$character ITEM=$clothes VICTIM=$bodyCharId")->nearCharacter($char)->andAdjacentLocations()->except($char)->show();

redirect("search", ["id" => $bodyCharId]);
