<?php

$playerId = HTTPContext::getInteger('player_id');

$requestData = Request::getInstance();
$adminPlayer = $requestData->getPlayer();

if (!$adminPlayer->hasAccessTo(AccessConstants::ACCEPT_PLAYERS)) {
  CError::throwRedirect("player", "You are not authorized to see this page.");
}

try {
  $requestedPlayer = Player::loadById($playerId);

  echo json_encode([
    "id" => $requestedPlayer->getId(),
    "userName" => $requestedPlayer->getUserName(),
    "firstName" => $requestedPlayer->getFirstName(),
    "lastName" => $requestedPlayer->getLastName(),
    "email" => $requestedPlayer->getEmail(),
    "status" => $requestedPlayer->getStatus(),
  ]);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("player", "Player with specified ID doesn't exist");
}


