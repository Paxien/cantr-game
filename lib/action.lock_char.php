<?php

// SANITIZE INPUT

$action = HTTPContext::getRawString("action");
$charid = HTTPContext::getInteger('charid');
$lockDays = HTTPContext::getInteger('lock_time');

$admin = Request::getInstance()->getPlayer();
if ($admin->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
  if ($action == 'lock') {
    if ($lockDays == 0) {
      $lockDays = 999; // "permanent"
    }

    Limitations::addLim($charid, Limitations::TYPE_LOCK_CHAR, Limitations::dhmstoc($lockDays, 0, 0, 0));
    $action = "locked (for $lockDays days)";
  } elseif ($action == 'unlock') {
    Limitations::delLims($charid, Limitations::TYPE_LOCK_CHAR);
    $action = "unlocked";
  }

  $lockedCharacter = Character::loadById($charid);
  $playerOfCharacter = Player::loadById($lockedCharacter->getPlayer());

  // report
  $reportText = $admin->getFullNameWithId() . " $action the character " . $lockedCharacter->getName() .
    " (ID: " . $lockedCharacter->getId() .") of " . $playerOfCharacter->getFullNameWithId();
  Report::saveInDb("characterlock", $reportText, $GLOBALS['emailGAB'], "report of character lock in PD panel");

  redirect("infoplayer", ["player_id" => $playerOfCharacter->getId()]);
}
