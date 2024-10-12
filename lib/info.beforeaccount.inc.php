<?php

  $smarty = new CantrSmarty;
  $smarty->assign('referrer', $referrer);
  $smarty->displayLang ("info.beforeaccount.tpl", $lang_abr); 

$stats = new Statistic("newplayer", Db::get());
$stats->update("onrules", 1);
