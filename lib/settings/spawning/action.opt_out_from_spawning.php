<?php

$optOut = !empty($_REQUEST['opt_out_from_spawning']);

$charSettings = new CharacterSettings($char, Db::get());
$charSettings->updateOptOutFromSpawningSystem($optOut);
