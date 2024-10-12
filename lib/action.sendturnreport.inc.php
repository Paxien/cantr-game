<?php

// SANITIZE INPUT
$turnnumber = HTTPContext::getInteger('turnnumber');

$playerInfo = Request::getInstance()->getPlayer();

$dailyReport = new PlayerDailyReport($playerInfo, $turnnumber);

try {
  $dailyReport->sendMail();
} catch (IllegalStateException $e) {
  CError::throwRedirect("player", "You have requested a report for this turn already.");
}

redirect("player");
