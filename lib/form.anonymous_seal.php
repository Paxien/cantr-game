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

if (!$object->hasAccessToAction("seal_object")) {
  CError::throwRedirect($backLink, "error_not_accessible");
}


$confirmation = new CantrSmarty();
$confirmation->assign("title", "title_seal_object");
$confirmation->assign("text", "seal_object_confirmation");
$confirmation->assign("action", "seal_object");
$confirmation->assign("object_id", $object->getId());
$confirmation->displayLang("confirmation.object_action.tpl", $lang_abr);