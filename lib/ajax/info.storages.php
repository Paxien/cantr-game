<?php

function getStoredObjectsText(CObject $storage) {
  $stored = CObject::storedIn($storage)->findAll();

  if (empty($stored)) {
    return "";
  }

  $stored = Pipe::from($stored)->filter(function(CObject $obj) {
    return $obj->getType() != ObjectConstants::TYPE_INNER_LOCK;
  })->toArray();

  usort($stored, function(CObject $a, CObject $b) {
    return $a->getWeight() < $b->getWeight();
  });

  $stored = array_slice($stored, 0, 3);

  $contentList = Pipe::from($stored)->map(function (CObject $obj) {
    return "<CANTR OBJNAME ID=". $obj->getId() ." TYPE=1>";
  })->map(function($objText) {
    return (new Tag($objText))->interpret();
  })->toArray();

  return " - ". implode(", ", $contentList);
}

$storagesInInventory = CObject::inInventoryOf($char)->hasProperty("Storage")
  ->hasNotProperty("NoteStorage")->findAll();

$storagesOnGround = [];
if ($char->getLocation() > 0) {
  $storagesOnGround = CObject::locatedIn($char->getLocation())->hasProperty("Storage")
    ->hasNotProperty("NoteStorage")->findAll();
}

$objectsInInventory = [];
foreach ($storagesInInventory as $storage) {
  $storageWrapper = new Storage($storage);
  $printableData = $storageWrapper->getPrintableData($char);
  $printableData['maxPossible'] = $printableData['space'];
  $printableData['name'] .= " (" . $printableData['space'] . ")" . getStoredObjectsText($storage);
  $objectsInInventory[] = $printableData;
}

$objectsOnGround = [];
foreach ($storagesOnGround as $storage) {
  $storageWrapper = new Storage($storage);
  $printableData = $storageWrapper->getPrintableData($char);
  $printableData['maxPossible'] = $printableData['space'];
  $printableData['name'] .= " (" . $printableData['space'] . ")" . getStoredObjectsText($storage);
  $objectsOnGround[] = $printableData;
}


echo json_encode(
  array(
    "storages" => array(
      "storagesInInventory" => $objectsInInventory,
      "storagesOnGround" => $objectsOnGround,
    )
  )
);
