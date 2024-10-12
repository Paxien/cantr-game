<?php
$_COLUMN_SIZE = 15;
$selectedtab = HTTPContext::getInteger('selectedtab');
$resetfilters = $_REQUEST['resetfilters'];

$db = Db::get();
// Smarty init
$smarty = new CantrSmarty;

switch ($selectedtab) {
  //events tab
  case 0:
    include 'deactive_events/page.charsettings.deactive.inc.php';
    break;
  case 1:
    if (isset($resetfilters)) {
      $stm = $db->prepare("DELETE FROM settings_chars WHERE person = :charId");
      $stm->bindInt("charId", $char->getId());
      $stm->execute();
      $stm = $db->prepare("INSERT INTO settings_chars(`type`, `person`, `data`)
        SELECT `type`, :charId, `data` from settings_chars where person = 0");
      $stm->bindInt("charId", $char->getId());
      $stm->execute();
    }
    include 'filters/page.charsettings.filters.inc.php';
    break;
  case 2:
    include 'other/page.charsettings.other.inc.php';
    break;
  case 3:
    include 'spawning/page.opt_out_from_spawning.php';
    break;
}

$smarty->assign("_COLUMN_SIZE", $_COLUMN_SIZE);
$smarty->assign("selectedtab", $selectedtab);
$smarty->displayLang("charsettings/page.charsettings.tpl", $lang_abr);
