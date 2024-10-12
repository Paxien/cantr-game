<?php

$objectId = HTTPContext::getInteger('object_id');
$bookTitle = $_REQUEST['book_title'];

try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

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

if (!$object->hasAccessToAction("alter_book_title")) {
  CError::throwRedirect($backLink, "error_not_accessible");
}

$sealsManager = new SealsManager($object);
$anonSeals = $sealsManager->getAll(true);

if (count($anonSeals) > 0) {
  CError::throwRedirectTag($backLink, "error_sealed_already");
}

$db = Db::get();
$stm = $db->prepare("UPDATE obj_notes SET utf8title = :bookTitle WHERE id = :noteId");
$stm->bindStr('bookTitle', $bookTitle);
$stm->bindInt('noteId', $object->getTypeid());
$stm->execute();

redirect($backLink);
