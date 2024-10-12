<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

$db = Db::get();

try {
  $noteObject = CObject::loadById($object_id);
  $note = new NoteHolder($noteObject);
  $rootObject = $noteObject->getRoot();
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

if ($noteObject->getAttached() > 0) {
  $backLink = "retrieve&object_id=" . $noteObject->getAttached();
} elseif ($noteObject->getLocation() > 0) {
  $backLink = "char.objects";
} else {
  $backLink = "char.inventory";
}

if (!$char->hasWithinReach($noteObject) && !$noteObject->isAccessibleInStorage($char, false, false)) {
  CError::throwRedirectTag($backLink, "error_too_far_away");
}

if (!$noteObject->hasAccessToAction("copynote")) {
  CError::throwRedirectTag($backLink, "error_not_authorized");
}

if ($note->isEditable()) { // create a copy of note contents
  $stm = $db->prepare("INSERT INTO obj_notes (setting, encoding, utf8title, utf8contents, converted)
    SELECT setting, encoding, utf8title, utf8contents, converted FROM obj_notes WHERE id = :id");
  $stm->bindInt("id", $noteObject->getTypeid());
  $stm->execute();
  $note_id = $db->lastInsertId();
} else {
  $note_id = $noteObject->getTypeid();
}


ObjectCreator::inInventory($char, ObjectConstants::TYPE_NOTE, ObjectConstants::SETTING_PORTABLE, 0)->typeid($note_id)->create();

// note change log
$gameDate = GameDate::NOW();
$currentDate = "{$gameDate->getDay()}-{$gameDate->getHour()}:{$gameDate->getMinute()}";
$stm = $db->prepare("INSERT INTO obj_notes_log (char_id, note_id, object_id, action, date)
  VALUES (:charId, :objNoteId, :objectId, 'copy', :date)");
$stm->bindInt("charId", $character);
$stm->bindInt("objNoteId", $note_id);
$stm->bindInt("objectId", $object_id);
$stm->bindStr("date", $currentDate);
$stm->execute();

if ($rootObject->getLocation() > 0) {

  Event::create(158, "ACTOR=" . $char->getId())->nearCharacter($char)->except($char)->show();
  
  $noteTitle = urlencode("'". $note->getTitle()) . '"';
  Event::create(157, "NOTE=$noteTitle")->forCharacter($char)->show();
}

redirect($backLink);