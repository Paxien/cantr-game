<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');
$removed = HTTPContext::getInteger('removed');

try {
  $storage = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_retrieve_from_illegal_storage");
}

$rootStorage = $storage->getRoot();
$isStorageInInventory = $char->hasInInventory($rootStorage);

// where should we go back
$backLink = $isStorageInInventory ? "char.inventory" : "char.objects";

if (!$char->hasWithinReach($storage)) {

  try {
    $storage->tryAccessibilityInStorage($char, false, false);
  } catch (IllegalStateException $e) {
    Logger::getLogger("page.retrieve.inc.php")->warn("object " . $storage->getId() . " is not in inventory/storage of char " . $char->getId());
    CError::throwRedirectTag($backLink, "error_too_far_away");
  } catch (TooFarAwayException $e) {
    CError::throwRedirectTag($backLink, "error_too_far_away");
  } catch (NoKeyToInnerLockException $e) {
    $e->getLock()->redirectToLockpicking();
  } catch (ObjectSealedException $e) {
    $sealability = $e->getObject()->getProperty("Sealable");
    if ($sealability['canBeBroken']) {
      redirect("break_seal", ["object_id" => $e->getObject()->getId()]);
      exit();
    } else {
      CError::throwRedirectTag($backLink, "error_cannot_break_seal");
    }
  }
}

if (!$storage->hasProperty("Storage")) {
  CError::throwRedirectTag($backLink, "error_retrieve_from_illegal_storage");
}

// check if it's possible to look inside (there can be inner lock)
if (!$storage->hasProperty("IgnoreLookingRestrictions")) {
  $keyLock = KeyLock::loadByObjectId($storage->getId());
  if (!$keyLock->canAccess($char->getId())) {
    $keyLock->redirectToLockpicking();
  }

  if ($storage->hasProperty("Sealable")) {
    $sealsManager = new SealsManager($storage);
    if (count($sealsManager->getAll(true)) > 0) { // there are seals
      redirect("break_seal", ["object_id" => $storage->getId()]);
      exit();
    }
  }
}

$storageHierarchy = $storage->getStorageHierarchy();
$storageHierarchy[] = $storage;

$envelopesInHierarchy = Pipe::from($storageHierarchy)->filter(function(CObject $object) {
  return $object->getType() == ObjectConstants::TYPE_ENVELOPE;
})->toArray();

$noEnvelopesInHierarchy = count($envelopesInHierarchy) == 0;
$allStoragesInHierarchyAreEnvelopes = count($storageHierarchy) == count($envelopesInHierarchy);

$canBeAccessed = $noEnvelopesInHierarchy || $allStoragesInHierarchyAreEnvelopes;
if (!$canBeAccessed) {
  // envelope must be completely outside or stored in another envelope
  CError::throwRedirect($backLink, "You are unable to see contents of this envelope");
}

// fredrik: Check to see if the container is a vehicle engine, and if so, is it
// locked and do we have the key? Locked vehicle means only people in possession
// of the vehicle key can access the fuel.
if ($storage->getObjectCategory()->getId() == ObjectConstants::OBJCAT_ENGINES) {
  $lock = KeyLock::loadByLocationId($char->getLocation());
  if (!$lock->canAccess($char->getId())) {
    CError::throwRedirectTag($backLink, "error_not_right_key");
  }
}

$stored = ObjectsList::generateObjectsArray($storage, $char, $l);

if (count($stored) == 0) { // number of items inside
  CError::throwRedirectTag($backLink, "error_retrieve_from_empty_storage");
}

$characterInfoView = new CharacterInfoView($char);
$characterInfoView->show();
/**/
$inventoryWeight = $char->getInventoryWeight();
$inInventory = $rootStorage->getPerson() > 0;

$allowsManipulation = true;
$cannotManipulateInfo = "";
try {
  $storage->tryAccessibilityOfStorageContents($char, true, false);
} catch (ObjectSealedException $e) {
  $allowsManipulation = false;
  $cannotManipulateInfo = "page_retrieve_cant_manipulate_sealed";
} catch (Exception $e) {
  $allowsManipulation = false;
  $cannotManipulateInfo = "page_retrieve_cant_manipulate_no_key";
}
$allowsManipulation = $storage->areStorageContentsAccessible($char, true, false);

