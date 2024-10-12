<?php

$playerInfo = Request::getInstance()->getPlayer();
$accessToAnyRelatedTool =
  $playerInfo->hasAccessTo(AccessConstants::MANAGE_RAW_MATERIAL_LOCATIONS) ||
  $playerInfo->hasAccessTo(AccessConstants::ALTER_ANIMAL_PLACEMENT) ||
  $playerInfo->hasAccessTo(AccessConstants::ALTER_LOCATIONS);
if (!$accessToAnyRelatedTool) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$db = Db::get();
$smarty = new CantrSmarty();

$stm = $db->query("SELECT id, name FROM regions");
foreach ($stm->fetchAll() as $region_info) {
  $regions[] = $region_info;
}

$stm = $db->query("SELECT * FROM islands");
foreach ($stm->fetchAll() as $island_info) {
  $islands[] = $island_info;
}

$smarty->assign("regions", $regions);
$smarty->assign("showempty", $showempty);
$smarty->assign("islands", $islands);
$smarty->assign("showlang", $showlang);
if ($showlang) {
  $stm = $db->query("SELECT id, name FROM languages");
  foreach ($stm->fetchAll() as $language_info) {
    $langs [] = $language_info;
  }
  $smarty->assign("langs", $langs);
}

$smarty->displayLang("form.selectregion.tpl", $lang_abr);
