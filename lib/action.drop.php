<?php

$objectId = HTTPContext::getInteger('object_id');
$amount = HTTPContext::getInteger('amount');

try {
  $object = CObject::loadById($objectId);
  $location = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_not_in_inventory");
}

if (($object->getSetting() == ObjectConstants::SETTING_QUANTITY) && !Validation::isPositiveInt($amount)) {
  CError::throwRedirectTag("drop&object_id=$objectId", "error_too_little_drop");
}

// TEMPORARY, BECAUSE THERE IS STILL NO SETTING "HEAVY OBJECT"
if ($object->getType() == ObjectConstants::TYPE_DEAD_BODY) {
  CError::throwRedirectTag("char.objects", "error_incorrect_setting");
}

$translocation = new ObjectTranslocation($object, $char, $location, $amount);

$translocation->setCheckNearness(true)->setCheckObjectSetting(true);
// impossible to exeed capacity, there is no receiver anyway
$translocation->setCheckCapacity(false)->setCheckReceiver(false);

try {
  $translocation->perform();
} catch (BadInitialLocationException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
} catch (TooFarAwayException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
} catch (InvalidObjectSettingException $e) {
  CError::throwRedirectTag("char.inventory", "error_incorrect_setting");
} catch (InvalidAmountException $e) {
  CError::throwRedirectTag("drop&object_id=$objectId", "error_too_much_drop");
}

// notify characters about the event
if ($object->getSetting() == ObjectConstants::SETTING_QUANTITY) {
  if ($object->getType() == ObjectConstants::TYPE_RAW) { // raw
    $objectView = new ObjectView($object, $char);
    $description = $objectView->show("transfer", $amount);
    $actorId = 73;
    $actorVars = "MATERIAL=". urlencode($description->transfer_long);
    $watcherId = 74;
    $watcherVars = "MATERIAL=". urlencode($description->transfer) ." ACTOR=". $char->getId();
  } else { // coin
    $actorId = 237;
    $actorVars = "OBJECT=". $object->getId() ." TYPE=2 NUMBER=$amount";
    $watcherId = 238;
    $watcherVars = "OBJECT=". $object->getId() ." TYPE=2 ACTOR=". $char->getId();
  }
} elseif ($object->getType() == ObjectConstants::TYPE_NOTE) {
  $actorId = 229;
  $db = Db::get();
  $stm = $db->prepare("SELECT utf8title FROM obj_notes WHERE id = :id");
  $stm->bindInt("id", $object->getTypeid());
  $noteTitle = $stm->executeScalar();
  $actorVars = "TITLE=". urlencode($noteTitle);
  $watcherId = 230;
  $watcherVars = "ACTOR=". $char->getId();
} else {
  $actorId = 141;
  $actorVars = "OBJECT=". $object->getId() ." TYPE=2";
  $watcherId = 142;
  $watcherVars = "OBJECT=". $object->getId() ." TYPE=1 ACTOR=". $char->getId();
}

Event::createPersonalEvent($actorId, $actorVars, $char->getId());
Event::createPublicEvent($watcherId, $watcherVars, $char->getId(), Event::RANGE_SAME_LOCATION, array($char->getId()));



redirect("char.inventory");
