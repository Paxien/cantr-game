<?php

if (!empty($s)) {
  redirect("player", ["noformat" => 1]);
  exit();
}

$smarty = new CantrSmarty();
$smarty->displayLang("form.login.tpl", $lang_abr);
