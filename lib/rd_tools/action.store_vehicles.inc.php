<?php

// SANITIZE INPUT
$speedlimit = HTTPContext::getInteger('speedlimit');
$connection = HTTPContext::getInteger('connection');
$walking = HTTPContext::getRawString("vehicle0");

show_title("MANAGE CONNECTION TYPES");

/********* CHECKING WHETHER PLAYER HAS ACCESS TO THIS PAGE **************/

$db = Db::get();
$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::ALTER_OBJECTTYPES_AND_VEHICLES)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$vehicles = [];
if ($walking) {
  $vehicles[] = "walking";
}

$stm = $db->query("SELECT * FROM objecttypes WHERE category='vehicles'"); // todo it shouldn't use this category
foreach ($stm->fetchAll() as $objecttype_info) {

  $var = HTTPContext::getRawString("vehicle" . $objecttype_info->id);

  if ($var) {
    $vehicles[] = $objecttype_info->id;
  }
}

$stm = $db->prepare("UPDATE connecttypes SET vehicles = :vehicles, speedlimit = :speedlimit WHERE id = :id");
$stm->execute([
  "vehicles" => implode(",", $vehicles),
  "speedlimit" => $speedlimit,
  "id" => $connection,
]);

$connectionType = ConnectionType::loadById($connection);

$mailService = new MailService("Cantr Resources", $GLOBALS['emailResources']);
$mailService->sendPlaintext($GLOBALS['emailGMS'], "Connection info change", "Connection info changed for {$connectionType->getName()} by {$playerInfo->getFullName()}");

redirect("managevehicles");
