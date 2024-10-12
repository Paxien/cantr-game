<?php

$desc_id = HTTPContext::getInteger('desc_id');
$return = HTTPContext::getInteger('return');

$reportedText = Descriptions::getDescriptionById($desc_id);

$smarty = new CantrSmarty;
$smarty->assign("reported_text", $reportedText);
$smarty->assign("desc_id", $desc_id);
$smarty->assign("return", $return);
$smarty->displayLang("page.report_description.tpl", $lang_abr);
