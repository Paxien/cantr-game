<?php

include_once "../lib/stddef.inc.php";

$sentPassword = $_POST['password'];
$email = $_POST["email"];

if ($sentPassword !== "cC1HQ2Ch7A") {
  echo json_encode(["e" => "incorrect password", "valid" => false]);
  die();
}

$db = Db::get();

$stm = $db->prepare("SELECT COUNT(*) FROM `players` WHERE `email` = :email");
$matchingEmails = $stm->executeScalar(["email" => $email]);

$stm = $db->prepare("SELECT COUNT(*) FROM `removed_players` WHERE `email` = :email");
$matchingEmails += $stm->executeScalar(["email" => $email]);

echo json_encode(["valid" => !!$matchingEmails]);
