<?php

$objectId = HTTPContext::getInteger('object_id');

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
$stm = $db->prepare("SELECT utf8title FROM obj_notes WHERE id = :id");
$stm->bindInt("id", $object->getTypeid());
$bookTitle = $stm->executeScalar();

$smarty = new CantrSmarty();
$smarty->assign("object_id", $object->getId());
$smarty->assign("book_title", $bookTitle);
$smarty->displayLang("page.alter_book_title.tpl", $lang_abr);
