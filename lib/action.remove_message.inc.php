<?php

// SANITIZE INPUT
$messageId = HTTPContext::getInteger('message_id');

$db = Db::get();
$stm = $db->prepare("SELECT * FROM pqueue WHERE id = :messageId AND player = :player");
$stm->bindInt("messageId", $messageId);
$stm->bindInt("player", $player);
$stm->execute();
$pqueueInfo = $stm->fetchObject();

if ($player != $pqueueInfo->player) {
  CError::throwRedirectTag("player", "error_remove_msg_not_authorised");
}

$stm = $db->prepare("DELETE FROM pqueue WHERE id = :messageId AND player = :player");
$stm->bindInt("messageId", $messageId);
$stm->bindInt("player", $player);
$stm->execute();

// If the sender is a PD (Prog or Players) member, notify them of the deletion.
if ($pqueueInfo->from != 0) {

  $stm = $db->prepare("SELECT * FROM assignments WHERE player = :from AND council IN (6, 8)");
  $stm->bindInt("from", $pqueueInfo->from);
  $stm->execute();
  if ($stm->exists()) {

    $playerInfo = Player::loadById($player);
    $message = "<b>Player " . $playerInfo->getFullNameWithId() . " has deleted your message:</b><br><br>" . $pqueueInfo->content;
    $messageManager = new MessageManager($db);
    $messageManager->sendMessage(MessageManager::PQUEUE_PD_NOTIFICATION, $pqueueInfo->from, $message, 0);
  }
}

redirect("player");
