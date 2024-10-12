<?php

// SANITIZE INPUT
$location = HTTPContext::getInteger('location', null);
$rawtype = HTTPContext::getInteger('rawtype', null);
$display_empty = HTTPContext::getRawString('display_empty');
$island_lookup = $_REQUEST['island_lookup'];

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_RAW_MATERIAL_LOCATIONS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$db = Db::get();
$stm = $db->prepare("SELECT * FROM locations WHERE id = :locationId");
$stm->bindInt("locationId", $location);
$stm->execute();
$location_info = $stm->fetchObject();

$stm = $db->prepare("DELETE FROM raws WHERE location = :locationId AND type = :rawType");
$stm->bindInt("locationId", $location);
$stm->bindInt("rawType", $rawtype);
$stm->execute();

  $rawName = ObjectHandler::getRawNameFromId($rawtype);
  $playerInfo = Request::getInstance()->getPlayer();
	mail ($GLOBALS['emailResources'],"Cantr Raw Material Removal","{$playerInfo->getFullName()} removed $rawName from $location_info->name.");


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
