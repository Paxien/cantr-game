<?php

  // SANITIZE INPUT
  $player_id = HTTPContext::getInteger('player_id');
  $new_email = $_REQUEST['new_email'];

  $playerInfo = Request::getInstance()->getPlayer();
  if ($playerInfo->hasAccessTo(AccessConstants::ALTER_EMAIL)) {

    $victim = Player::loadById($player_id);
    $old_email = $victim->getEmail();

    if ($new_email) {
      $victim->setEmail($new_email);
      $victim->saveInDb();
    }

    $name = urlencode($victim->getFirstName());
    // message to user
    $message_tag = new tag;
    $message_tag->language = $victim->getLanguage();
    $message_tag->html = false;
    $message_tag->content = "<CANTR REPLACE NAME=email_changed_email_body FIRSTNAME=$name OLD_EMAIL=$old_email NEW_EMAIL=$new_email>";
    $message = $message_tag->interpret();

    $topic_tag = new tag;
    $topic_tag->language = $victim->getLanguage();
    $topic_tag->html = false;
    $topic_tag->content = "<CANTR REPLACE NAME=email_changed_email_title>";
    $topic = $topic_tag->interpret();

    $mailService = new MailService("Cantr Support", $GLOBALS['emailSupport']);
    $mailService->sendPlaintext("$old_email,$new_email", $topic, $message);

    $message_to_staff  = $playerInfo->getFullNameWithId() . " changed the email address for player " . $victim->getFullNameWithId(). ". ";
    $message_to_staff .= "Old email: $old_email, new email: $new_email";
    
    Report::saveInDb("playersearch", $message_to_staff, $GLOBALS['emailGAB'], "Players Database Searches Report");
    
    redirect("infoplayer", ["player_id" => $player_id]);

  } else {
    CError::throwRedirectTag('player', 'error_not_your_email');
  }

