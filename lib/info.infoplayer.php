<?php

ini_set("display_errors", 1);
include_once("func.bit.inc.php");

$transferchar = HTTPContext::getInteger('transferchar');
$transferdest = HTTPContext::getInteger('transferdest');
$player_id = HTTPContext::getInteger('player_id');
$removewatch = HTTPContext::getRawString('removewatch');
$setwatch = HTTPContext::getRawString('setwatch');
$other_account = HTTPContext::getInteger('other_account');
$remove_other_account = HTTPContext::getInteger('remove_other_account');
$alter_credits = HTTPContext::getInteger('alter_credits');
$tou_change = HTTPContext::getInteger('tou_change');
$tou = HTTPContext::getInteger('tou');
$listips = HTTPContext::getRawString('listips');
$revive = HTTPContext::getRawString('revive');
$lock_radio = $_REQUEST['lock_radio'];
$newTutorCharLanguage = $_REQUEST['new_tutor_language'];
$newTutorCharSex = HTTPContext::getInteger('new_tutor_sex');
$travels = HTTPContext::getRawString('travels');
$username = HTTPContext::getRawString('username');
$listknownas = HTTPContext::getRawString('listknownas');
$listknows = HTTPContext::getRawString('listknows');
$alterdesc = HTTPContext::getRawString('alterdesc');

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}
$adminInfo = $playerInfo;

function getActionForStatus($playerStatus)
{
  if (in_array($playerStatus, [PlayerConstants::APPROVED, PlayerConstants::ACTIVE])) {
    return "Lock";
  }
  if ($playerStatus == PlayerConstants::LOCKED) {
    return "Unlock";
  }
  if (in_array($playerStatus, [PlayerConstants::UNSUBSCRIBED, PlayerConstants::IDLEDOUT])) {
    return "Revive";
  }
  return "";
}

/*****************************************************************************
 * General info
 ****************************************************************************/
$gameDate = GameDate::NOW();
$env = Request::getInstance()->getEnvironment();
$db = Db::get();
$message = "";

/*****************************************************************************
 * Actions (Note that they are elseif'ed because only one action is possible
 * at the same time)
 ****************************************************************************/

// Character transfers


