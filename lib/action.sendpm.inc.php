<?php

// SANITIZE INPUT
$receiverId = HTTPContext::getInteger('player_id');
$new = HTTPContext::getInteger('new');

$message_text = $_REQUEST['message_text']; // shouldn't be escaped, it's done in pqueue

try {
  $plr = Request::getInstance()->getPlayer();
  $receiver = Player::loadById($receiverId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("player", "Sent to not existing player (ID: $receiverId)");
}

$allowedStaffMember = $plr->hasAccessTo(AccessConstants::SEND_PRIVATE_MESSAGES);

if (!$allowedStaffMember) {
  $new = 1;
}

if ($receiver->hasAccessTo(AccessConstants::SEND_PRIVATE_MESSAGES) || $allowedStaffMember) {

  $message_text = nl2br(htmlspecialchars($message_text));
  $message_text = "<B>From</B>: " . $plr->getFullName() . "<BR><BR><B>Message</B>:<BR>" . $message_text;
  $message_text = $message_text . "<BR>QQQadd reply buttonQQQ";

  $messageManager = new MessageManager(Db::get());
  $messageManager->sendMessage($player, $receiverId, $message_text, max($new, 2));

  if ($allowedStaffMember) {
    redirect("infoplayer", ["player_id" => $receiverId]);
  } else {
    redirect("player");
  }
} else {
  CError::throwRedirect("player", "You are not authorized to send a PM to that player!");
}
