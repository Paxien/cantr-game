<?php

// SANITIZE INPUT
$object = HTTPContext::getInteger('object');
$comments = $_REQUEST['comments'];
$name = $_REQUEST['name'];
$unique_name = $_REQUEST['unique_name'];

$propNames = $_REQUEST['propNames'];
$propDetails = $_REQUEST['propDetails'];

$db = Db::get();

$message = "New object in objecttypes database:\n\n";

$stm = $db->prepare("UPDATE objecttypes SET name = :name, unique_name = :unique_name, show_instructions_outside = :show_instructions_outside,
  show_instructions_inventory = :show_instructions_inventory, build_conditions = :build_conditions, build_description = :build_description,
  build_requirements = :build_requirements, build_result = :build_result, skill = :skill, subtable = :subtable, category = :category,
  rules = :rules, visible = :visible, report = :report, deter_rate_turn = :deter_rate_turn, deter_rate_use = :deter_rate_use,
  repair_rate = :repair_rate, deter_visible = :deter_visible, project_weight = :project_weight, image_file_name = :image_file_name,
  objectcategory = :objectcategory WHERE id = :id");
$stm->execute([
  "name" => $_REQUEST["name"],
  "unique_name" => $_REQUEST["unique_name"],
  "show_instructions_outside" => $_REQUEST["show_instructions_outside"],
  "show_instructions_inventory" => $_REQUEST["show_instructions_inventory"],
  "build_conditions" => $_REQUEST["build_conditions"],
  "build_description" => $_REQUEST["build_description"],
  "build_requirements" => $_REQUEST["build_requirements"],
  "build_result" => $_REQUEST["build_result"],
  "skill" => $_REQUEST["skill"],
  "subtable" => $_REQUEST["subtable"],
  "category" => $_REQUEST["category"],
  "rules" => $_REQUEST["rules"],
  "visible" => $_REQUEST["visible"],
  "report" => $_REQUEST["report"],
  "deter_rate_turn" => $_REQUEST["deter_rate_turn"],
  "deter_rate_use" => $_REQUEST["deter_rate_use"],
  "repair_rate" => $_REQUEST["repair_rate"],
  "deter_visible" => $_REQUEST["deter_visible"],
  "project_weight" => $_REQUEST["project_weight"],
  "image_file_name" => $_REQUEST["image_file_name"],
  "objectcategory" => $_REQUEST["objectcategory"],
  "id" => $object,
]);

$newProperties = array_combine($propNames, $propDetails);
$stm = $db->prepare("DELETE FROM obj_properties WHERE objecttype_id = :id");
$stm->bindInt("id", $object);
$stm->execute();

$stm = $db->prepare("INSERT INTO obj_properties (objecttype_id, property_type, details)
  VALUES (:id, :propName, :propDetails)");
foreach ($newProperties as $propName => $propDetails) {
  if (!empty($propName) && !empty($propDetails)) {
    $stm->bindInt("id", $object);
    $stm->bindStr("propName", $propName);
    $stm->bindStr("propDetails", $propDetails);
    $stm->execute();
  }
}

if (count($newProperties)) {
  $message .= "Properties: \n";
  foreach ($newProperties as $propName => $propDetails) {
    if (!empty($propName)) {
      $message .= "   $propName: $propDetails\n";
    }
  }
}

$plr = Request::getInstance()->getPlayer();

$message .= "\nComments:\n\n$comments\n";
$message .= "\nby: " . $plr->getFullNameWithId() ."\n\n";
$message .= "(This is an automatically created message.)";

$env = Request::getInstance()->getEnvironment();
$mailService = new MailService("Resources department", $GLOBALS['emailResources']);
$mailService->sendPlaintext($GLOBALS['emailResources'], $env->getFullName() . " Altered objecttype ($unique_name)", $message);

redirect("manageobjects");
