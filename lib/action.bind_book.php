<?php

$objectId = HTTPContext::getInteger('object_id');
$bookTitle = $_REQUEST['book_title'];

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

// check accessibility
if ($object->getSetting() == ObjectConstants::SETTING_FIXED) {
  $backLink = "char.objects";
  if (!$char->isInSameLocationAs($object)) {
    CError::throwRedirectTag($backLink, "error_too_far_away");
  }
} else {
  $backLink = "char.inventory";
  if (!$char->hasInInventory($object)) {
    CError::throwRedirectTag($backLink, "error_too_far_away");
  }
}

if (!$object->hasAccessToAction("bind_book")) {
  CError::throwRedirect($backLink, "error_not_accessible");
}

if (empty($bookTitle)) {
  CError::throwRedirectTag("bind_book&object_id=$objectId", "error_book_no_title");
}

$sealsManager = new SealsManager($object);
$anonSeals = $sealsManager->getAll(true);

if (count($anonSeals) > 0) {
  CError::throwRedirectTag($backLink, "error_sealed_already");
}

$sealsManager->addAnonymousSeal();

$objNotesId = $object->getTypeid(); // set book title
$db = Db::get();
$stm = $db->prepare("UPDATE obj_notes SET utf8title = :title WHERE id = :id");
$stm->bindStr("title", $bookTitle);
$stm->bindInt("id", $objNotesId);
$stm->execute();

$notes = CObject::storedIn($object)->type(ObjectConstants::TYPE_NOTE)->findAll();
foreach ($notes as $note) {
  $noteHolder = new NoteHolder($note);
  $noteHolder->setEditable(Note::NOTE_SETTING_UNEDITABLE);
  $noteHolder->saveInDb();
}

redirect($backLink);
