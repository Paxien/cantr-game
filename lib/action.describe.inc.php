<?php

// SANITIZE INPUT
$id = HTTPContext::getInteger('id', null);
$publicDesc = $_REQUEST['publicDesc'];
$next = $_REQUEST['next']; // next page to redirect to

if (!$next) {
  $next = "char.events";
}

try {
  $charFor = Character::loadById($id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag($next, "error_no_valid_id");
}

  $auth = 1;// 1 = normal player, 0 = url modding little wannabe hacker, 2 = PD member, -1 = not authorized to describe their own

  if ($player == $charFor->getPlayer()) {
    //Character belongs to the player
    $forbidden = Limitations::getLims($player, Limitations::TYPE_PLAYER_CHARDESCRIPTION);
    if ($forbidden > 0) {
      $auth = -1;//not authorized, otherwise 1 = normal
    }
  } else {
    //The target is played by someone else
    $playerInfo = Request::getInstance()->getPlayer();
    $isPD = $playerInfo->hasAccessTo(AccessConstants::VIEW_PLAYERS);
    if (!$isPD) {
      $auth = 0;//not authorized
    } else {
      $auth = 2;//PD
    }
  }

  if ($auth > 0) {
    $publicDesc = strip_tags($publicDesc); //removes HTML since it wouldn't work anyway

    if (!Descriptions::isDescriptionAllowed(Descriptions::TYPE_CHAR, $publicDesc)) {
        CError::throwRedirectTag($next, "error_desc_too_long");
    }

    $oldDesc = $charFor->getCustomDesc();
    
    if ($publicDesc != $oldDesc) {
      //It should only update it if it has actually been changed
      $charFor->setCustomDesc($publicDesc);
      $charFor->saveInDb();
      
      $umessage =  "Player $player changed description of char $id ";
      if ($auth == 2) {
        $umessage .= "(of player ". $charFor->getPlayer() .") ";//if describing someone else's character, PD ability
      }
      $umessage .= "to: '$publicDesc'\n (From: '$oldDesc')";
      Report::saveInDb("desc_changes", $umessage, $GLOBALS['emailPlayers'], "Custom Description Changes");
    }
  } elseif ($auth < 0) {
    // not allowed to describe your own charries
    CError::throwRedirectTag($next, "error_describe_own");
  } else {
    $umessage = "Apparent exploit attempt: Player $player attempted to change description of char $id (of player ". $charFor->getPlayer() .") to: $publicDesc";
    Report::saveInDb("desc_changes", $umessage, $GLOBALS['emailPlayers'], "Custom Description Changes");
    CError::throwRedirect($next, "Nooooooooooooooooooooo!");
  }

redirect($next);
