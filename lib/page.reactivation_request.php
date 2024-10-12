<?php

$playerInfo = Request::getInstance()->getPlayer();

if (!in_array($playerInfo->getStatus(), [PlayerConstants::UNSUBSCRIBED, PlayerConstants::IDLEDOUT])) {
  redirect("player");
} else {
  redirect("reactivation");
}
