<?php

$noteStoragesInInventory = CObject::inInventoryOf($char)->hasProperties(["Storage", "NoteStorage"])->findAll();


$objectsInInventory = [];
foreach ($noteStoragesInInventory as $noteStorage) {
  $sealsManager = new SealsManager($noteStorage);
  if (count($sealsManager->getAll(true)) > 0) { // sealed -> can't put any notes
    continue;
  }

  $storageWrapper = new Storage($noteStorage);
  $db = Db::get();
  $stm = $db->prepare("SELECT utf8title FROM obj_notes WHERE id = :id");
  $stm->bindInt("id", $noteStorage->getTypeid());
  $title = $stm->executeScalar();
  $typeName = TagBuilder::forObject($noteStorage->getId(), false)->build()->interpret();

  $printableData = [
    "id" => $noteStorage->getId(),
    "name" => $title . " (" . $typeName . ")",
  ];
  $objectsInInventory[] = $printableData;
}


echo json_encode(
  array(
    "noteStorages" => $objectsInInventory,
  )
);
