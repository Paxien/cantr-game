<?php

$notename = $_REQUEST['notename'];
$notetext = $_REQUEST['notetext'];

// to notify people who make obvious cracking attempts or copypaste whole note html source
import_lib('func.strip_html_tags.inc.php');
if (!check_event_strings($notetext)) {
  $playerInfo = Request::getInstance()->getPlayer();
  $env = Request::getInstance()->getEnvironment();
  $subject = "Possible hacking attempt ". $env->getFullName();
  $message = "Player     : " . $playerInfo->getId() . ", " . $playerInfo->getFullName() . "\n";
  $message .= "Extra info : \n\n" . __FILE__ ."\n\nNote text:\n$notetext";

  $mailService = new MailService("Cantr Programming", $GLOBALS['emailProgramming']);
  $mailService->sendPlaintext($GLOBALS['emailProgramming'], $subject, $message);

  CError::throwRedirectTag("player", "error_invalid_input");
}

// SANITIZE INPUT
$objectId = HTTPContext::getInteger('object_id');
$noteSetting = HTTPContext::getInteger('setting');
$notename = htmlspecialchars($notename);

try {
  $readableObject = CObject::loadById($objectId);
  $noteHolder = new NoteHolder($readableObject);
} catch (InvalidArgumentException $e) { // no such note
  CError::throwRedirectTag("char.events", "error_too_far_away");
} catch (IllegalStateException $e) { // no row in `obj_notes`
  Logger::getLogger("action.editnote.inc.php")->warn("object " . $objectId . " doesn't have a row in obj_notes for id " . $readableObject->getTypeid());
  CError::throwRedirect("char.events", "<CANTR REPLACE NAME=error_not_authorized>\n" . "You can not edit not existing note.");
}

$keyLock = KeyLock::loadByObjectId($readableObject->getId());
if (!$keyLock->canAccess($char->getId())) {
  $keyLock->redirectToLockpicking();
}

// purify input text
$notePurifier = new NotePurifier();
$cleanedText = $notePurifier->purify($notetext);

$isClassicNote = $readableObject->getType() == ObjectConstants::TYPE_NOTE;
$backLink = $readableObject->getPerson() > 0 ? "char.inventory" : "char.objects";

$isFixedObjectNear = in_array($readableObject->getSetting(), [ObjectConstants::SETTING_FIXED, ObjectConstants::SETTING_HEAVY])
  && $char->isInSameLocationAs($readableObject); // noticeboard in the same location as char

if (!($char->hasInInventory($readableObject) || $isFixedObjectNear || $readableObject->isAccessibleInStorage($char, true, true))) {
  CError::throwRedirectTag($backLink, "error_too_far_away");
}

if ($readableObject->getAttached() > 0) {
  $backLink = "retrieve&object_id=" . $readableObject->getAttached();
}

if (!in_array($noteSetting, [Note::NOTE_SETTING_EDITABLE, Note::NOTE_SETTING_UNEDITABLE])) {
  $noteSetting = Note::NOTE_SETTING_EDITABLE;
}

if (!$noteHolder->isEditable()) {
  CError::throwRedirect($backLink, "This note is set to be not editable");
}

$date = GameDate::NOW();
$dateStr = $date->getDay() . "-" . $date->getHour() . ":" . $date->getMinute();
$db = Db::get();

$oldTitle = $noteHolder->getTitle();
$oldContents = $noteHolder->getContents();

//if both title and contents are blank, delete it, don't delete for containers related
if ($isClassicNote && empty($notename) && empty($cleanedText)) {
  // deleting note contents
  $noteHolder->removeContents();
  $noteHolder->saveInDb();

  // deleting the object
  $readableObject->remove();
  $readableObject->saveInDb();

  // logging
  $stm = $db->prepare("INSERT INTO obj_notes_log (char_id, note_id, object_id, action, date, prev_title, prev_contents)
    VALUES (:charId, :objNoteId, :objectId, 'delete', :date, :oldTitle, :oldContents)");
  $stm->bindInt("charId", $char->getId());
  $stm->bindInt("objNoteId", $readableObject->getTypeid());
  $stm->bindInt("objectId", $readableObject->getId());
  $stm->bindStr("date", $dateStr);
  $stm->bindStr("oldTitle", $oldTitle);
  $stm->bindStr("oldContents", $oldContents);
  $stm->execute();
} else {
  $noteHolder->setTitleAndContents($notename, $cleanedText);
  if ($isClassicNote) {
    $noteHolder->setEditable($noteSetting);
  }
  $noteHolder->saveInDb();

  // logging
  $stm = $db->prepare("INSERT INTO obj_notes_log (char_id, note_id, object_id, action, date, prev_title, prev_contents)
    VALUES (:charId, :objNoteId, :objectId, 'edit', :date, :oldTitle, :oldContents)");
  $stm->bindInt("charId", $char->getId());
  $stm->bindInt("objNoteId", $readableObject->getTypeid());
  $stm->bindInt("objectId", $readableObject->getId());
  $stm->bindStr("date", $dateStr);
  $stm->bindStr("oldTitle", $oldTitle);
  $stm->bindStr("oldContents", $oldContents);
  $stm->execute();

  if ($readableObject->getType() == ObjectConstants::TYPE_NOTICEBOARD) {
    $notename = urlencode($notename);

    Event::create(_EVENT_EDITCONTAINERNOTE_SELF, "NOTEID=" . $readableObject->getTypeid() . " CONTAINERID=" . $readableObject->getId() . " NOTETITLE=$notename")
      ->forCharacter($char)->show();

    Event::create(_EVENT_EDITCONTAINERNOTE_OTHERS,
      "ACTOR=" . $char->getId() . " NOTEID=" . $readableObject->getTypeid() . " CONTAINERID=" . $readableObject->getId() . " NOTETITLE=$notename")
        ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
  }
}

redirect($backLink);
