<?php

// SANITIZE INPUT
$type = HTTPContext::getInteger('type', 1);

$target_id = HTTPContext::getInteger('target_id');

$order = HTTPContext::getInteger('order', null);
//we don't want to remove tags from $name - user can use <CANTR CHARDESC> tag
//we manually use htmlentities() before store data in DB
$name = $_REQUEST['name'];
$personalDesc = $_REQUEST['personalDesc'];
$character = HTTPContext::getInteger('character');
$next = $_REQUEST['next'];

$db = Db::get();

$stm = $db->prepare("SELECT COUNT(*) FROM charnaming WHERE observer = :observer AND observed = :observed AND type = :type");
$stm->bindInt("observer", $char->getId());
$stm->bindInt("observed", $target_id);
$stm->bindInt("type", $type);
$count = $stm->executeScalar();

if (($type == NamingConstants::TYPE_LOCATION) && ($order)) {

  $stm = $db->prepare("SELECT name FROM signs WHERE location = :locationId AND signorder = :order LIMIT 1 ");
  $stm->bindInt("locationId", $target_id);
  $stm->bindInt("order", $order);
  $name = $stm->executeScalar();
}

if ($name) {
  $name = htmlspecialchars($name);
  $name = str_replace(htmlspecialchars("<CANTR CHARDESC>"), "<CANTR CHARDESC>", $name);
} else if ($type == NamingConstants::TYPE_CHAR) {
  $name = "<CANTR CHARDESC>";
}

if ($type == NamingConstants::TYPE_LOCATION) {
  $stm = $db->prepare("SELECT loyal_to FROM animal_domesticated WHERE from_location = :locationId");
  $stm->bindInt("locationId", $target_id);
  $owner = $stm->executeScalar();
  if ($owner > 0) { // replace [OWNER] to owner name
    $name = str_replace("[OWNER]", "<CANTR CHARNAME ID=$owner>", $name);
  }
}

$personalDesc = str_replace("\r", "", htmlspecialchars ($personalDesc));

$name = trim($name);
if ($name) {
  if (($type == NamingConstants::TYPE_CHAR) && ($target_id == $character)) {
    $char->setName($name);
    $char->saveInDb();
  }
  
  if ($count) {
    $stm = $db->prepare("UPDATE charnaming SET name = :name, description = :description WHERE observer = :observer AND observed = :observed AND type = :type");
    $stm->bindStr("name", $name);
    $stm->bindStr("description", $personalDesc);
    $stm->bindInt("observer", $char->getId());
    $stm->bindInt("observed", $target_id);
    $stm->bindInt("type", $type);
    $stm->execute();
  } else {
    $stm = $db->prepare("INSERT INTO charnaming (type, name, observer, observed, description) VALUES (:type, :name, :observer, :observed, :description)");
    $stm->bindStr("name", $name);
    $stm->bindStr("description", $personalDesc);
    $stm->bindInt("observer", $char->getId());
    $stm->bindInt("observed", $target_id);
    $stm->bindInt("type", $type);
    $stm->execute();
  }
} else {
  $stm = $db->prepare("DELETE FROM charnaming WHERE observer = :observer AND observed = :observed AND type = :type");
  $stm->bindInt("observer", $char->getId());
  $stm->bindInt("observed", $target_id);
  $stm->bindInt("type", $type);
  $stm->execute();
}

redirect($next);
