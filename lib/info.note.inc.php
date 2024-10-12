<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

try {
  $noteObject = CObject::loadById($object_id);
  $noteHolder = new NoteHolder($noteObject);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

$storageHierarchy = $noteObject->getStorageHierarchy();
$rootObject = $noteObject->getRoot();

if ($noteObject->getAttached() > 0) {
  $backLink = "retrieve&object_id=". $noteObject->getAttached();
} elseif ($rootObject->getPerson() > 0) {
  $backLink = "char.inventory";
} else {
  $backLink = "char.objects";
}

if (!$char->hasWithinReach($noteObject) && !$noteObject->isAccessibleInStorage($char, false, false)) {
  CError::throwRedirect($backLink, "You cannot read this note as it is not in your inventory and not on the same location as you are.");
}

// if note is not outside
if ($rootObject->getId() != $noteObject->getId()) {
  $envelopesInHierarchy = Pipe::from($storageHierarchy)->filter(function(CObject $object) {
    return $object->getType() == ObjectConstants::TYPE_ENVELOPE;
  })->toArray();

  $noEnvelopesInHierarchy = count($envelopesInHierarchy) == 0;
  $allStoragesInHierarchyAreEnvelopes = count($storageHierarchy) == count($envelopesInHierarchy);
  // Disallow reading notes in envelope that is placed in any non-envelope storage.
  // it's to encourage usage of more logical and powerful ways for storing notes

  $canBeRead = $noEnvelopesInHierarchy || $allStoragesInHierarchyAreEnvelopes;
  if (!$canBeRead) {
    // must be completely outside or stored in envelope
    CError::throwRedirectTag($backLink, "error_too_far_away");
  }
}

$smarty = new CantrSmarty;

$smarty->assign("back_link", $backLink);
$smarty->assign("note_title", $noteHolder->getTitle());

$content = $noteHolder->getContents();
$printable = wordwrap($content, 85);
$smarty->assign("contents", $printable);

$sealsManager = new SealsManager($noteObject);
$seals = Pipe::from($sealsManager->getAll())->map(function($seal) {
  return TextFormat::getDistinctHtmlText($seal);
})->toArray();

$smarty->assign("Seals", $seals);

function getObjectWithIdx($objectsInStorage, $idx) {
  if ($idx >= 0 && $idx < count($objectsInStorage)) {
    try {
      $note = CObject::loadById($objectsInStorage[$idx]);
      if ($note->hasProperty("Readable")) {
        $noteHolder = new NoteHolder($note);
        return [
          "id" => $note->getId(),
          "title" => $noteHolder->getTitle(),
        ];
      }
    } catch (InvalidArgumentException $e) {
      Logger::getLogger("info.note.inc.php")->warn("inexistent note with id: " . $objectsInStorage[$idx] . "on index: " . $idx);
    }
  }
  return null;
}

$parentStorage = $noteObject->getParent();
if ($parentStorage != null && $parentStorage->hasProperty("NoteStorage") && $parentStorage->getType() != ObjectConstants::TYPE_ENVELOPE) {
  $objectsInStorage = ObjectsList::getOrderedObjectIds($parentStorage);
  $idx = array_search($noteObject->getId(), $objectsInStorage);

  $smarty->assign("previousNote", getObjectWithIdx($objectsInStorage, $idx - 1));
  $smarty->assign("nextNote", getObjectWithIdx($objectsInStorage, $idx + 1));
}

$smarty->displayLang("info.note.tpl", $lang_abr); 

