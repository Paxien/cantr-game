<?php

function syncPlayerOnIntro(Db $introDb, Db $db, $playerId, $mainDbName)
{
  // if a player started the game when intro server was active then can already have an account, otherwise they don't
  $stm = $introDb->prepare("SELECT COUNT(*) FROM players WHERE id = :playerId");
  $stm->bindInt("playerId", $playerId);
  $alreadyExists = $stm->executeScalar();
  if (!$alreadyExists) {
    $stm = $introDb->prepare("INSERT INTO `players` SELECT * FROM $mainDbName.players WHERE id = :playerId");
    $stm->bindInt("playerId", $playerId);
    $stm->execute();
  }

  // set the same password and reactivate account if needed
  $stm = $db->prepare("SELECT password FROM players WHERE id = :playerId");
  $stm->bindInt("playerId", $playerId);
  $mainPassword = $stm->executeScalar();
  $stm = $introDb->prepare("UPDATE players SET status = :status,
      password = :password, approval = 0 WHERE id = :playerId");
  $stm->bindInt("status", PlayerConstants::ACTIVE);
  $stm->bindStr("password", $mainPassword);
  $stm->bindInt("playerId", $playerId);
  $stm->execute();
}


$confirmed = !empty($_REQUEST['confirmed']);
$genesis = HTTPContext::getInteger('genesis');
$name = HTTPContext::getStripped('name');
$sex = $_REQUEST['sex'];
$charlanguage = HTTPContext::getInteger('charlanguage');

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->getStatus() == PlayerConstants::PENDING) {
  CError::throwRedirectTag("player", "error_create_character_account_pending");
}

if ($genesis != 1 && Limitations::getLims($player, Limitations::TYPE_NEW_CHARACTERS) > 0) {
  CError::throwRedirectTag("player", "error_disallowed_new_characters");
}

if ($genesis == 1) {
  $db = Db::get();
  $introDb = Db::get("intro");
  $env = Request::getInstance()->getEnvironment();
  $mainDbName = $env->getDbNameFor($env->getName());
  $playerId = $playerInfo->getId();
  syncPlayerOnIntro($introDb, $db, $playerId, $mainDbName);

  // Create character on Intro DB
  $introPlayerInfo = Player::loadById($playerInfo->getId(), $introDb);
  $charCreator = CharacterCreator::forPlayer($introPlayerInfo, $introDb);
} else {
  $db = Db::get();
  $charCreator = CharacterCreator::forPlayer($playerInfo, $db);
}

if (!$confirmed) {
  if ($charCreator->validate($name, $sex, $charlanguage)) {
    $smarty = new CantrSmarty();
    $smarty->assign("confirmation", true);
    $smarty->assign("name", $name);
    $smarty->assign("sex", $sex);
    $smarty->assign("charlanguage", $charlanguage);
    $smarty->assign("genesis", $genesis);

    $smarty->displayLang("form.addchar.tpl", $lang_abr);
  } else {
    CError::throwRedirectTag("addchar", $charCreator->getError());
  }
} else { // if confirmed
  if ($charCreator->validate($name, $sex, $charlanguage)) {
    if (!$charCreator->create($name, $sex, $charlanguage)) {
      CError::throwRedirectTag("addchar", $charCreator->getError());
    }
    redirect("player");
  } else {
    CError::throwRedirectTag("addchar", $charCreator->getError());
  }
}