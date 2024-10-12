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

$sealsManager = new SealsManager($object);
$anonSeals = $sealsManager->getAll(true);

if (count($anonSeals) > 0) {
  CError::throwRedirectTag($backLink, "error_sealed_already");
}

$sealsManager->addAnonymousSeal();

redirect($backLink);