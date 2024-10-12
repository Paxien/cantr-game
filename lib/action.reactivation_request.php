<?php

$playerInfo = Request::getInstance()->getPlayer();

if (!in_array($playerInfo->getStatus(), [PlayerConstants::UNSUBSCRIBED, PlayerConstants::IDLEDOUT])) {
  redirect("player");
}

// Get information about the player
$db = Db::get();

$user = new user();
$user->username = $playerInfo->getUserName();
$user->firstname = $playerInfo->getFirstName();
$user->lastname = $playerInfo->getLastName();
$user->email = $playerInfo->getEmail();
$user->age = $playerInfo->getBirthYear();
$user->ipinfo = $playerInfo->getLastLoginString();

$user->password = $playerInfo->getPasswordHash();
$user->password_retype = $playerInfo->getPasswordHash();
$user->language = $playerInfo->getLanguage();
$user->referrer = "";

$remaddr = $_SERVER['REMOTE_ADDR'];
$remhost = gethostbyaddr($remaddr);
$newPlayerMatchesReporting = new NewPlayerMatchesReporting($user, Db::get());
list($message, $CEIP) = $newPlayerMatchesReporting->findMatchesWithPlayersDatabase($playerInfo->getId(), $remaddr);

$matcher = new ForIntroPlayerMatching($user);
$matches = $matcher->findSuspiciousMatches();

if (count($matches) !== 0) { // found any suspicious match, so deactivate character creation, and send email to PD

  Limitations::addLim($playerInfo->getId(), Limitations::TYPE_NEW_CHARACTERS, Limitations::dhmstoc(9999, 0, 0, 0));

  $suspiciousMsg = TagBuilder::forText("<CANTR REPLACE NAME=mail_reactivation_suspicious_msg PLAYER_NAME=" . urlencode($user->firstname) . "%20" . urlencode($user->lastname) . " " . "PLAYER_ID=" . urlencode($playerInfo->getId()) . " " . "INFO=" . urlencode($message) . ">")->build()->interpret();
  $suspiciousTitle = TagBuilder::forTag("mail_reactivation_suspicious_title" . " " . "PLAYER_ID=" . urlencode($playerInfo->getId()))->build()->interpret();

  $mailService = new MailService("Cantr Players Department", $GLOBALS['emailProgramming']);
  $mailService->send($GLOBALS['emailPlayers'], $suspiciousTitle, $suspiciousMsg);

  $stm = $db->prepare("SELECT COUNT(*) FROM newplayers WHERE id = :playerId");
  $stm->bindInt("playerId", $playerInfo->getId());
  $reactivationRequestExists = $stm->executeScalar() > 0;
  if (!$reactivationRequestExists) {
    // Since character creation was disabled, add reactivation request to pending players
    $info = date("d/m/Y H:i") . " $remaddr ($remhost)";

    $stm = $db->prepare("INSERT INTO newplayers (`id`, `reference`, `ipinfo`, `research`, `comment`, `refplayer`, `type`)
    VALUES (:id, :reference, :ipinfo, :research, :comment, :refplayer, :type)");

    $stm->bindInt("id", $playerInfo->getId());
    $stm->bindStr("reference", "");
    $stm->bindStr("ipinfo", $info);
    $stm->bindStr("research", $message);
    $stm->bindInt("type", PlayersDeptConstants::NEWPLAYER_TYPE_REACTIVATION);
    $stm->bindStr("comment", "");
    $stm->bindStr("refplayer", "");
    $stm->execute();
  }
} else { // not found any matches, send email to PD 

  $unsuspiciousMsg = TagBuilder::forText("<CANTR REPLACE NAME=mail_reactivation_msg PLAYER_NAME=" . urlencode($user->firstname) . "%20" . urlencode($user->lastname) . " " . "PLAYER_ID=" . urlencode($playerInfo->getId()) . " " . "INFO=" . urlencode($message) . ">")->build()->interpret();

  $unsuspiciousTitle = TagBuilder::forTag("mail_reactivation_title PLAYER_ID=" . urlencode($playerInfo->getId()))
    ->language(LanguageConstants::ENGLISH)->build()->interpret();
  $mailService = new MailService("Cantr Players Department", $GLOBALS['emailProgramming']);
  $mailService->send($GLOBALS['emailPlayers'], $unsuspiciousTitle, $unsuspiciousMsg);
}

// reactivate account regardless of whether they're suspicious
$stm = $db->prepare("UPDATE players SET status = :active WHERE id = :playerId");
$stm->bindInt("active", PlayerConstants::ACTIVE);
$stm->bindInt("playerId", $playerInfo->getId());
$stm->execute();

$reactivationStats = new Statistic("reactivation", $db);
$reactivationStats->store("request", $playerInfo->getId());

redirect("reactivation");
