<?php
// SANITIZE INPUT
$message_id = HTTPContext::getInteger('message_id');

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::MAIL_ALL_PLAYERS)) {
  $db = Db::get();
  $stm = $db->prepare("DELETE FROM messages WHERE id = :messageId");
  $stm->bindInt("messageId", $message_id);
  $stm->execute();

  $stm = $db->prepare("DELETE FROM message_seen WHERE message = :messageId");
  $stm->bindInt("messageId", $message_id);
  $stm->execute();
}

redirect("player");
