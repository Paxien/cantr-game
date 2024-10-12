<?php

$large_box = HTTPContext::getRawString("large_box");

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::MESSAGE_TO_ALL_PLAYERS)) {
  $smarty = new CantrSmarty();
  $smarty->assign ("to", 0);
  $smarty->assign ("large_box", $large_box);

  $db = Db::get();
  $stm = $db->query("SELECT * FROM messages WHERE language=1 ORDER BY date");
  foreach ($stm->fetchAll() as $message_info) {
    $messages [] = $message_info;
  }
  $smarty->assign ("messages", $messages);
  
  $smarty->displayLang ("form.message_all_players.tpl", $lang_abr);
}
