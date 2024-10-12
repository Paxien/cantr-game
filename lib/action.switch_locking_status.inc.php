<?php

$playerIdToLock = HTTPContext::getInteger('player_id');
$playerToLock = Player::loadById($playerIdToLock);

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::LOCK_ACCOUNTS)) {
  CError::throwRedirect("player", "You are not authorized to switch another player'slocking status!");
}

if ($playerToLock->getStatus() == PlayerConstants::LOCKED) {
  $action = "unlocked";
  $playerToLock->setStatus(PlayerConstants::APPROVED);
} else {
  $action = "locked";
  $playerToLock->setStatus(PlayerConstants::LOCKED);
}
$playerToLock->saveInDb();


$reportMessage = $playerInfo->getFullNameWithId() . " $action the account of " . $playerToLock->getFullNameWithId();
Report::saveInDb("playersearch", $reportMessage);

redirect("infoplayer", ["player_id" => $playerIdToLock]);