// NEEDS TO BE HANDLED: take => [retrieve on ground, take to inventory], point at object, read note, copy note, open inner cointainer, store into other container

function isNotEnvelope(CObject $object)
{
  return $object->getType() != ObjectConstants::TYPE_ENVELOPE;
}
$objectIds = Pipe::from($stored)->map(function($objDesc) {return $objDesc['id'];})->toArray();

$orderedObjects = CObject::bulkLoadByIds($objectIds);
$objectsMap = [];

foreach ($orderedObjects as $object) {
  $objectsMap[$object->getId()] = $object;
}

foreach ($stored as &$objDesc) {
  $objDesc['buttons'] = Pipe::from($objDesc['buttons'])->filter(function($button)
  use ($char, $objDesc, $allowsManipulation, $inInventory, $noEnvelopesInHierarchy, $allStoragesInHierarchyAreEnvelopes, $objectsMap) {
    return in_array($button['page'], ["pointat", "readnote", "copynote"]) ||
    ($allowsManipulation && in_array($button['page'], ["drop"])) ||
    ($allowsManipulation && $inInventory && in_array($button['page'], ["eatraw"])) ||
    (in_array($button['page'], ["retrieve"]) && ($allStoragesInHierarchyAreEnvelopes ||
        ($noEnvelopesInHierarchy && isNotEnvelope($objectsMap[$objDesc['id']])))) || // no "retrieve" for envelopes
    (in_array($button['page'], ["writenote"]) && $inInventory && $allowsManipulation &&
      ($noEnvelopesInHierarchy && isNotEnvelope($objectsMap[$objDesc['id']])));
  })->toArray();

  $storedObject = $objectsMap[$objDesc['id']];
  foreach ($objDesc['buttons'] as &$button) {
    if ($button['page'] == "drop") {
      $key = array_search($button, $objDesc['buttons']);

      $storageActions = [];
      $toInventoryAction = new ObjectAction("retrieve_inventory", "take", "alt_take");
      $storageActions[] = $toInventoryAction->asArray($storedObject, ["destination" => "inventory"]);

      if (!$char->hasInInventory($storage)) {
        $toGroundAction = new ObjectAction("retrieve_ground", "drop", "alt_drop");
        $storageActions[] = $toGroundAction->asArray($storedObject, ["destination" => "ground"]);
      }
      array_splice($objDesc['buttons'], $key, 1, $storageActions); // replace "take" with "retrieve to inv" and "retrieve to ground"
    }
  }
}

$upLink = $backLink;
if ($storage->getAttached() > 0) { // if it's possible to go one level "up" then do it, otherwise completely leave storage view
  $upLink = "retrieve&object_id=" . $storage->getAttached();
}

JsTranslations::getManager()->addTags(["js_form_retrieve_inventory_amount", "js_form_retrieve_ground_amount",
  "js_take_all_confirmation", "page_eatraw_amount", "state_text", "amount_to_maximize", "change_per_100g", "confirm_reset_objects_order"]);

$hierarchyView = Pipe::from($storageHierarchy)->map(function(CObject $storage) use ($char) {
  $result = (new ObjectView($storage, $char))->show("transfer");
  $result->id = $storage->getId();
  return $result;
})->toArray();

$smarty = new CantrSmarty();
$smarty->assign("objects", $stored);
$smarty->assign("character", $char->getId());
$smarty->assign("storage_id", $object_id); // container object
$smarty->assign("storageHierarchy", $hierarchyView);
$smarty->assign("carry", $inventoryWeight);
$smarty->assign("upLink", $upLink);
$smarty->assign("canManipulate", $allowsManipulation);
$smarty->assign("cannotManipulateInfo", $cannotManipulateInfo);
$smarty->assign("can_carry", $char->getMaxInventoryWeight() - $inventoryWeight);
$smarty->assign("removed", $removed);
$smarty->assign("canBeReordered", $storage->isReorderable());
$smarty->assign("canRetrieveAll", $storage->hasProperty("NoteStorage") && $char->hasInInventory($rootStorage));
$smarty->assign("canRemoveDuplicates", $storage->hasProperty("NoteStorage") && $char->hasInInventory($rootStorage));
$smarty->assign("seals", (new SealsManager($storage))->getAll(false, true));

$smarty->displayLang("page.retrieve.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
