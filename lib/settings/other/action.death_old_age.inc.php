<?php

$wantDeath = !empty($_REQUEST['old_age_death']);

$isOldEnoughToDie = ($char->getAgeInYears() >= CharacterConstants::OLD_AGE_DEATH_MIN_YEARS);

if ($isOldEnoughToDie) {
  $isGoingToDie = (Limitations::getLims($char->getId(), Limitations::TYPE_OLD_AGE_DEATH_LOCK) > 0);
  if (!$isGoingToDie && $wantDeath) {
    Limitations::addLim($char->getId(), Limitations::TYPE_OLD_AGE_DEATH_LOCK,
      GameDate::fromDate(CharacterConstants::OLD_AGE_DEATH_LOCK_DAYS, 0, 0, 0));

    Limitations::addLim($char->getId(), Limitations::TYPE_OLD_AGE_DEATH_ALLOW,
      GameDate::fromDate(CharacterConstants::OLD_AGE_DEATH_ALLOW_DAYS, 0, 0, 0));
  } elseif ($isGoingToDie && !$wantDeath) {
    Limitations::delLims($char->getId(), Limitations::TYPE_OLD_AGE_DEATH_LOCK);
    Limitations::delLims($char->getId(), Limitations::TYPE_OLD_AGE_DEATH_ALLOW);
  }
}
