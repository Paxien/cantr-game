<?php

$currentPlayer = Request::getInstance()->getPlayer();

if ($currentPlayer->hasAccessTo(AccessConstants::MANAGE_SURVEYS)) {

  $get = HTTPContext::getString('survey_id', -1);

  $from_date = HTTPContext::getString('from_date', "");
  $to_date = HTTPContext::getString('to_date', "");

  $survey = new Survey(Db::get());
  if ($survey->resultExists($get)) {

    if (empty($from_date)) $from_date = "-60 days";
    if (empty($to_date)) $to_date = "now";

    $survey->setDateBounds($from_date, $to_date);

    $resultSmarty = new CantrSmarty();
    $resultSmarty->assign("result", $survey->showResult($get, 'index.php?page=surveylist'));
    $resultSmarty->assign("survey_id", $get);
    $resultSmarty->assign("from_date", $from_date);
    $resultSmarty->assign("to_date", $to_date);

    $resultSmarty->displayLang("page.survey_results.tpl", $lang_abr);
  }


  $smarty = new CantrSmarty();

  $smarty->assign("surveyList", $survey->showSurveysList());
  $smarty->displayLang("page.survey_list.tpl", $lang_abr);
}
