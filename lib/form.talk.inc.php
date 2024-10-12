<?php

$smarty = new CantrSmarty;
$smarty->assign ("to", $to);
$smarty->assign ("large_box", $large_box);

$smarty->displayLang ("form.talk.tpl", $lang_abr);
