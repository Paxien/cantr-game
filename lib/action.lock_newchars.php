<?php

$player_id = HTTPContext::getInteger('player_id');

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
  $wasDisabled = Limitations::getLims($player_id, Limitations::TYPE_NEW_CHARACTERS) > 0;
  if ($wasDisabled) {
    Limitations::delLims($player_id, Limitations::TYPE_NEW_CHARACTERS);
  } else {
    Limitations::addLim($player_id, Limitations::TYPE_NEW_CHARACTERS, Limitations::dhmstoc(9999,0,0,0));
  }
  
  // report
  $victim = Player::loadById($player_id);
  $content = $playerInfo->getFullNameWithId() . " " . ($wasDisabled ? "enabled" : "disabled") . " possibility of creating new characters for {$victim->getFullNameWithId()}";
  Report::saveInDb("characterlock", $content, $GLOBALS['emailPlayers']);
}

redirect("infoplayer", ["player_id" => $player_id]);
