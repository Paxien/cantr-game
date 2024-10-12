<?php

$buildingId = HTTPContext::getInteger('building'); 

try {
  $building = Location::loadById($buildingId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if ($char->getLocation() != $building->getRegion()) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if (!$building->isDestroyable()) {
  CError::throwRedirectTag("char.buildings", "error_builiding_not_destroyable");
}

$rules = Parser::rulesToArray($building->getTypeRules());
$destructionRules = Parser::rulesToArray($rules['destroyable'], ",>");
if (isset($destructionRules['tools'])) {
  $tools = explode("/", $destructionRules['tools']);
  $requiredTools = urlencode(implode(", ", $tools));
}

$smarty = new CantrSmarty();

$smarty->assign("buildingId", $buildingId);
$smarty->assign("requiredTools", $requiredTools);

$smarty->displayLang("page.destroy_building.tpl", $lang_abr);
