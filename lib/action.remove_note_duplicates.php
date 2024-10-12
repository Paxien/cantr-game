<?php

$objectId = HTTPContext::getInteger('object_id');

import_lib("func.expireobject.inc.php");

$nowDate = GameDate::NOW();

try {
  $storage = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("char.inventory", "not an envelope");
}

if (!$storage->areStorageContentsAccessible($char, true, true)) {
  CError::throwRedirect("char.events", "envelope not in inventory");
}

if (!$storage->hasProperty("NoteStorage")) {
  CError::throwRedirect("index.php?page=retrieve&object_id=" . $storage->getId(), "not an envelope");
}


function logRemovedDuplicates(array $objIds, array $noteIds, GameDate $date, $charId, Db $db)
{
  $dateStr = $date->getDay() ."-". $date->getHour() .":". $date->getMinute();
  $stm = $db->prepare("INSERT INTO obj_notes_log (char_id, note_id, action, date, object_id)
    VALUES (:charId, :objNoteId, 'delete_duplicate', :date, :objectId)");
  foreach (array_combine($objIds, $noteIds) as $objId => $noteId) {
    $stm->bindInt("charId", $charId);
    $stm->bindInt("objNoteId", $noteId);
    $stm->bindStr("date", $dateStr);
    $stm->bindInt("objectId", $objId);
    $stm->execute();
  }
}

$db = Db::get();
$envelopeNoteId = $storage->getTypeid();

// group_concat needs more space :D
$db->query("SET SESSION group_concat_max_len = 100000");

$removed = 0;

// uneditable notes pointing on exactly the same row in obj_notes
$stm = $db->prepare("SELECT GROUP_CONCAT(o.id) AS notes, typeid FROM objects o
  INNER JOIN obj_notes n ON n.id = o.typeid AND
    n.setting = :setting
  WHERE type = :type AND attached = :storage AND
    NOT EXISTS (SELECT s.note FROM seals s WHERE s.note = o.id)
  GROUP BY typeid
    HAVING COUNT(*) > 1");
$stm->bindInt("setting", Note::NOTE_SETTING_UNEDITABLE);
$stm->bindInt("type", ObjectConstants::TYPE_NOTE);
$stm->bindInt("storage", $storage->getId());
$stm->execute();

foreach ($stm->fetchAll() as $notesList) {
  $notes = explode(",", $notesList->notes);
  array_pop($notes); // ignore one of notes, so only duplicates will be removed
  $removed += count($notes);

  $noteIds = array_fill(0, count($notes), $notesList->typeid);
  logRemovedDuplicates($notes, $noteIds, $nowDate, $char->getId(), $db);

  $notesList = implode(",", $notes);
  expire_multiple_objects("id IN ($notesList) AND attached = " . $storage->getId());
}

// editable notes having the same (like java's "equals") contents
$stm = $db->prepare("SELECT GROUP_CONCAT(o.id) FROM objects o
    INNER JOIN obj_notes n ON n.id = o.typeid
  WHERE o.type = :type AND
    n.setting = :setting AND
    o.attached = :storage
  GROUP BY title, contents, encoding, utf8title, utf8contents
    HAVING COUNT(*) > 1");
$stm->bindInt("type", ObjectConstants::TYPE_NOTE);
$stm->bindInt("setting", Note::NOTE_SETTING_EDITABLE);
$stm->bindInt("storage", $storage->getId());
$stm->execute();
foreach ($stm->fetchScalars() as $notesList) {
  $notes = explode(",", $notesList);
  array_pop($notes); // ignore one of notes, so only duplicates will be removed
  $removed += count($notes);

  sort($notes, SORT_NUMERIC); // to keep objIds matched with noteIds later

  $notesList = implode(",", $notes);
  $stm = $db->prepareWithIntList("SELECT typeid FROM objects
    WHERE id IN (:notesList) AND attached = :storage ORDER BY id", [
      "notesList" => $notes,
  ]);
  $stm->bindInt("storage", $storage->getId());
  $stm->execute();
  $objNotesIds = $stm->fetchScalars();

  expire_multiple_objects("id IN ($notesList) AND attached = " . $storage->getId());

  if (count($objNotesIds) != count($notes)) {
    Logger::getLogger("remove_note_duplicates")->error("number of notes and corresponding objNotesIds doesn't match; objIds: ". count($notes) ." | objNotesIds: ". count($objNotesIds));
  }

  // both notes and objNotesIds are ordered by objId
  logRemovedDuplicates($notes, $objNotesIds, $nowDate, $char->getId(), $db);

  $stm = $db->prepareWithIntList("DELETE FROM obj_notes WHERE id IN (:ids)", [
    "ids" => $objNotesIds,
  ]);
  $stm->execute();
}

redirect("retrieve", ["object_id" => $storage->getId(), "removed" => $removed]);

