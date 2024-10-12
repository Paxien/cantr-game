<?php

// SANITIZE INPUT
$objectId = HTTPContext::getInteger('object');
$choice = HTTPContext::getInteger('choice');

$next = 'noform';

$db = Db::get();
if ($choice == 'yes') {

  $stm = $db->prepare("SELECT * FROM objects WHERE id = :objectId");
  $stm->bindInt("objectId", $objectId);
  $stm->execute();
  $object_info = $stm->fetchObject();

  $stm = $db->prepare("SELECT * FROM machines WHERE type = :type");
  $stm->bindInt("type", $object_info->type);
  $stm->execute();
  $machine_info = $stm->fetchObject();
} else {

  $stm = $db->prepare("SELECT * FROM machines WHERE id = :id");
  $stm->bindInt("id", $choice);
  $stm->execute();
  $machine_info = $stm->fetchObject();
}

if ($machine_info->multiply) {
  $next = 'form';
}

$linkParams = "object=$objectId&machine=$machine_info->id";

list ($outputRaw, $dailyOutput) = explode(":", $machine_info->result);

if ($next == 'noform') {
    $linkParams .= "&multiplier=1";
    $result_parts = preg_split ("/:/", $machine_info->result);
    $linkParams .= "&amount=" . $dailyOutput;
} else {
    $linkParams .= "&multiplier=0";
}

$show_allocation = false;

$rules = Parser::rulesToArray($machine_info->requirements);
$requiredRaws = Parser::rulesToArray($rules['raws'], ",>");
$days = $rules['days'];

$stm = $db->prepare("SELECT * FROM rawtypes WHERE id = :id");
$stm->bindInt("id", $outputRaw);
$stm->execute();
$raw_info = $stm->fetchObject();

//ugly translate resource name
$tag = new tag();
$tag->html = false;
$rawname = str_replace (" ", "_", $raw_info->name);
$tag->content = "<CANTR REPLACE NAME=raw_$rawname>";
$rawMaterialName =  urlencode($tag->interpret());

$resources = array();
foreach ($requiredRaws as $rawName => $rawAmount) {
  $rawsStm = $db->prepare("SELECT obj.weight FROM rawtypes rt
    LEFT JOIN objects obj ON obj.type = 2 AND obj.typeid = rt.id AND obj.person = :charId
    WHERE rt.name = :rawName");
  $rawsStm->bindInt("charId", $char->getId());
  $rawsStm->bindStr("rawName", $rawName);
  $rawsStm->execute();

  if ($yourRaw = $rawsStm->fetchObject()) {
    $youHave = $yourRaw->weight;
  } else {
    $youHave = 0;
  }
  
  $rawName = str_replace(" ", "_", $rawName);
  $resources[$rawName] = array( 0 => $rawAmount, 1 => $youHave );
  $show_allocation = true;
}

$smarty = new CantrSmarty;

$smarty->assign("resources", $resources);
$smarty->assign("output_name", $rawMaterialName);
$smarty->assign("days", $days);
$smarty->assign("prod", $dailyOutput);
$smarty->assign("next", $next);
$smarty->assign("back_to_objects", $choice == 'yes');
$smarty->assign("show_allocation", $show_allocation);
$smarty->assign("character", $character);
$smarty->assign("object", $objectId);
$smarty->assign("linksParams", $linkParams );

$smarty->displayLang("form.amount.use.tpl", $lang_abr);
