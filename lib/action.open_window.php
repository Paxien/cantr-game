<?php

$object_id = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if (!$char->hasWithinReach($object)) {
  CError::throwRedirectTag("char.events", "error_not_near_window");
}

$seeingOutsideProp = $object->getProperty("EnableSeeingOutside");
if ($seeingOutsideProp === null || !$seeingOutsideProp['canBeOpenAndClosed']) {
  CError::throwRedirect("char.events", "<CANTR REPLACE NAME=error_not_authorized>, page=$page, trying to open/close not-window.");
}

$keyLock = KeyLock::loadByObjectId($object_id);
if (!$keyLock->canAccess($char->getId())) {
  $keyLock->redirectToLockpicking(); // if window is locked and you have no key then try to destroy it
}


function changeWindowState($oldText, $newText, $object_id, Db $db) {
  $stm = $db->prepare("UPDATE objects SET specifics = REPLACE(specifics, :oldText, :newText) WHERE id = :objectId");
  $stm->bindStr("oldText", $oldText);
  $stm->bindStr("newText", $newText);
  $stm->bindInt("objectId", $object_id);
  $stm->execute();
}

// open/close window - action

$oldText = ( strpos($object->getSpecifics(), 'open') !== false ) ? 'open' : 'closed'; // which is current state of the window
$newText = ( $oldText == 'open') ? 'closed' : 'open';

$db = Db::get();
if ($newText == 'open'){ // when we open window we must first open it and then check people near to be visible outside
  changeWindowState( $oldText, $newText, $object_id, $db);
}

$window_action_self =   ($newText == 'open') ? _EVENT_WINDOW_OPEN_SELF : _EVENT_WINDOW_CLOSE_SELF;
$window_action_others = ($newText == 'open') ? _EVENT_WINDOW_OPEN_OTHERS : _EVENT_WINDOW_CLOSE_OTHERS;

Event::create($window_action_self, "LOC=" . $char->getLocation())
  ->forCharacter($char)->show();
Event::create($window_action_others, "ACTOR=" . $char->getId() . " LOC=" . $char->getLocation())
  ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();

if ($newText == 'closed'){ // when we close window we must first check people near and then close it to be visible outside
  changeWindowState( $oldText, $newText, $object_id, $db);
}

redirect("char.objects");
