<?php

$objectId = HTTPContext::getInteger('object_id');
$multipleNotes = $_REQUEST['notes'];

// checking if there's valid target envelope

if (!empty($multipleNotes)) { // multiple notes selection
  $noteIds = $multipleNotes;
} else { // single note
  $noteIds = [$objectId];
}

if (!Validation::isPositiveIntArray($noteIds)) {
  CError::throwRedirectTag("char.inventory", "error_no_note");
}

// please consider these don't have to be envelopes, they can also be objects which work like envelopes (e.g. books)
$envelopes = CObject::inInventoryOf($char)->hasProperty("NoteStorage")->exceptIds($noteIds)->findAll();

$envelopes = Pipe::from($envelopes)->filter(function(CObject $envelope) {
  $sealsManager = new SealsManager($envelope);
  return count($sealsManager->getAll(true)) == 0;
})->toArray();


if (count($envelopes) == 0) { // no envelopes allowed
  CError::throwRedirectTag("char.inventory", "error_no_envelop");
}

$envelopeIds = Pipe::from($envelopes)->map(function(CObject $envelope) {
  return $envelope->getId();
})->toArray();

$db = Db::get();
$stm = $db->prepareWithIntList("SELECT o.id, ot.unique_name AS name, utf8title AS title FROM objects o
    INNER JOIN objecttypes ot ON ot.id = o.type
    INNER JOIN obj_notes obn ON obn.id = o.typeid
  WHERE o.id IN (:ids)", [
    "ids" => $envelopeIds,
]);
$stm->execute();

$envs = $stm->fetchAll();

$smarty = new CantrSmarty();
$smarty->assign ("envelopes", $envs);
$smarty->assign ("notes", implode(", ", $noteIds));
$smarty->displayLang ("form.fill_envelop.tpl", $lang_abr);
