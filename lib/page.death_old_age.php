<?php

if (Limitations::getLims($char->getId(), Limitations::TYPE_OLD_AGE_DEATH_ALLOW) == 0) {
  CError::throwRedirectTag("char.events", "error_cant_die_yet");
}

$smarty = new CantrSmarty();
$smarty->displayLang("page.death_old_age.tpl", $lang_abr);
