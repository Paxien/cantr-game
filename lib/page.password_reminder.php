<?php

$email = $_REQUEST['email'];

$env = Request::getInstance()->getEnvironment();

$mailService = new MailService("Cantr Accounts", $GLOBALS['emailSupport']);

$db = Db::get();

$smarty = new CantrSmarty;


if ($email) {

  $stm = $db->prepare("SELECT p.id, p.password, p.status IN (:approved, :reactivated) AS active FROM players p
    LEFT JOIN chars c ON c.player = p.id WHERE p.email = :email GROUP BY p.id ORDER BY active DESC, COUNT(c.id) DESC, p.id");
  $stm->bindInt("approved", PlayerConstants::APPROVED);
  $stm->bindInt("reactivated", PlayerConstants::ACTIVE);
  $stm->bindStr("email", $email);
  $stm->execute();
  $playerIds = $stm->fetchScalars();

  if (count($playerIds) > 0) {
    $title = TagBuilder::forTag("password_request_sent_title_mail")->build()->interpret();

    $forgottenPlayer = Player::loadById($playerIds[0]); // load possibly active account having most characters
    // it can look ugly, but let's try do something harder to guess
    $confirmationHash = SecurityUtil::createPlayerConfirmationHash($forgottenPlayer);
    $domainAddress = Request::getInstance()->getDomainAddress();
    $message = new Tag("<CANTR REPLACE NAME=password_request_sent_mail ID=" . $forgottenPlayer->getId() .
      " HASH=$confirmationHash ADDRESS=" . urlencode($domainAddress) . ">");
    $message = $message->interpret();

    $mailService->send($email, $title, $message);

    $smarty->assign("show", "hash_sent");
  } else {
    CError::throwRedirectTag("passreminder", "error_wrong_mail_address");
  }
} elseif ($_GET['h']) {
  $passwordSent = false;
  $forgottenPlayerId = HTTPContext::getInteger('id');
  $confirmationHash = HTTPContext::getRawString('h');
  if (!empty($forgottenPlayerId) && !empty($confirmationHash)) {
    $stm = $db->prepare("SELECT COUNT(*) FROM players WHERE id = :id");
    $stm->bindInt("id", $forgottenPlayerId);
    $playerExists = $stm->executeScalar();

    if ($playerExists) {
      $forgottenPlayer = Player::loadById($forgottenPlayerId);
      $correctConfirmationHash = SecurityUtil::createPlayerConfirmationHash($forgottenPlayer);
      if ($confirmationHash == $correctConfirmationHash) {

        $newPassword = SecurityUtil::generateNewRandomPassword();

        $newPasswordHash = SecurityUtil::generatePasswordHash($newPassword);
        $stm = $db->prepare("UPDATE players SET password = :passwordHash WHERE id = :id");
        $stm->bindStr("passwordHash", $newPasswordHash);
        $stm->bindInt("id", $forgottenPlayer->getId());
        $stm->execute();

        $title = TagBuilder::forTag("password_sent_title_mail")->build()->interpret();

        $domainAddress = Request::getInstance()->getDomainAddress();
        $message = new Tag("<CANTR REPLACE NAME=password_sent_mail ID=" . $forgottenPlayer->getId() .
          " NEWPASSWORD=" . urlencode($newPassword) . " ADDRESS=" . urlencode($domainAddress) . ">");
        $message = $message->interpret();
        $mailService->send($forgottenPlayer->getEmail(), $title, $message);
        $smarty->assign("show", "password_sent");
        $passwordSent = true;

        $reminderStats = new Statistic("password_reminder", $db);
        $reminderStats->store($forgottenPlayer->getStatus(), $forgottenPlayer->getId());
      }
    }
  }
  if (!$passwordSent) {
    CError::throwRedirectTag("passreminder", "error_broken_link");
  }
}

$smarty->displayLang('page.password_reminder.tpl', $lang_abr);
