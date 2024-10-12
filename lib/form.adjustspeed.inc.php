<?php

// SANITIZE INPUT
$character = HTTPContext::getInteger('character');

try {
  $travel = Travel::loadByParticipant(Character::loadById($character));
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_no_adjust_when_not_travelling");
} catch (Exception $e) {
  CError::throwRedirect("char.Events", "Unknown exception");
}

$currentSpeed = $travel->getWantedSpeed() / $travel->getMaxSpeed() * 100;

$smarty = new CantrSmarty;

$smarty->assign ("CURRSPEED", $currentSpeed);
$smarty->displayLang ("form.adjustspeed.tpl", $lang_abr);
