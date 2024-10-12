<?php

$animalId = HTTPContext::getInteger('vehicle'); 

$smarty = new CantrSmarty();
$smarty->assign("animalId", $animalId);

$smarty->displayLang("animals/steed/page.unsaddling.tpl", $lang_abr);
