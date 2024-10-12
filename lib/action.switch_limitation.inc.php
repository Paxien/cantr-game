<?php

// SANITIZE INPUT
$targetPlayerId = HTTPContext::getInteger('player_id');
$type = HTTPContext::getInteger('type');

$targetPlayer = Player::loadById($targetPlayerId);

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::LOCK_ACCOUNTS)) {
  CError::throwRedirect("player", "You are not authorized to switch who can or cannot describe their characters! (The right goes together with locking accounts.)");
}

$charDescDisallowed = Limitations::getLims($targetPlayerId, Limitations::TYPE_PLAYER_CHARDESCRIPTION);
//1 if limitation is in place, 0 if not

if ($charDescDisallowed == 0) {
  $action = "disallowed";
  Limitations::addLim($targetPlayerId, Limitations::TYPE_PLAYER_CHARDESCRIPTION, Limitations::dhmstoc(9999, 0, 0, 0));
} else {
  $action = "allowed";
  Limitations::delLims($targetPlayerId, Limitations::TYPE_PLAYER_CHARDESCRIPTION);
}

$content = $playerInfo->getFullNameWithId() . " changed limitations for " . $targetPlayer->getFullNameWithId() . ": The player is now $action to edit their own character descriptions.";
Report::saveInDb("playersearch", $content);

redirect("infoplayer", ["player_id" => $targetPlayerId]);
