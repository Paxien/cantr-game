<?php

$data = $_REQUEST['data'];

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::MANAGE_SURVEYS)) {
  
  $survey = new Survey(Db::get());
  $accepted = true;
  if ($data) { // action - create survey
  
    $mal = $survey->createSurvey();
    if ($mal !== true) {
      echo $mal;
      $accepted = false;
    }
    else {
      redirect("surveylist");
    }
  }
  if (!$data || $accepted == false) {
  
    $langs = array();
    $langs[0] = "All";

    foreach (LanguageConstants::$LANGUAGE as $languageId => $language) {
      $langs[$languageId] = $language['original_name'];
    }

    $smarty = new CantrSmarty();
    $smarty->assign("langs", $langs);
    $smarty->displayLang("page.create_survey.tpl", $lang_abr);
  }
}
