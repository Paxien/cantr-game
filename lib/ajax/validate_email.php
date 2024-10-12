<?php

$email = $_REQUEST['email'];

$db = Db::get();

$stm = $db->prepareWithIntList("SELECT COUNT(*) FROM `players` WHERE `email` = :email AND `status` IN (:statuses)",
  [
    "statuses" => [PlayerConstants::PENDING, PlayerConstants::APPROVED, PlayerConstants::ACTIVE, PlayerConstants::LOCKED],
  ]);
$stm->bindStr("email", $email);
$existingPlayerEmails = $stm->executeScalar();

$stm = $db->prepareWithIntList("SELECT COUNT(*) FROM `players` WHERE `email` = :email AND `status` IN (:statuses)",
  [
    "statuses" => [PlayerConstants::UNSUBSCRIBED, PlayerConstants::IDLEDOUT],
  ]);
$stm->bindStr("email", $email);
$existingInactivePlayerEmails = $stm->executeScalar();

echo json_encode([
  "activeAccount" => $existingPlayerEmails > 0,
  "inactiveAccount" => $existingInactivePlayerEmails > 0,
]);
