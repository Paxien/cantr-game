<?php

if (Limitations::getLims($char->getId(), Limitations::TYPE_OLD_AGE_DEATH_ALLOW) == 0) {
  CError::throwRedirectTag("char.events", "error_cant_die_yet");
}

$char->dieCharacter(CharacterConstants::CHAR_DEATH_EXPIRED, 1, true);
$char->saveInDb();

redirect("player");
