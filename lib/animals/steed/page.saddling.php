<?php

$animalId = HTTPContext::getInteger('object_id');

$smarty = new CantrSmarty();
$smarty->assign("animalId", $animalId);

$smarty->displayLang("animals/steed/page.saddling.tpl", $lang_abr);

