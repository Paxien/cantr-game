<?php

$objectId = HTTPContext::getInteger('object_id');

$notename = "";
$notetext = "";

$canBecomeUneditable = true;
$isinobject = false;
$backLink = "char.inventory";

if ($objectId != null) { // editing already existing note (readable object), not creating a new one
  try {
    $readableObject = CObject::loadById($objectId);
    $noteHolder = new NoteHolder($readableObject);
    $notename = $noteHolder->getTitle();
    $notetext = $noteHolder->getContents();

  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag($backLink, "too_far_away");
  } catch (IllegalStateException $e) { // no row in `obj_notes`
    Logger::getLogger("from.writenote.inc.php")->warn("object " . $objectId . " doesn't have a row in obj_notes for id " . $readableObject->getTypeid());
    CError::throwRedirect($backLink, "<CANTR REPLACE NAME=error_not_authorized>\n" . "You can not edit not existing note.");
  }

  // turn off settings if it isn't a classic note
  $isClassicNote = $readableObject->getType() == ObjectConstants::TYPE_NOTE;
  $canBecomeUneditable = $isClassicNote;

  $isinobject = !$canBecomeUneditable;
  $backLink = $readableObject->getPerson() > 0 ? "char.inventory" : "char.objects";

  $inInventory = $char->hasInInventory($readableObject);

  $fixedObjectWithinReach = in_array($readableObject->getSetting(), [ObjectConstants::SETTING_FIXED, ObjectConstants::SETTING_HEAVY])
    && $char->isInSameLocationAs($readableObject);
  if (!($inInventory || $fixedObjectWithinReach || $readableObject->isAccessibleInStorage($char, true, true))) {
    CError::throwRedirectTag($backLink, "error_too_far_away");
  }

  $keyLock = KeyLock::loadByObjectId($readableObject->getId());
  if (!$keyLock->canAccess($char->getId())) {
    $keyLock->redirectToLockpicking();
  }
}

JsTranslations::getManager()->addTags(["confirm_note_no_preview"]);

$smarty = new CantrSmarty();
$smarty->assign("notename", $notename);
$smarty->assign("notetext", htmlspecialchars($notetext));
$smarty->assign("id", $id);
$smarty->assign("object_id", $objectId);
$smarty->assign("needsettings", $canBecomeUneditable);
$smarty->assign("isinobject", $isinobject);
$smarty->assign("backLink", $backLink);
$smarty->assign("charId", $char->getId());
$smarty->assign("requireConfirmation", (PlayerSettings::getInstance($player)->get(PlayerSettings::CONFIRM_NO_PREVIEW) == 0 ? "true" : "false"));

$smarty->displayLang ("form.writenote.tpl", $lang_abr); 
