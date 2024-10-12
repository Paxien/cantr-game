<?php

$noJavaScript = $_REQUEST['noJavaScript'];

$charInfo = new CharacterInfoView($char);
$charInfo->show();

if ($noJavaScript) {
  include "menu.build.inc.php";
} else {
  $backLink = "index.php?page=char.events";

  $smarty = new CantrSmarty();
  $smarty->assign("backLink", $backLink);
  $smarty->assign("character", $char->getId());
  $smarty->assign("l", $l);

  $smarty->displayLang("page.build_menu.tpl", $lang_abr);
}

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
