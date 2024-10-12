<?php

// SANITIZE INPUT
$type = HTTPContext::getInteger('type');

$smarty = new CantrSmarty;

$db = Db::get();
$stm = $db->prepare("SELECT * FROM rawtypes WHERE id = :id");
$stm->bindInt("id", $type);
$stm->execute();
$rawtype_info = $stm->fetchObject();

if (empty($rawtype_info)) {
  CError::throwRedirectTag("char.description", "error_no_dig_here");
}

$actions = array(
  "dig" => "action_dig_1",
  "collect" => "action_dig_2",
  "pump" => "action_dig_3",
  "farm" => "action_dig_4",
  "catch" => "action_dig_5",
  "pick" => "action_dig_6",
);

$action = $actions[$rawtype_info->action];
if (!$action) $actions["dig"]; // default
$action = "<CANTR REPLACE NAME=$action>";

$raw = str_replace(" ", "_", $rawtype_info->name);

$smarty->assign ("rawtitle", "$action <CANTR REPLACE NAME=raw_$raw>");
$smarty->assign ("ACTION", urlencode ($rawtype_info->action));
$smarty->assign ("PERDAY", $rawtype_info->reqtools == 1 ? 0 : $rawtype_info->perday);

$stm = $db->prepare("
    SELECT rt.tool, rt.perday, ot.unique_name
    FROM rawtools rt INNER JOIN objecttypes ot ON ot.id = rt.tool
    WHERE rt.projecttype = 1 AND rt.rawtype = :id");
$stm->bindInt("id", $rawtype_info->id);
$stm->execute();

foreach ($stm->fetchAll() as $tool_info) {
  $tools [] = "<CANTR REPLACE NAME=page_dig_2 TYPE=" . urlencode($tool_info->unique_name) .
    " ACTION=" . urlencode($rawtype_info->action) .
    " PERDAY=" . floor($tool_info->perday / 100 * $rawtype_info->perday) . ">";
}

$smarty->assign ("tools", $tools);
$smarty->assign ("rawtype", $rawtype_info->id);
$smarty->assign ("rawaction", $rawtype_info->action);

$smarty->displayLang ("form.dig.tpl", $lang_abr); 
