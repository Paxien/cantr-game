<?php

// SANITIZE INPUT
$player_id = HTTPContext::getInteger('player_id');

$smarty = new CantrSmarty;
$smarty->assign ("player_id", $player_id);

if ($player_id) { // unsubscription of sb else's account in admin mode
  $playerInfo = Player::loadById($player_id);
  $smarty->assign ("firstname", $playerInfo->getFirstName());
  $smarty->assign ("lastname", $playerInfo->getLastName());
  $smarty->assign ("email", $playerInfo->getEmail());
  $smarty->displayLang ("form.unsubscribe.tpl", $lang_abr);
} else { // if unsubbing own account
  // unsub lock code
  $db = Db::get();
  $stm = $db->prepare("SELECT unsub_countdown FROM players WHERE id = :playerId");
  $stm->bindInt("playerId", $player);
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
  
  $survey = new Survey(Db::get());
  $surveySent = HTTPContext::getString("survey_sent", null);
  $postHolder = HTTPContext::getString("holder", null);
  if ($surveySent != null) { // submit survey to db if there is possibility of doing it
    $accepted = $survey->submitSurvey(_EXIT_SURVEY_S_ID, $player, $l, false) || ($postHolder != null);
  }
  /*
  DISPLAY
  */
  //if there is at least one answer from that player in db then show page with password field and ultimate removal button
  if (!$survey->isSurveyAvailable(_EXIT_SURVEY_S_ID, $player, $l, true) || $accepted) {
    $smarty->displayLang ("form.unsubscribe.tpl", $lang_abr);
  } else { // no survey was sent => show survey
  
  $survey->loadSurvey(_EXIT_SURVEY_S_ID);
  $survey->form_action = "index.php?page=unsubscribe";
  $surveySmarty = new CantrSmarty();
  $surveySmarty->assign("survey", $survey->showSurvey());
  $surveySmarty->assign("show_back", true);
  $surveySmarty->assign("back_destination", "player");
  
  $surveySmarty->displayLang ("page.show_survey.tpl", $lang_abr);
  
  }
}
