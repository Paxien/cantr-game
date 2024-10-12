<?php

$playerId = HTTPContext::getInteger('id');
$timestamp = HTTPContext::getInteger('timestamp');
$hash = HTTPContext::getRawString('hash');

$emailAddressManager = new InvalidEmailAddressManager($playerId);

$smarty = new CantrSmarty();
if ($emailAddressManager->canValidateEmail($hash, $timestamp)) {
  $emailAddressManager->makeEmailValid();
  $smarty->assign("success", true);
} else {
  $smarty->assign("success", false);
}

$smarty->displayLang("page.activate_invalid_email.tpl", $lang_abr);
