<?php


if (!$char->isNearDeath()) {
  CError::throwRedirectTag("char.events", "error_suicide_not_near_death");
}

$smarty = new CantrSmarty();
$smarty->displayLang("page.suicide.tpl", $lang_abr);
