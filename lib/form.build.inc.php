<?php

$objecttype = HTTPContext::getInteger('objecttype');
$targetcontainer = HTTPContext::getInteger('targetcontainer');


try {
  $objectType = ObjectType::loadById($objecttype);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("build", "error_no_objecttype");
}

$requirements = Parser::rulesToArray($objectType->getBuildRequirements());
$buildConditions = Parser::rulesToArray($objectType->getBuildConditions());
$buildResult = Parser::rulesToArray($objectType->getBuildResult());
$rules = Parser::rulesToArray($objectType->getRules());
$questions = array();
foreach ($buildResult as $rule) {
  $rulePart = explode(",", $rule);
  foreach ($rulePart as $subRule) { // if subrule contains "ask" then it will ask for name
    $subParts = explode(">", $subRule);
    if (in_array($subParts[1], array("ask", "unique-ask"))) {
      $questions[] = array("a" => $subParts[0], "b" => strtolower($subParts[2]));
    }
  }
}

$requiredRaws = array();
if ($requirements['raws']) {
  $raws = Parser::rulesToArray($requirements['raws'], ",>");
  foreach ($raws as $raw => $amount) {
    $requiredRaws[str_replace(" ", "_", $raw)] = $amount;
  }
}

$requiredObjects = array();
if ($requirements['objects']) {
  $objects = Parser::rulesToArray($requirements['objects'], ",>");
  foreach ($objects as $objName => $objNum) {
    $objectTag = ObjectHandler::getObjectTypeTagByName($objName);
    $requiredObjects[$objectTag] = $objNum;
  }
}

$requiredTools = array();
if ($requirements['tools']) {
  $tools = explode(",", $requirements['tools']);
  foreach ($tools as $tool) {
    $requiredTools[] = ObjectHandler::getObjectTypeTagByName($tool);
  }
}

$requiredMachines = array();
if ($buildConditions['hasobject']) {
  $machines = explode(",", $buildConditions['hasobject']);
  foreach ($machines as $machine) {
    $requiredMachines[] = ObjectHandler::getObjectTypeTagByName($machine);
  }
}

if ($rules['describable']) {
  $descRules = Parser::rulesToArray($rules['describable'], ",>");
  $isDescribable = ($descRules['bymanufacturing'] == "yes");
}

$objectNameTag = ObjectHandler::getBuildObjectNameTag($objecttype);
$objectImage = PlayerSettings::getInstance($player)->get(PlayerSettings::BUILD_PICTURES) ? strtolower($objectType->getImageFileName()) : "";

$massProduction = ProjectSetup::isMassProductionAllowed($objectType);

$smarty = new CantrSmarty;

$smarty->assign("objectNameTag", $objectNameTag);
$smarty->assign("objecttype", $objecttype);
$smarty->assign("objectImage", $objectImage);
$smarty->assign("days", $requirements['days']);
$smarty->assign("raws", $requiredRaws);
$smarty->assign("objects", $requiredObjects);
$smarty->assign("tools", $requiredTools);
$smarty->assign("machines", $requiredMachines);
$smarty->assign("massProduction", $massProduction);
$smarty->assign("MASS_PRODUCTION_MAX", ProjectConstants::MASS_PRODUCTION_MAX);
$smarty->assign("ALLOWED_NUMBERS", range(1, ProjectConstants::MASS_PRODUCTION_MAX));

$smarty->assign("isDescribable", $isDescribable);
$smarty->assign("DESC_MAX_LEN", Descriptions::$TEXT_MAXLEN[Descriptions::TYPE_OBJECT]);

$smarty->assign("targetcontainer", $targetcontainer);
$smarty->assign("show_allocation", $requiredRaws || $requiredObjects);
$smarty->assign("items", $questions);

$charInfo = new CharacterInfoView($char);
$charInfo->show();

$smarty->displayLang("form.build.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
