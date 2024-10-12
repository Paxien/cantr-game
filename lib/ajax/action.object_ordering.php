<?php

$newOrdering = $_REQUEST['newOrdering'];
$oldOrdering = $_REQUEST['oldOrdering'];

$storageId = HTTPContext::getInteger('storage');
$locationId = HTTPContext::getInteger('location');
$ownerId = HTTPContext::getInteger('owner');

$resetToDefault = HTTPContext::getBoolean('resetToDefault');

$newOrdering = json_decode($newOrdering);
$oldOrdering = json_decode($oldOrdering);

if (empty($oldOrdering) || !Validation::isPositiveIntArray($oldOrdering)) {
  CError::throwRedirectTag("", "error_object_not_found");
}

$db = Db::get();

if ($storageId > 0) {
  try {
    $parentEntity = CObject::loadById($storageId);
    if (!$parentEntity->areStorageContentsAccessible($char, true)) {
      throw new InvalidArgumentException("not in storage");
    }
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("", "error_too_far_away");
  }

  $stm = $db->prepareWithIntList("SELECT COUNT(*) FROM objects WHERE id IN (:ids) AND attached = :storageId", ["ids" => $oldOrdering]);
  $stm->bindInt("storageId", $storageId);
  $objectsHere = $stm->executeScalar();

} elseif ($locationId > 0) {
  try {
    $parentEntity = Location::loadById($locationId);
    if ($char->getLocation() != $parentEntity->getId()) {
      throw new InvalidArgumentException("not in the same location");
    }
    die("TEMPORARILY ALLOWED ONLY FOR STORAGES");
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("", "error_too_far_away");
  }

  $stm = $db->prepareWithIntList("SELECT COUNT(*) FROM objects WHERE id IN (:ids) AND location = :locationId", ["ids" => $oldOrdering]);
  $stm->bindInt("locationId", $locationId);
  $objectsHere = $stm->executeScalar();

} elseif ($ownerId > 0) {
  try {
    $parentEntity = Character::loadById($ownerId);
    if ($char->getId() != $parentEntity->getId()) {
      throw new InvalidArgumentException("not own inventory");
    }
    die("TEMPORARILY ALLOWED ONLY FOR STORAGES");
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("", "error_too_far_away");
  }

  $stm = $db->prepareWithIntList("SELECT COUNT(*) FROM objects WHERE id IN (:ids) AND person = :ownerId", ["ids" => $oldOrdering]);
  $stm->bindInt("ownerId", $ownerId);
  $objectsHere = $stm->executeScalar();

} else {
  CError::throwRedirect("", "error_too_far_away");
}

if ($objectsHere != count($oldOrdering)) {
  CError::throwRedirect("", "Some of the objects have gone away");
}

$stm = $db->prepareWithIntList("SELECT id, ordering FROM objects WHERE id IN (:ids) ORDER BY ordering", ["ids" => $oldOrdering]);
$stm->execute();

$currentOrdering = Pipe::from($stm->fetchAll())->mapKV(function($key, $row) {
  return [$row->id => $row->ordering];
})->toArray();

$orderCorrect = true;

$prev = -1;
foreach ($oldOrdering as $objId) {
  if (array_key_exists($objId, $currentOrdering)) {
    $order = $currentOrdering[$objId];
    if ($prev > $order) {
      $orderCorrect = false;
    }
    $prev = $order;
  }
}

if (!$orderCorrect) {
  CError::throwRedirect("", "Some of the objects have been reordered in the mean time");
}

// RESET
if ($resetToDefault) {

  $stm = $db->prepareWithIntList("UPDATE `objects` SET ordering = 0 WHERE id IN (:ids)", ["ids" => $oldOrdering]);
  $stm->execute();

  $objectsList = ObjectsList::generateObjectsArray(CObject::loadById($storageId), $char, $char->getLanguage());
  $ids = Pipe::from($objectsList)->map(function($objInfo) {
    return $objInfo['id'];
  })->toArray();

  echo json_encode(["newOrdering" => $ids]);
  exit();

} else {

  if (!Validation::isPositiveIntArray($newOrdering)) {
    CError::throwRedirect("char.events", "Incorrect objects order");
  }

  $stm = $db->prepare("UPDATE `objects` SET ordering = :order WHERE id = :id");
  $order = 0;
  foreach ($newOrdering as $objId) {
    $stm->bindInt("id", $objId);
    $stm->bindInt("order", $order);
    $stm->execute();
    $order++;
  }
}