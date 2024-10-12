<?php

// SANITIZE INPUT
$message = HTTPContext::getInteger('message');

$db = Db::get();
$stm = $db->prepare("INSERT INTO message_seen (player,message) VALUES (:playerId, :message)");
$stm->bindInt("playerId", $player);
$stm->bindInt("message", $message);
$stm->execute();

redirect("player");
