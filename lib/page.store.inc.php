<?php

// SANITIZE INPUT

$objectId = HTTPContext::getInteger('object_id');

try {
  $toBeStored = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasWithinReach($toBeStored)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

// in inventory
$invStorage = [];
$storagesInInventory = CObject::inInventoryOf($char)
  ->hasProperty("Storage")->hasNotProperty("NoteStorage")->findAll();
foreach ($storagesInInventory as $storage) {
  $invStorage[$storage->getId()] = (new Storage($storage))->getPrintableData($char);
}

// on ground
$storagesOnGround = [];
if ($char->getLocation() > 0) { // avoid search when travelling
  $storagesOnGround = CObject::locatedIn($char->getLocation())
    ->hasProperty("Storage")->hasNotProperty("NoteStorage")->findAll();
}
$groundStorage = [];
foreach ($storagesOnGround as $storage) {
  $groundStorage[$storage->getId()] = (new Storage($storage))->getPrintableData($char);
}

$tag = TagBuilder::forObject($objectId, true)->observedBy($char)->allowHtml(false)->build();
$objname = urlencode($tag->interpret());

$smarty = new CantrSmarty();
$smarty->assign("name", $objname);
$smarty->assign("invStorage", $invStorage);
$smarty->assign("groundStorage", $groundStorage);
$smarty->assign("object_id", $toBeStored->getId());
$smarty->assign("canCarry", $char->getMaxInventoryWeight() - $char->getMaxInventoryWeight());
$smarty->assign("rawsInInventory", $char->hasInInventory($toBeStored));
$smarty->assign("max", $toBeStored->getWeight());
$smarty->assign("isamount", $toBeStored->isQuantity());

$smarty->displayLang("page.store.tpl", $lang_abr);
