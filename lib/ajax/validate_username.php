<?php

$username = $_REQUEST['username'];

$db = Db::get();

$stm = $db->prepare("SELECT COUNT(*) FROM `players` WHERE `username` = :username");
$stm->bindStr("username", $username);
$existingPlayerUsernames = $stm->executeScalar();

$allUsernameMatches = $existingPlayerUsernames;
echo json_encode($allUsernameMatches == 0);
