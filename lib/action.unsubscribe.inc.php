<?php

// SANITIZE INPUT
$player_id = HTTPContext::getInteger('player_id');
$password = $_REQUEST['password'];


$db = Db::get();
$stm = $db->prepare("SELECT unsub_countdown FROM players WHERE id = :playerId");
$stm->bindInt("playerId", $player_id);
$useUnsubLock = $stm->executeScalar();
if (Limitations::getLims($player, Limitations::TYPE_PLAYER_UNSUB_LOCK) > 0) { // if unsub counter didn't come to 0 yet
  CError::throwRedirectTag("player", "error_cant_unsub_yet");
} elseif ($useUnsubLock && Limitations::getLims($player, Limitations::TYPE_PLAYER_UNSUB_ALLOW) == 0) {
  // if unsub counter came to 0 and it's still allowed to unsub
  Limitations::addLim($player, Limitations::TYPE_PLAYER_UNSUB_LOCK,
    Limitations::dhmstoc(PlayerConstants::UNSUB_LOCK_DAYS, 0, 0, 0));

  Limitations::addLim($player, Limitations::TYPE_PLAYER_UNSUB_ALLOW,
    Limitations::dhmstoc(PlayerConstants::UNSUB_ALLOW_DAYS, 0, 0, 0));

  CError::throwRedirectTag("player", "error_cant_unsub_yet");
}

if ($player == $player_id) {

  $playerInfo = Request::getInstance()->getPlayer();
  if (!SecurityUtil::verifyPassword($password, $playerInfo->getPasswordHash())) {
    CError::throwRedirectTag("player", "error_missing_password");
  }

  foreach ($playerInfo->getAliveCharacters() as $char) {
    $char->dieCharacter(CharacterConstants::CHAR_DEATH_UNSUB, 0, false);
    $char->saveInDb();
    Event::create(188, "ACTOR=" . $char->getId())->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
  }

  $playerInfo->setStatus(PlayerConstants::UNSUBSCRIBED);
  $stm = $db->prepare("DELETE FROM unreported_turns WHERE player = :playerId");
  $stm->bindInt("playerId", $player_id);
  $stm->execute();

  // exit survey visibility and anonymize the results
  $stm = $db->prepare("UPDATE survey_player_surveys SET submitted = :submitted, player_id = NULL
    WHERE player_id = :playerId AND s_id = :surveyId ORDER BY date DESC LIMIT 1");
  $stm->bindInt("submitted", 1);
  $stm->bindInt("playerId", $playerInfo->getId());
  $stm->bindInt("surveyId", _EXIT_SURVEY_S_ID);
  $stm->execute();
  $stm = $db->prepare("INSERT INTO survey_respondents (survey_id, player_id) VALUES (:surveyId, :playerId)");
  $stm->bindInt("surveyId", _EXIT_SURVEY_S_ID);
  $stm->bindInt("playerId", $playerInfo->getId());
  $stm->execute();

  $message = "Player {$playerInfo->getFullNameWithId()} unsubscribed from Cantr";
  Report::saveInPlayerReport($message);

  // LOG ACTION
  Report::saveInPcStatistics("punsubscribed", $playerInfo->getId());

  $mailText = TagBuilder::forText("<CANTR REPLACE NAME=mail_unsubscribe_text>")->build()->interpret();
  $mailService = new MailService("Cantr Accounts", $GLOBALS['emailSupport']);
  $mailService->send($playerInfo->getEmail(), "Cantr II ENDING", $mailText);

  Session::deleteSessionFromDatabase($s);
  Session::deleteCookie();
}

redirect("intro");
