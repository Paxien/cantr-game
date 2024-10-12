<?php

// resets unsub counter
$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->isUnsubCountdownEnabled()) {
  Limitations::delLims($player, Limitations::TYPE_PLAYER_UNSUB_LOCK);
  Limitations::delLims($player, Limitations::TYPE_PLAYER_UNSUB_ALLOW);
} else {
  CError::throwRedirectTag("player", "error_disabling_countdown");
}

redirect("player");
