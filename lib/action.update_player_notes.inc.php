<?php

// SANITIZE INPUT
$player_id = HTTPContext::getInteger('player_id');
$player_notes = $_REQUEST['player_notes'];

$requestData = Request::getInstance();
$adminPlayer = $requestData->getPlayer();

if (!$adminPlayer->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
  CError::throwRedirect("infoplayer&player_id=$player_id",
    "You are not authorized to change player's notes");
}

if (isset($player_notes)) {
  $db = Db::get();
  $stm = $db->prepare("UPDATE players SET notes = :notes WHERE id = :playerId");
  $stm->bindStr("notes", urlencode($player_notes));
  $stm->bindInt("playerId", $player_id);
  $stm->execute();
}

redirect("infoplayer", ["player_id" => $player_id]);
