<?php

//* Only mails to and from staff members are allowed.
// SANITIZE INPUT
$receiverId = HTTPContext::getInteger('player_id');

try {
  $plr = Request::getInstance()->getPlayer();
  $receiver = Player::loadById($receiverId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("player", "Sent to not existing player (ID: $receiverId)");
}

$allowedStaffMember = $plr->hasAccessTo(AccessConstants::SEND_PRIVATE_MESSAGES);

if ($receiver->hasAccessTo(AccessConstants::SEND_PRIVATE_MESSAGES) || $allowedStaffMember) {

  $smarty = new CantrSmarty;

  $smarty->assign("receiverId", $receiver->getId());
  $smarty->assign("receiverFullName", $receiver->getFullName());
  $smarty->assign("staffmember", $allowedStaffMember);

  $smarty->displayLang("form.sendpm.tpl", $lang_abr);
} else {
  CError::throwRedirect("player", "You are not authorized to send a PM to that player!");
}

