<?php

$newTarget = HTTPContext::getInteger('newTarget');
$actionType = $_REQUEST['actionType'];

if (!in_array($actionType, array("add", "remove"))) {
  CError::throwRedirect("", "Error! Invalid value of 'actionType'! The only valid are 'add' and 'remove'");
}

try {
  $target = Character::loadById($newTarget);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("", "error_invalid_character");
}

$db = Db::get();
$stm = $db->prepare("SELECT id FROM bookmark_whispering WHERE owner = :charId AND target = :targetId");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("targetId", $target->getId());
$existingId = $stm->executeScalar();

if ($existingId != null) {
  $stm = $db->prepare("DELETE FROM bookmark_whispering WHERE id = :id");
  $stm->bindInt("id", $existingId);
  $stm->execute();
}

if ($actionType == "add") {
  $stm = $db->prepare("INSERT INTO bookmark_whispering (owner, target) VALUES (:charId, :targetId)");
  $stm->bindInt("charId", $char->getId());
  $stm->bindInt("targetId", $target->getId());
  $stm->execute();
}
