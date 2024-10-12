<?php

include_once("page.settings.profile.inc.php");
include_once("page.settings.charlist.inc.php");
include_once("page.settings.interface.inc.php");

$categories = array('profile' => 'SettingsProfile', 'charlist' => 'SettingsCharlist', 'interface' => 'SettingsInterface');

$category = $_REQUEST['category'];

// default state
if (!$category) {
  $category = 'profile';
}

$settingsObj = new $categories[$category];

$barSmarty = new CantrSmarty;
$barSmarty->assign("categories", $categories);
$barSmarty->assign("curr_cat", $category);
$barSmarty->assign("catLink", "index.php?page=settings&category=");

$barSmarty->displayLang("template.link.categories.tpl", $lang_abr);

$smarty = $settingsObj->getSmarty();

$smarty->displayLang($settingsObj->template_name, $lang_abr);
