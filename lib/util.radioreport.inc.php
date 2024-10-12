<?php

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {

  $db = Db::get();
  echo "<strong>Creating radio report.</strong><br /><br />";

  $message = "Report requested by {$playerInfo->getFullNameWithId()}<br /><br />";
  $stm = $db->prepare("SELECT * FROM events WHERE type = :type ORDER BY day, hour, minute");
  $stm->bindInt("type", _EVENT_RADIO_TRANSMIT_ACTOR);
  $stm->execute();
  foreach ($stm->fetchAll() as $event) {
    $line = "";
    $eventData = Parser::rulesToArray($event->parameters, " =");
    if (array_key_exists("ACTOR", $eventData)) {
      $stm = $db->prepare("SELECT id, name, player FROM chars WHERE id = :charId LIMIT 1");
      $stm->bindInt("charId", $eventData["ACTOR"]);
      $stm->execute();
      $charData = $stm->fetchObject();
      $message .= $event->day . "-" . $event->hour . "." . $event->minute . "|";
      $message .= "[<a href=\"index.php?page=infoplayer&player_id=" . $charData->player . "\">" . $charData->player . "</a>]|";
      $message .= "(" . $charData->id . ", " . $charData->name . ")|";
      $message .= "Freq:" . $eventData["FREQ"] . "|";
      $message .= urldecode($eventData["MESSAGE"]) . "<br /><br />";
    }
  }
  echo $message;
  
  $env = Request::getInstance()->getEnvironment();
  $mailService = new MailService("Players Department", $GLOBALS['emailPlayers']);
  $mailService->send($GLOBALS['emailPlayers'], $env->getFullName() ." Radio report", $message);

  echo "<strong>Done. This report has been sent to the Players Department mailing list.</strong>";

} else {
  CError::throwRedirect("player", "You are not authorized to read the players info");
}

?>

<div style="width: 100%; position: fixed; bottom: 10px; left: 10px;">
   <a href="index.php?page=player">
   <img src="<?php echo _IMAGES; ?>/button_back2.gif" title="Back"></a>
</div>
