<?php

include_once "../lib/stddef.inc.php";
include_once _LIB_LOC . "/header.functions.inc.php";

$UserId = HTTPContext::getInteger("UserId");

$remote = getenv("REMOTE_ADDR");
$remote_url = gethostbyaddr($remote);
$db = Db::get();

if ($remote_url == "plitgames.dk") {

  $stm = $db->prepare("UPDATE players SET credits = credits + 1 WHERE id = :playerId LIMIT 1");
  $stm->bindInt("playerId", $UserId);
  $stm->execute();
} else {

  echo "Sorry, no voting site was recognized and your vote was not recorded.";
}
