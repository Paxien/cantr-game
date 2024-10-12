<?php
// SANITIZE INPUT
$location = HTTPContext::getInteger('location');
$rawtype = HTTPContext::getInteger('rawtype');
$island_lookup = $_REQUEST['island_lookup'];
$display_empty = HTTPContext::getRawString("display_empty");

$accepted = false;

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::MANAGE_RAW_MATERIAL_LOCATIONS)) {
	$accepted = true;
}

$db = Db::get();
$stm = $db->prepare("SELECT * FROM locations WHERE id = :locationId");
$stm->bindInt("locationId", $location);
$stm->execute();

$location_info = $stm->fetchObject();

if ($location_info->type != 1) { $accepted = false; }

$stm = $db->prepare("SELECT COUNT(*) FROM raws WHERE location = :locationId AND type = :type");
$stm->bindInt("locationId", $location);
$stm->bindInt("type", $rawtype);
$rawExists = $stm->executeScalar() > 0;

if ($rawExists) { $accepted = false; }

if ($accepted) {

	$stm = $db->prepare("SELECT * FROM rawtypes WHERE id = :id");
	$stm->bindInt("id", $rawtype);
	$stm->execute();
	$rawtype_info = $stm->fetchObject();

  $playerInfo = Request::getInstance()->getPlayer();

	$mailService = new MailService("Cantr Resources", $GLOBALS['emailResources']);
	$mailService->sendPlaintext($GLOBALS['emailResources'],"Cantr Raw Material Placing ", "{$playerInfo->getFullName()} placed $rawtype_info->name in $location_info->name.");

	$stm = $db->prepare("INSERT INTO raws (location, type) VALUES (:locationId, :type)");
	$stm->bindInt("locationId", $location);
	$stm->bindInt("type", $rawtype);
	$stm->execute();
}

$redir = [];
if ($island_lookup) {
  $redir["region"] = $location_info->island;
} else {
  $redir["region"] = $location_info->region;
}
if ($display_empty) {
  $redir["display_empty"] = $display_empty;
}
$redir["island_lookup"] = $island_lookup;

redirect("listraws", $redir);
