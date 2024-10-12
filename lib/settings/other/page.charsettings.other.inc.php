<?php

$isOldEnoughToDie = ($char->getAgeInYears() >= CharacterConstants::OLD_AGE_DEATH_MIN_YEARS);

$isGoingToDie = Limitations::getLims($char->getId(), Limitations::TYPE_OLD_AGE_DEATH_LOCK) > 0;


$smarty->assign("isGoingToDie", $isGoingToDie);
$smarty->assign("isOldEnoughToDie", $isOldEnoughToDie);
$smarty->assign("OLD_AGE_DEATH_MIN_YEARS", CharacterConstants::OLD_AGE_DEATH_MIN_YEARS);
