<?php

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::LOCK_THE_GAME)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$db = Db::get();

$stm = $db->query("SELECT locked FROM gamelock");
$locked = $stm->executeScalar();

  if ($locked) {
    $db->query("UPDATE gamelock SET locked=0");
    $action = "unlocked";
  } else {
    $db->query("UPDATE gamelock SET locked=1");
    $action = "locked";
  }

  $mailService = new MailService("Cantr Server", $GLOBALS['emailProgramming']);
  $message = "The game was $action by {$playerInfo->getFullName()} <{$playerInfo->getEmail()}>";
  $mailService->sendPlaintext($GLOBALS['emailProgramming'].",".$GLOBALS['emailGAB'], "Game $action (" . _ENV . ")", $message);


redirect("player");

