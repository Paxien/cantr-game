<?php

$email = $_REQUEST['email'];

$db = Db::get();

$stm = $db->prepareWithIntList("SELECT p.id, p.username
  FROM players p
    LEFT JOIN chars c ON c.player = p.id
  WHERE email = :email AND p.status IN (:statuses)
    GROUP BY p.id ORDER BY COUNT(*) DESC LIMIT 1", [
  "statuses" => [PlayerConstants::UNSUBSCRIBED, PlayerConstants::IDLEDOUT],
]);
$stm->bindStr("email", $email);

$stm->execute();
$playerInfo = $stm->fetchObject();
if (!$playerInfo) {
  CError::throwRedirectTag("adduser", "error_no_inactive_account_for_email");
}

$codeLength = 8;
$easyCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $codeLength);
$easyHash = SecurityUtil::generatePasswordHash($easyCode);

$stm = $db->prepare("INSERT INTO onetime_passwords (player, password) VALUES (:player, :password)");
$stm->bindInt("player", $playerInfo->id);
$stm->bindStr("password", $easyHash);
$stm->execute();

$request = Request::getInstance();

$mailTitle = TagBuilder::forTag("mail_title_reactivation_email")->build()->interpret();
$mailContent = TagBuilder::forText("<CANTR REPLACE NAME=mail_reactivation_email " .
  "EMAIL=" . urlencode($email) .
  " ID=" . $playerInfo->id .
  " USERNAME=" . urlencode($playerInfo->username) .
  " LOGIN_LINK=" . urlencode($request->getDomainAddress() . "/index.php?page=login&id=" .
    $playerInfo->id . "&password=" . urlencode($easyCode) . "&onetime=1&data=yes&from=reactivation_email") .
  " PASSREMINDER_LINK=" . urlencode($request->getDomainAddress() . "/index.php?page=passreminder") .
  ">")->build()->interpret();

$mailService = new MailService("Cantr Accounts", $GLOBALS['emailPlayers']);
$mailService->send(
  $email,
  $mailTitle,
  $mailContent
);

$smarty = new CantrSmarty();
$smarty->displayLang("page.send_reactivation_email.tpl", $lang_abr);

$emailReactivationStat = new Statistic("email_reactivation", $db);
$emailReactivationStat->store(mb_substr($email, 0, 31));
