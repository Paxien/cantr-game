<?php

$page = "server.violencereport";
include "server.header.inc.php";

$db = Db::get();
$SPARRING_WEAPON_HIT = 11; // weapons with low damage are treated separately

$logger = Logger::getLogger("server.violencereport");


function formatCharInfo(Character $char)
{
  $charPlayer = Player::loadById($char->getPlayer());
  return $char->getName() . " (" . $char->getId() . " of " . $charPlayer->getId() . " " . $charPlayer->getFullName() . ")";
}

$violenceReport = "Recorded violence:\n\n";
$sparringReport = "\n\n\n*********************************************\n";
$sparringReport .= "Sparring report:\n\n";

$stm = $db->query("SELECT * FROM `violence` ORDER BY `victim`, `turn`, `turnpart`, `minute`, `second`");
foreach ($stm->fetchAll() as $violence_info) {

  try {
    $perpetrator = Character::loadById($violence_info->perpetrator);
    $victim = Character::loadById($violence_info->victim);

    if (!$violence_info->type) {
      $weaponName = "bare hands";
      $weaponWrapper = new Fists();
    } else { // normal weapon
      $weapon = CObject::loadById($violence_info->type);
      $weaponWrapper = new Weapon($weapon);

      $weaponName = $weapon->getUniqueName();
    }

    $formattedDate = $violence_info->turn . "-" . $violence_info->turnpart
      . "." . $violence_info->minute . "." . $violence_info->second;
    $violenceEntry = sprintf("%s: %s is hit by %s with %s\n", $formattedDate, formatCharInfo($victim),
      formatCharInfo($perpetrator), $weaponName);

    if ($weaponWrapper->getHit() >= $SPARRING_WEAPON_HIT) {
      $violenceReport .= $violenceEntry;
    } else {
      $sparringReport .= $violenceEntry;
    }
  } catch (InvalidArgumentException $e) {
    $logger->warn("Unable to record violence of " . $violence_info->perpetrator . " on " . $violence_info->victim, $e);
  }
}


$env = Request::getInstance()->getEnvironment();

$mailService = new MailService("Players Department", $GLOBALS['emailPlayers']);
$mailService->sendPlaintext($GLOBALS['emailPlayers'], $env->getFullName() . " Violence Report", $violenceReport . $sparringReport);

$stm = $db->prepare("SELECT contents FROM reports WHERE name='dragging'");
$report = $stm->executeScalar();

$dragging_message = "Recorded dragging:\n\n $report";

$mailService->sendPlaintext($GLOBALS['emailPlayers'], $env->getFullName() . " Dragging Report", $dragging_message);

$db->query("UPDATE reports SET contents='' WHERE name='dragging'");

include "server/server.footer.inc.php";

