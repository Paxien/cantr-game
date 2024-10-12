<?php

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->getStatus() == PlayerConstants::PENDING) {
  CError::throwRedirectTag("player", "error_create_character_account_pending");
}

$smarty = new CantrSmarty;
$db = Db::get();
$stm = $db->prepare("SELECT l.id, l.name, t.content
  FROM languages l INNER JOIN texts t ON t.name = CONCAT('lang_', l.name) AND (t.language = :language  or t.language = 1)
  WHERE l.spawning_allowed = 1 ORDER BY id");
$stm->bindInt("language", $l);
$stm->execute();
foreach ($stm->fetchAll() as $lang_info) {
  $languages [$lang_info->id] = $lang_info->content;
}

$playerId = $playerInfo->getId();

$env = Request::getInstance()->getEnvironment();
if ($env->introExists()) {
  $dbConnectIntro = Db::get("intro");
  $stm = $dbConnectIntro->prepareWithIntList("SELECT COUNT(*) as number FROM chars WHERE player = :playerId AND status IN (:statuses)",
  ["statuses" => [
    CharacterConstants::CHAR_PENDING,
    CharacterConstants::CHAR_ACTIVE,
  ]]);
  $stm->bindInt("playerId", $playerId);
  $canCreateOnGenesis = $stm->executeScalar() == 0;
} else {
  $canCreateOnGenesis = false;
}

$smarty->assign ("ownlanguage", $l);
$smarty->assign ("languages", $languages);
$smarty->assign ("canCreateOnGenesis", $canCreateOnGenesis);

$smarty->displayLang ("form.addchar.tpl", $lang_abr);
