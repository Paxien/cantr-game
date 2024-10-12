<?php

$db = Db::get();

$stm = $db->prepare("SELECT id, name FROM chars WHERE location = 636 AND status <= :active");
$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->execute();

$chars = array();
foreach ($stm->fetchAll() as $char) {
  $chars[$char->id] = $char->name;
}

foreach ($chars as $observerId => $observerName) { // for every observer
  foreach ($chars as $observedId => $observedName) { // add every observed
    if ($observerId != $observedId) {
      $stm = $db->prepare("
      INSERT IGNORE INTO charnaming (observer, observed, name, type, description)
      SELECT :observerId1, :observedId1, :observedName, 1, '' FROM charnaming
      WHERE NOT EXISTS
      (SELECT * FROM `charnaming` WHERE observer = :observerId2 AND observed = :observedId2)");
      $stm->bindStr("observedName", $observedName);
      $stm->bindInt("observerId1", $observerId);
      $stm->bindInt("observerId2", $observerId);
      $stm->bindInt("observedId1", $observedId);
      $stm->bindInt("observedId2", $observedId);
      $stm->execute();
    }
  }
}
