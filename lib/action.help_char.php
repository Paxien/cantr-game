<?php

// SANITIZE INPUT
$ocharId = HTTPContext::getInteger('ocharacter');

if ($char->isBusy()) {
  CError::throwRedirectTag("char.events", "error_cant_help_already_busy");
}

try {
  $otherChar = Character::loadById($ocharId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if (!$otherChar->isBusy()) {
  CError::throwRedirectTag("char.events", "error_nothing_to_help");
}

if (!$char->isNearTo($otherChar)) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if ($otherChar->getProject() > 0) { // we help in normal project
  redirect("joinproject", ["project" => $otherChar->getProject()]);
} else { // we help in dragging
  redirect("helpdrag", ["ocharacter" => $otherChar->getId()]);
}
exit;
