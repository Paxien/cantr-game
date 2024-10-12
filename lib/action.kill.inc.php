<?php

// SANITIZE INPUT
$chartokill = HTTPContext::getInteger('chartokill');
$player_id = HTTPContext::getInteger('player_id');

$admin = Request::getInstance()->getPlayer();
if (!$admin->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
  CError::throwRedirectTag("index.php?page=infoplayer&player_id=$player_id", "error_not_authorized");
}

try {
  $charToKill = Character::loadById($chartokill);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("index.php?page=infoplayer&player_id=$player_id", "Character $chartokill does not exist");
}

$charToKill->dieCharacter(CharacterConstants::CHAR_DEATH_PD, $player, true);
$charToKill->saveInDb();

redirect("infoplayer", ["player_id" => $player_id]);
