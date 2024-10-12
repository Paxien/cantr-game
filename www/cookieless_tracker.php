<?php

/**
 * A script meant to intercept displaying one picture called 'graphics/cantr/pictures/button_newchar.gif'.
 * It does display it but at the same time it does read/set picture's ETAG (sth like cache key) to make ETAG contain encrypted version of player id.
 * When the picture is displayed for user who is not logged in then ETAG value is set to value of _ETAG_NO_PLAYER.
 * When the picture is displayed for player and no valid player ID was stored in the ETAG value, then current player's ID is saved in ETAG.
 * When the picture is displayed for player and there already exists ETAG containing other player's ID,
 * then it means that two accounts have logged in from the same browser and it's suspected multiaccount.
 * The information is stored in `multi_logins` table, where group_id is the player ID stored in ETAG
 * and player is ID of player who displayed this picture (please note it means group_id != player).
 * When there already exists at least one row for certain group_id, then login info is stored even if group_id = player.
 * It means it starts to count number of logins for main account too.
 * To keep the data reliable, the `multi_logins` table stored number of logins for every account to
 * see how often was suspected multiaccount accessed. If it's done just a few times then maybe it's friend's account.
 * To avoid increasing the `count` every time the main page is entered, column last_session id is updated and count is incremented only if
 * current session id != last_session.
 *
 * Interpretation of data:
 * Players recorded for the same group_id are suspected multi-accounts. Count shows number of logins of every account.
 */

define("_ETAG_NO_PLAYER", "mv8WQDI8U64LBNiiyAsLDTQnKM2Y647lEkQbEusjhQM=");
define("_NOT_SO_SECRET_KEY", "I like potatoes!@#!@$!@$!@$@#%!#");

/*
 * HELPER FUNCTIONS
 */

function encryptEtag($sValue)
{
  return rtrim(
    base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, _NOT_SO_SECRET_KEY, $sValue, MCRYPT_MODE_ECB,
      mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))), "\0");
}

function decryptEtag($sValue)
{
  return rtrim(
    mcrypt_decrypt(MCRYPT_RIJNDAEL_256, _NOT_SO_SECRET_KEY, base64_decode($sValue), MCRYPT_MODE_ECB,
      mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)), "\0");
}


function compareLoggedPlayers($currentPlayer, $rememberedPlayer, $sessionId, Db $db)
{
  $stm = $db->prepare("SELECT COUNT(*) FROM multi_logins WHERE group_id = :id");
  $stm->bindInt("id", $currentPlayer);
  $alreadyRecorded = $stm->executeScalar();

  $canBeSaved = $alreadyRecorded || ($currentPlayer != $rememberedPlayer); // we notice a multi or we know it's already known multi

  if ($canBeSaved) { // somebody has logged into second account
    $stm = $db->prepare("INSERT INTO multi_logins (group_id, player, last_session, count)
      VALUES (:rememberedPlayer, :currentPlayer, :sessionId1, 1)
      ON DUPLICATE KEY UPDATE count = IF(last_session != :sessionId2, count + 1, count), last_session = :sessionId3");
    $stm->bindInt("rememberedPlayer", $rememberedPlayer);
    $stm->bindInt("currentPlayer", $currentPlayer);
    $stm->bindInt("sessionId1", $sessionId);
    $stm->bindInt("sessionId2", $sessionId);
    $stm->bindInt("sessionId3", $sessionId);
    $stm->execute();
  }
}

function getEtag($sessionId, Db $db)
{

  if (!Validation::isPositiveInt($sessionId)) { // not logged in
    if (empty($_SERVER["HTTP_IF_NONE_MATCH"])) { // probably looking for the first time
      return _ETAG_NO_PLAYER;
    }
    return $_SERVER["HTTP_IF_NONE_MATCH"]; // keep old value
  }

  $stm = $db->prepare("SELECT player FROM sessions WHERE id = :sessionId");
  $stm->bindInt("sessionId", $sessionId);
  $currentPlayer = $stm->executeScalar();

  if (empty($_SERVER["HTTP_IF_NONE_MATCH"])) {
    return encryptEtag($currentPlayer);
  }
  // ok, so we have saved something in etag

  $oldEtag = $_SERVER["HTTP_IF_NONE_MATCH"];
  $previousPlayer = decryptEtag($oldEtag);

  if (!Validation::isPositiveInt($previousPlayer)) { // old etag value doesn't have sense, must overwrite it
    return encryptEtag($currentPlayer);
  }

  if (Validation::isPositiveInt($currentPlayer)) {
    compareLoggedPlayers($currentPlayer, $previousPlayer, $sessionId, $db);
  }

  return $oldEtag;
}

/*
 * MAIN CODE
 */


include_once "../lib/stddef.inc.php";

$s = session::getSessionFromCookie();
$db = Db::get();
$etag = getEtag($s, $db); // our "cookie"

header("Cache-Control: private, must-revalidate, proxy-revalidate");
header("ETag: " . $etag);
header("Content-type: image/gif");
header("Content-length: " . filesize("graphics/cantr/pictures/button_newchar.gif"));
readfile("graphics/cantr/pictures/button_newchar.gif");
