<?php

$objectId = HTTPContext::getInteger('object_id');
$amount = HTTPContext::getInteger('amount');

try {
  $object = CObject::loadById($objectId);
  $location = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_object_not_same_location");
}

if (($object->getSetting() == ObjectConstants::SETTING_QUANTITY) && !Validation::isPositiveInt($amount)) {
  CError::throwRedirectTag("take&object_id=$objectId", "error_too_little_take");
}

// TEMPORARY, BECAUSE THERE IS STILL NO SETTING "HEAVY OBJECT"
if ($object->getType() == ObjectConstants::TYPE_DEAD_BODY) {
  CError::throwRedirectTag("char.objects", "error_incorrect_setting");
}

$translocation = new ObjectTranslocation($object, $location, $char, $amount);


$translocation->setCheckNearness(true)->setCheckObjectSetting(true);
$translocation->setCheckCapacity(true)->setCheckReceiver(true);

try {
  $translocation->perform();
} catch (BadInitialLocationException $e) {
  CError::throwRedirectTag("char.objects", "error_object_not_same_location");
} catch (TooFarAwayException $e) {
  CError::throwRedirectTag("char.objects", "error_object_not_same_location");
} catch (InvalidObjectSettingException $e) {
  CError::throwRedirectTag("char.objects", "error_incorrect_setting");
} catch (WeightCapacityExceededException $e) {
  CError::throwRedirectTag("char.objects", "error_you_has_too_much");
} catch (InvalidAmountException $e) {
  CError::throwRedirectTag("take&object_id=$objectId", "error_too_much_take");
}

// notify characters about the event
if ($object->getSetting() == ObjectConstants::SETTING_QUANTITY) {
  if ($object->getType() == ObjectConstants::TYPE_RAW) { // raw
    $objectView = new ObjectView($object, $char);
    $description = $objectView->show("transfer", $amount);
    $actorId = 83;
    $actorVars = "MATERIAL=". urlencode($description->transfer_long);
    $watcherId = 84;
    $watcherVars = "MATERIAL=". urlencode($description->transfer) ." ACTOR=". $char->getId();
  } else { // coin
    $actorId = 242;
    $actorVars = "OBJECT=". $object->getId() ." TYPE=2 NUMBER=$amount";
    $watcherId = 243;
    $watcherVars = "OBJECT=". $object->getId() ." TYPE=2 ACTOR=". $char->getId();
  }
} elseif ($object->getType() == ObjectConstants::TYPE_NOTE) {
  $actorId = 231;
  $db = Db::get();
  $stm = $db->prepare("SELECT utf8title FROM obj_notes WHERE id = :id");
  $stm->bindInt("id", $object->getTypeid());
  $noteTitle = $stm->executeScalar();
  $actorVars = "TITLE=". urlencode($noteTitle);
  $watcherId = 232;
  $watcherVars = "ACTOR=". $char->getId();
} else {
  $actorId = 146;
  $actorVars = "OBJECT=". $object->getId() ." TYPE=2";
  $watcherId = 147;
  $watcherVars = "OBJECT=". $object->getId() ." TYPE=1 ACTOR=". $char->getId();
}

Event::createPersonalEvent($actorId, $actorVars, $char->getId());
Event::createPublicEvent($watcherId, $watcherVars, $char->getId(), Event::RANGE_SAME_LOCATION, array($char->getId()));



redirect("char.objects");
