<?php

$objectId = HTTPContext::getInteger('object_id');

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

$db = Db::get();
$stm = $db->prepare("SELECT utf8title FROM obj_notes WHERE id = :id");
$stm->bindInt("id", $object->getTypeid());
$bookTitle = $stm->executeScalar();

$confirmation = new CantrSmarty();
$confirmation->assign("object_id", $object->getId());
$confirmation->assign("book_title", $bookTitle);
$confirmation->displayLang("page.bind_book.tpl", $lang_abr);
