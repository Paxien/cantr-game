<?php

$after = $_REQUEST['after'];

$env = Request::getInstance()->getEnvironment();

if (!$env->introExists()) {
  CError::throwRedirect("player", "Intro server doesn't exist");
}

$introDb = Db::get("intro");

$stm = $introDb->prepareWithIntList("SELECT id FROM players WHERE id = :player AND status IN (:statuses)",
  [
    "statuses" => [
      PlayerConstants::PENDING,
      PlayerConstants::APPROVED,
      PlayerConstants::ACTIVE,
    ],
  ]);
$stm->bindInt("player", $player);
$stm->execute();
if (!$stm->exists()) {
  CError::throwRedirect("player", "You have no active account on intro server");
}

// add onetime password
$codeLength = 8;
$easyCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $codeLength);
$easyHash = SecurityUtil::generatePasswordHash($easyCode);

$stm = $introDb->prepare("INSERT INTO `onetime_passwords` (player, password) VALUES (:player, :hash)");
$stm->bindInt("player", $player);
$stm->bindStr("hash", $easyHash);
$stm->execute();


redirect("login", [
  "id" => $player,
  "password" => $easyCode,
  "data" => "yes",
  "onetime" => "yes",
  "from" => "main",
  "after" => urlencode($after),
], $env->getIntroSubdomainAddress());
