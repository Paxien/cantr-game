<?php

$changed = HTTPContext::getRawString("changed");
$enabled = HTTPContext::getRawString("enabled");
$s_language = HTTPContext::getRawString("s_language");
$player_ids = HTTPContext::getRawString("surv_player_ids");
$survey_id = HTTPContext::getInteger('survey_id');
$survey = new Survey(Db::get());

if ($changed) {
  $survey->setSurveyData($survey_id, $enabled, $s_language, $player_ids);
}

$smarty = new CantrSmarty();
$smarty->assign("data", $survey->getSurveyData($survey_id));

$lang_list = array();
$lang_list[0] = 'All';
$db = Db::get();
$stm = $db->query("SELECT id, name FROM languages ORDER BY id ASC");
foreach ($stm->fetchAll() as $langs) {
  $lang_list[$langs->id] = $langs->name;
}
$smarty->assign("lang_list", $lang_list);

$smarty->displayLang("page.survey_manage.tpl", $lang_abr);
