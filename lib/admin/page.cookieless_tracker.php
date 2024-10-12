<?php

$admin = Request::getInstance()->getPlayer();

if (!$admin->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
  CError::throwRedirect("player", "you are not allowed to see this page");
}

$db = Db::get();

$stm = $db->query("SELECT group_id, player, count, date FROM multi_logins");

$groups = [];
foreach ($stm->fetchAll() as $row) {
  if (empty($groups[$row->group_id])) {
    $groups[$row->group_id] = [];
  }
  $groups[$row->group_id][] = $row;
}

$nodes = [];
foreach ($groups as $groupId => $players) {
  foreach ($players as $plr) {
    $nodes[$plr->player] = true;
  }
}

$smarty = new CantrSmarty();
$smarty->assign("groups", $groups);
$smarty->assign("nodes", $nodes);
$smarty->displayLang("admin/page.cookieless_tracker.tpl", $lang_abr);