if ($lock_radio == "yes") {
  Limitations::addLim($player_id, Limitations::TYPE_PLAYER_RADIO_USAGE, Limitations::dhmstoc(9999, 0, 0, 0));
} elseif ($lock_radio == "no") {
  Limitations::delLims($player_id, Limitations::TYPE_PLAYER_RADIO_USAGE);
} elseif ($transferchar > 0 && $transferdest > 0) {
  $stm = $db->prepare("UPDATE chars SET player = :playerId WHERE id = :charId");
  $stm->bindInt("playerId", $transferdest);
  $stm->bindInt("charId", $transferchar);
  $stm->execute();
  Report::saveInPlayerReport($adminInfo->getFullNameWithId() . " transferred character "
    . $transferchar . " into account " . $transferdest . ".");
} elseif ($revive) {
  $stm = $db->prepare("UPDATE players SET status = :status, lastdate = :lastDay,
                   lasttime = :lastHour WHERE id = :playerId");
  $stm->bindInt("status", PlayerConstants::ACTIVE);
  $stm->bindInt("lastDay", $gameDate->getDay());
  $stm->bindInt("lastHour", $gameDate->getHour());
  $stm->bindInt("playerId", $player_id);
  $stm->execute();

  $stm = $db->prepare("SELECT * FROM players WHERE id = :id LIMIT 1");
  $stm->bindInt("id", $player_id);
  $stm->execute();
  $player_info = $stm->fetchObject();
  Report::saveInPlayerReport($adminInfo->getFullNameWithId() .
    " revived player $player_info->firstname $player_info->lastname ($player_info->id, $player_info->email)");
} elseif ($removewatch) {
  $stm = $db->prepare("DELETE FROM watches WHERE player = :playerId AND email = :email");
  $stm->bindInt("playerId", $player_id);
  $stm->bindStr("email", $removewatch);
  $stm->execute();
} elseif ($setwatch) {
  $stm = $db->prepare("INSERT INTO watches (player, email) VALUES (:playerId, :email)");
  $stm->bindInt("playerId", $player_id);
  $stm->bindStr("email", $setwatch);
  $stm->execute();
} elseif ($other_account) {
  $stm = $db->prepare("INSERT INTO same_player (player1, player2, admin)
    VALUES (:playerId, :otherPlayerId, :adminId)");
  $stm->bindInt("playerId", $player_id);
  $stm->bindInt("otherPlayerId", $other_account);
  $stm->bindInt("adminId", $adminInfo->getId());
  $stm->execute();
} elseif ($remove_other_account) {
  $stm = $db->prepare("UPDATE same_player SET deleted = 1
    WHERE (player1 = :playerId AND player2 = :otherPlayerId)
      OR (player1 = :otherPlayerId AND player2 = :playerId)");
  $stm->bindInt("playerId", $player_id);
  $stm->bindInt("otherPlayerId", $remove_other_account);
  $stm->execute();
} elseif ($alter_credits) {
  if ($adminInfo->hasAccessTo(AccessConstants::ALTER_CREDITS)) {
    $stm = $db->prepare("SELECT * FROM players WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $player_id);
    $playerCredits = $stm->executeScalar();
    $new_credits = $playerCredits + $alter_credits;
    $stm = $db->prepare("INSERT INTO credits_alterations (`admin_id`,`player_id`,`from`,`to`)
      VALUES (:adminId, :playerId, :oldCredits, :newCredits)");
    $stm->bindInt("adminId", $adminInfo->getId());
    $stm->bindInt("playerId", $player_id);
    $stm->bindInt("oldCredits", $playerCredits);
    $stm->bindInt("newCredits", $new_credits);
    $stm->execute();

    $stm = $db->prepare("UPDATE players SET credits = :credits WHERE id = :playerId");
    $stm->bindInt("credits", $new_credits);
    $stm->bindInt("playerId", $player_id);
    $stm->execute();
  }
} elseif ($tou_change) {
  $stm = $db->prepare("UPDATE players SET terms_of_use = :touVersion WHERE id = :playerId LIMIT 1");
  $stm->bindInt("playerId", $player_id);
  $stm->bindInt("touVersion", $tou);
  $stm->execute();
} elseif ($newTutorCharLanguage && $env->introExists()) {
  $introDb = Db::get("intro");
  $mainDbName = $env->getDbNameFor("main");

  // if a player started the game when intro server was active then can already have an account, otherwise doesn't have any
  $stm = $introDb->prepare("SELECT COUNT(*) FROM players WHERE id = :playerId");
  $stm->bindInt("playerId", $player_id);
  $alreadyExists = $stm->executeScalar();
  if (!$alreadyExists) {
    $stm = $introDb->prepare("INSERT INTO `players` SELECT * FROM $mainDbName.players WHERE id = :playerId");
    $stm->bindInt("playerId", $player_id);
    $stm->execute();
  }

  // set the same password and reactivate account if needed
  // approval = 0 to disable possibility of intro->main account transfer
  $stm = $db->prepare("SELECT password FROM players WHERE id = :playerId");
  $stm->bindInt("playerId", $player_id);
  $mainPassword = $stm->executeScalar();
  $stm = $introDb->prepare("UPDATE players SET status = :status,
      password = :password, approval = 0 WHERE id = :playerId");
  $stm->bindInt("status", PlayerConstants::ACTIVE);
  $stm->bindStr("password", $mainPassword);
  $stm->bindInt("playerId", $player_id);
  $stm->execute();

  // params
  $sex = $newTutorCharSex;
  $lang = $newTutorCharLanguage;
  $creator = CharacterCreator::forPlayerId($player_id, $introDb);

  $mentorId = $creator->create("Mentor", $sex, $lang);
  $oldDate = GameDate::NOW()->minus(GameDate::fromDate(401, 0, 0, 0));

  // make mentor character much older
  $ageDesc = ($sex == CharacterConstants::SEX_MALE) ? "a man in his fourties" : "a woman in her fourties";
  $stm = $introDb->prepare("UPDATE chars SET register = :createDay,
                 description = :ageDesc WHERE id = :charId");
  $stm->bindInt("createDay", $oldDate->getDay());
  $stm->bindStr("ageDesc", $ageDesc);
  $stm->bindInt("charId", $mentorId);
  $stm->execute();

  $stm = $introDb->prepare("INSERT INTO `states` (person, type, value)
      VALUES (:charId, 2, 10000), (:charId, 13, 10000)");
  $stm->bindInt("charId", $mentorId);
  $stm->execute();
} elseif ($username) {
  $stm = $db->prepare("SELECT id FROM players WHERE username = :username");
  $stm->bindInt("username", $username);
  $alreadyUsed = $stm->executeScalar();

  if (!$alreadyUsed) {
    $stm = $db->prepare("UPDATE players SET username = :username WHERE id = :playerId");
    $stm->bindStr("username", $username);
    $stm->bindInt("playerId", $player_id);
    $stm->execute();
  } else {
    echo "<p style=\"text-align:center;color:#f00;font-size:18pt;\">This username is already used!</p>";
  }
}


/****************************************************************************/

// If there is a message, report it
if ($message != "") {
  $stm = $db->prepare("INSERT INTO players_report (contents) VALUES (:message)");
  $stm->bindStr("message", $message);
  $stm->execute();
}


$aPageData = [];

$aPageData["imagepath"] = _IMAGES;
$aPageData["tou_versions"] = $terms_of_use_versions;

$aPageData["admin"] = new stdClass();
$aPageData["admin"]->firstname = $adminInfo->getFirstName();
$aPageData["admin"]->lastname = $adminInfo->getLastName();
$aPageData["admin"]->allowed_to_alter_email = $adminInfo->hasAccessTo(AccessConstants::ALTER_EMAIL);
$aPageData["admin"]->allowed_to_see_passwords = $adminInfo->hasAccessTo(AccessConstants::SEE_PASSWORDS);
$aPageData["admin"]->allowed_to_change_privs = $adminInfo->hasAccessTo(AccessConstants::ALTER_PRIVILEGES);

/*****************************************************************************
 * Player data
 ****************************************************************************/

$stm = $db->prepare("SELECT * FROM players WHERE id = :id LIMIT 1");
$stm->bindInt("id", $player_id);
$stm->execute();
$player_info = $stm->fetchObject();
$targetPlayer = Player::loadById($player_id);

$aPageData["player"] = $player_info;
$aPageData["player"]->notes = urldecode($player_info->notes);
$aPageData["player"]->languageName = LanguageConstants::$LANGUAGE[$player_info->language]["en_name"];
$aPageData["player"]->recent_activity_display = display_bits($player_info->recent_activity, 16);

$aPageData["status"]["description"] = PlayerConstants::$STATUS_NAMES[$player_info->status];
$aPageData["status"]["action"] = getActionForStatus($player_info->status);
$aPageData["status"]["is_active"] = $targetPlayer->isAlive();

$aPageData["player"]->terms_of_use = $aPageData["tou_versions"][$aPageData["player"]->terms_of_use];
/*****************************************************************************
 * Account data
 ****************************************************************************/

//description (dis)allowance block starts here
$charDescDisallowed = Limitations::getLims($player_id, Limitations::TYPE_PLAYER_CHARDESCRIPTION);

if ($charDescDisallowed == 0) {
  $aPageData["custom_descriptions"]["status"] = "Allowed";
  $aPageData["custom_descriptions"]["button"] = "Disallow";
} else {
  $aPageData["custom_descriptions"]["status"] = "Disallowed";
  $aPageData["custom_descriptions"]["button"] = "Allow";
}

$newCharsLocked = Limitations::getLims($player_id, Limitations::TYPE_NEW_CHARACTERS) > 0;
$aPageData["newCharsLocked"] = $newCharsLocked;
$radioLocked = Limitations::getLims($player_id, Limitations::TYPE_PLAYER_RADIO_USAGE) > 0;
$aPageData["radioLocked"] = $radioLocked;

$privileges = new PrivilegesView($targetPlayer, $db);
if ($aPageData["admin"]->allowed_to_change_privs) {
  $aPageData["assignments"] = $privileges->getAssignmentTexts();
  $aPageData["access"] = $privileges->getAccess();
  $aPageData["ceAccess"] = $privileges->getCeAccess();
}

// Referred players
$stm = $db->prepare("SELECT id FROM players WHERE refplayer = :playerId ORDER BY id");
$stm->bindInt("playerId", $player_id);
$stm->execute();
$aPageData["referred_players"] = $stm->fetchScalars();

// Same player info
$stm = $db->prepare("
    SELECT sp.date AS date,sp.player1 AS player1,sp.player2 AS player2,sp.admin AS admin_id,
      players.firstname AS admin_fn,players.lastname AS admin_ln
    FROM same_player sp, players
    WHERE (sp.player1 = :playerId
      OR sp.player2 = :playerId)
      AND sp.admin = players.id
      AND sp.deleted = 0");
$stm->bindInt("playerId", $player_id);
$stm->execute();
foreach ($stm->fetchAll() as $same_player_info) {
  if ($same_player_info->player1 == $player_info->id) {
    $same_player_info->p2 = $same_player_info->player2;
  } else {
    $same_player_info->p2 = $same_player_info->player1;
  }
  $aPageData["same_player_info"][] = $same_player_info;
}

// IP History
if ($listips == "yes") {
  $stm = $db->prepare("SELECT * FROM ips WHERE player = :playerId ORDER BY lasttime");
  $stm->bindInt("playerId", $player_id);
  $stm->execute();
  foreach ($stm->fetchAll() as $ip_info) {
    $ip_info->remhost_ip = gethostbyaddr($ip_info->ip);
    if (!empty($ip_info->client_ip)) {
      $ip_info->remhost_client_ip = gethostbyaddr($ip_info->client_ip);
    }
    $aPageData["ip_info"][] = $ip_info;
  }
}

// Watches
$stm = $db->prepare("SELECT email FROM watches WHERE player = :playerId");
$stm->bindInt("playerId", $player_id);
$stm->execute();

$aPageData["ip_watch"] = $stm->fetchScalars();


// Mentors
$aPageData["introServerAvailable"] = $env->introExists();
if ($env->introExists()) {
  $introDb = Db::get("intro");
  $stm = $introDb->prepare("SELECT name, language FROM chars
	      WHERE status <= 1 AND player = :playerId");
  $stm->bindInt("playerId", $player_id);
  $stm->execute();
  $aPageData["mentors"] = $stm->fetchAll();
}

$realDate = date("d/m H:i");
// Characters
$playerHandler = new PlayerAdminTool($player_id, $db);
$playerCharacters = $playerHandler->getCharacters();
$aPageData["characters"] = [];
foreach ($playerCharacters as $char) {
  $aPageData["characters"][] = $playerHandler->getCharacterDetails($char,
    $gameDate,
    $listknownas == $char->getId(),
    $listknows == $char->getId(),
    $travels);
}

$aPageData["travels"] = $travels;
$aPageData["canControlCharacters"] = $adminInfo->hasAccessTo(AccessConstants::CONTROL_OTHER_CHARACTERS);
$aPageData["characterToAlterDesc"] = $alterdesc;


$smarty = new CantrSmarty;
$smarty->assign("player_id", $player_id);
$smarty->assign("aPageData", $aPageData);
$smarty->assign("langcode", $langcode);
$smarty->displayLang("info.infoplayer.tpl", $lang_abr);
