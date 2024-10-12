<?php

$charSettings = new CharacterSettings($char, Db::get());

$smarty->assign("isOptOutFromSpawningSystem", $charSettings->isOptOutFromSpawningSystem());
