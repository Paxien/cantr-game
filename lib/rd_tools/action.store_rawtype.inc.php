<?php

// SANITIZE INPUT
$new = HTTPContext::getString('new', null);
$perday = HTTPContext::getInteger('perday');
$nutrition = HTTPContext::getInteger('nutrition');
$strengthening = HTTPContext::getInteger('strengthening');
$tainting = HTTPContext::getInteger('tainting');
$skill = HTTPContext::getInteger('skill');
$reqtools = HTTPContext::getInteger('reqtools');
$energy = HTTPContext::getInteger('energy');
$drunkenness = HTTPContext::getInteger('drunkenness');
$taint_target_weight = HTTPContext::getInteger('taint_target_weight');
$agricultural = HTTPContext::getBoolean('agricultural') ? 1 : 0;
$rawtype_id = HTTPContext::getInteger('rawtype_id');
$action = HTTPContext::getString('action', '');
$name = HTTPContext::getString('name');
$description = HTTPContext::getRawString("description");

/********* CHECKING WHETHER PLAYER HAS ACCESS TO THIS PAGE **************/

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_RAW_MATERIALS)) {
  CError::throwRedirect("player", "You do not have access to the raw material types management page.");
}

/************* STORING THE DATA ******************************************/

$db = Db::get();
if ($new) {

  $message = "The following new raw material type has been entered into the database:\n\n";

  $stm = $db->prepare("INSERT INTO rawtypes (name, perday, action, nutrition, strengthening,
    tainting, taint_target_weight, skill, reqtools, energy, drunkenness, agricultural)
    VALUES (:name, :perday, :action, :nutrition, :strengthening, :tainting, :taint_target_weight,
    :skill, :reqtools, :energy, :drunkenness, :agricultural)");
  $stm->execute([
    "name" => $name, "perday" => $perday, "action" => $action, "nutrition" => $nutrition, "strengthening" => $strengthening,
    "tainting" => $tainting, "taint_target_weight" => $taint_target_weight, "skill" => $skill, "reqtools" => $reqtools,
    "energy" => $energy, "drunkenness" => $drunkenness, "agricultural" => $agricultural,
  ]);
  $id = $db->lastInsertId();

  $date = date("Y-m-d", time());
  $stm = $db->prepare("INSERT INTO texts (type, language, name, content, translator, updated)
    VALUES (1, 1, :tag, :content, 'Resource Department <resources@cantr.net>', :date)");
  $stm->execute([
    "tag" => TagUtil::getRawTagByName($name),
    "content" => $name,
    "date" => $date,
  ]);

} else {
  $message = "The following raw material type has been changed:\n\n";

  $id = $rawtype_id;

  $stm = $db->prepare("UPDATE rawtypes SET name = :name, perday = :perday, action = :action, nutrition = :nutrition,
    strengthening = :strengthening, tainting = :tainting, taint_target_weight = :taint_target_weight, skill = :skill,
    reqtools = :reqtools, energy = :energy, drunkenness = :drunkenness, agricultural = :agricultural WHERE id = :id");
  $stm->execute([
    "name" => $name, "perday" => $perday, "action" => $action, "nutrition" => $nutrition, "strengthening" => $strengthening,
    "tainting" => $tainting, "taint_target_weight" => $taint_target_weight, "skill" => $skill, "reqtools" => $reqtools,
    "energy" => $energy, "drunkenness" => $drunkenness, "agricultural" => $agricultural, "id" => $id,
  ]);
}

$message .= "ID:\t$id\n";
$message .= "Name:\t$name\n";
$message .= "Amount per day:\t$perday\n";
$message .= "Requires tools to dig:\t$reqtools\n";
$message .= "Action:\t$action\n";
$message .= "Skill:\t$skill\n";
$message .= "Nutrition:\t$nutrition\n";
$message .= "Strengthening:\t$strengthening\n";
$message .= "Energy:\t$energy\n";
$message .= "Drunkenness:\t$drunkenness\n";
$message .= "Tainting (outside):\t$tainting\n";
$message .= "Target weight for Tainting (universal):\t$taint_target_weight\n";
$message .= "Agricultural:\t$agricultural\n";
$message .= "Comments:\n\n";
$message .= "$description\n\n";
$message .= $playerInfo->getFullNameWithId() . "\n\n";
$message .= "(This is an automatically created message)";

$env = Request::getInstance()->getEnvironment();
$mailService = new MailService("Resources Department", $GLOBALS['emailResources']);
$mailService->sendPlaintext($GLOBALS['emailResources'], $env->getFullName() . " Raw material $name change", $message);

redirect("managerawtypes");
