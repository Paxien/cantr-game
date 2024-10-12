<?php

$message = $_REQUEST['message'];

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::MESSAGE_TO_ALL_PLAYERS)) {
  $author = $playerInfo->getFullName();

  $datum = date("Y-m-d");

  $db = Db::get();
  $stm = $db->prepare("INSERT INTO messages (content, date, author) VALUES (:message, :date, :author)");
  $stm->bindStr("message", $message);
  $stm->bindStr("date", $datum);
  $stm->bindStr("author", $author);
  $stm->execute();
}
    
redirect("player");

