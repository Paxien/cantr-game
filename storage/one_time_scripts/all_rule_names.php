<?php

$page = "all_rule_names";
include("../../lib/stddef.inc.php");


$db = Db::get();
$stm = $db->query("SELECT rules FROM objecttypes WHERE rules NOT LIKE ''");
$allRules = [];
foreach ($stm->fetchScalars() as $rule) {
  $ruleAsArray = Parser::rulesToArray($rule);
  $ruleNames = array_keys($ruleAsArray);
  foreach ($ruleNames as $ruleName) {
    $allRules[$ruleName] = "";
  }
}

echo json_encode(array_keys($allRules));
