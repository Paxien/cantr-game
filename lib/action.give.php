<?php

$objectId = HTTPContext::getInteger('object_id');
$receiverId = HTTPContext::getInteger('receiver');
$amount = HTTPContext::getInteger('amount');

try {
  $object = CObject::loadById($objectId);
  $receiver = Character::loadById($receiverId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_not_in_inventory");
}

if (($object->getSetting() == ObjectConstants::SETTING_QUANTITY) && !Validation::isPositiveInt($amount)) {
  CError::throwRedirectTag("char.inventory", "error_too_little_give");
}

// TEMPORARY, BECAUSE THERE IS STILL NO SETTING "HEAVY OBJECT"
if ($object->getType() == ObjectConstants::TYPE_DEAD_BODY) {
  CError::throwRedirectTag("char.objects", "error_incorrect_setting");
}

$translocation = new ObjectTranslocation($object, $char, $receiver, $amount);


$translocation->setCheckNearness(true)->setCheckObjectSetting(true);
$translocation->setCheckCapacity(true)->setCheckReceiver(true);

try {
  $translocation->perform();
} catch (BadInitialLocationException $e) {
  CError::throwRedirectTag("char.inventory", "error_not_in_inventory");
} catch (TooFarAwayException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
} catch (InvalidObjectSettingException $e) {
  CError::throwRedirectTag("char.inventory", "error_incorrect_setting");
} catch (WeightCapacityExceededException $e) {
  CError::throwRedirectTag("char.inventory", "error_receiver_has_too_much");
} catch (InvalidAmountException $e) {
  CError::throwRedirectTag("give&object_id=$objectId", "error_too_much_give");
} catch (CharacterStatusException $e) {
  CError::throwRedirectTag("char.inventory", "error_receiver_in_near_death_state");
}

// notify characters about the event
if ($object->getSetting() == ObjectConstants::SETTING_QUANTITY) {
  if ($object->getType() == ObjectConstants::TYPE_RAW) { // raw

    $objectView = new ObjectView($object, $char);
    $description = $objectView->show("transfer", $amount);
    $actorId = 78;
    $actorVars = "MATERIAL=". urlencode($description->transfer_long) ." VICTIM=". $receiver->getId();
    $watcherId = 79;
    $watcherVars = "MATERIAL=". urlencode($description->transfer)
      ." ACTOR=". $char->getId() ." VICTIM=". $receiver->getId();
    $victimId = 80;
    $victimVars = "MATERIAL=". urlencode($description->transfer_long)
      ." ACTOR=". $char->getId() ." VICTIM=". $receiver->getId();
  } else { // coin
    $actorId = 239;
    $actorVars = "OBJECT=". $object->getId() ." TYPE=2 NUMBER=$amount VICTIM=". $receiver->getId();
    $watcherId = 241;
    $watcherVars = "OBJECT=". $object->getId() ." TYPE=2 ACTOR=". $char->getId() ." VICTIM=". $receiver->getId();
    $victimId = 240;
    $victimVars = "OBJECT=". $object->getId() ." TYPE=2 ACTOR=". $char->getId() ." NUMBER=$amount";
  }
} elseif ($object->getType() == ObjectConstants::TYPE_NOTE) {
  $actorId = 226;
  $db = Db::get();
  $stm = $db->prepare("SELECT utf8title FROM obj_notes WHERE id = :id");
  $stm->bindInt("id", $object->getTypeid());
  $noteTitle = $stm->executeScalar();
  $actorVars = "TITLE=". urlencode($noteTitle) ." VICTIM=". $receiver->getId();
  $watcherId = 227;
  $watcherVars = "ACTOR=". $char->getId() ." VICTIM=". $receiver->getId();
  $victimId = 228;
  $victimVars = "TITLE=". urlencode($noteTitle) ." ACTOR=". $char->getId();
} else {
  $actorId = 143;
  $actorVars = "OBJECT=". $object->getId() ." TYPE=2 VICTIM=". $receiver->getId();
  $watcherId = 144;
  $watcherVars = "OBJECT=". $object->getId() ." TYPE=1 ACTOR=". $char->getId() ." VICTIM=". $receiver->getId();
  $victimId = 145;
  $victimVars = "OBJECT=". $object->getId() ." TYPE=2 ACTOR=". $char->getId();
}

Event::createPersonalEvent($actorId, $actorVars, $char->getId());
Event::createPersonalEvent($victimId, $victimVars, $receiver->getId());
Event::createPublicEvent($watcherId, $watcherVars, $char->getId(),
  Event::RANGE_SAME_LOCATION, array($char->getId(), $receiver->getId()));


PlayersMonitoring::reportObjectTranslocation($object, $char, $receiver, $amount, Db::get());

redirect("char.inventory");
