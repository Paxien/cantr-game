<?php

$player_total = 0;
$char_total = 0;
$spawn_total = 0;
$translated_total = 0;
$language_count = 0;
  
$language_data = array();

$db = Db::get();
$stm = $db->query("SELECT languages.* FROM languages ORDER BY id");
foreach ($stm->fetchAll() as $lang) {
  $language_count++;
  $language_data[$lang->id]['name'] = "<CANTR REPLACE NAME=lang_$lang->name>";
  $language_data[$lang->id]['spawn_allowed'] = $lang->spawning_allowed;
  $language_data[$lang->id]['density_spawn'] = $lang->use_density_spawning;
}
  
$stm = $db->prepare("SELECT players.language, count(*) AS count FROM players WHERE status < :status GROUP BY language");
$stm->bindInt("status", PlayerConstants::LOCKED);
$stm->execute();
foreach ($stm->fetchAll() as $players) {
  $language_data[$players->language]['players'] = $players->count;
  $player_total += $players->count;
}

$stm = $db->query("SELECT chars.language, count(*) AS count FROM chars WHERE status=1 GROUP BY language");
foreach ($stm->fetchAll() as $chars) {
  $language_data[$chars->language]['characters'] = $chars->count;
  $char_total += $chars->count;
}

$stm = $db->query("SELECT spawninglocations.language, count(*) AS count FROM spawninglocations GROUP BY language");
foreach ($stm->fetchAll() as $locs) {
  $language_data[$locs->language]['spawn_locations'] = $locs->count;
  $spawn_total += $locs->count;
}


$stm = $db->query("SELECT texts.language, count(*) AS count FROM texts WHERE language > 0 GROUP BY language ORDER BY language");
foreach ($stm->fetchAll() as $texts) {
  $language_data[$texts->language]['translated'] = $texts->count;
  $translated_total += $texts->count;
}

$base = $language_data[1]['translated']; // base = amount of english texts

$language_data['&#931']['players'] = $player_total;
$language_data['&#931']['name'] = "";
$language_data['&#931']['characters'] = $char_total;
$language_data['&#931']['spawn_locations'] = $spawn_total;
$language_data['&#931']['spawn_allowed'] = true;
$language_data['&#931']['density_spawn'] = false;
$language_data['&#931']['translated'] = $translated_total/$language_count;

$smarty = new CantrSmarty;

$smarty->assign("data", $language_data);
$smarty->assign("base", $base);
$smarty->displayLang ("statistics/info.statistics.languages.tpl", $lang_abr);
