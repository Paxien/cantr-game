<?php

// SANITIZE INPUT
$object = HTTPContext::getInteger('object');

$db = Db::get();

$stm = $db->prepare("INSERT INTO objecttypes (name, unique_name, show_instructions_outside, show_instructions_inventory, build_conditions, build_description, build_requirements, build_result, skill, subtable, category, rules, visible, report, deter_rate_turn, deter_rate_use, repair_rate, deter_visible, project_weight, image_file_name, objectcategory)
  SELECT name, unique_name, show_instructions_outside, show_instructions_inventory, build_conditions, build_description, build_requirements, build_result, skill, subtable, category, rules, visible, report, deter_rate_turn, deter_rate_use, repair_rate, deter_visible, project_weight, image_file_name, objectcategory FROM objecttypes WHERE id = :id");
$stm->bindInt("id", $object);
$stm->execute();
$id = $db->lastInsertId();

$stm = $db->prepare("UPDATE objecttypes SET objectcategory = :objectCategory WHERE id = :id");
$stm->bindInt("objectCategory", ObjectConstants::OBJCAT_TEMPORARILY_UNMANUFACTURABLE);
$stm->bindInt("id", $id);
$stm->execute();

$stm = $db->prepare("INSERT INTO obj_properties (objecttype_id, property_type, details)
  SELECT :newId, property_type, details FROM obj_properties WHERE objecttype_id = :originalId");
$stm->bindInt("newId", $id);
$stm->bindInt("originalId", $object);
$stm->execute();

redirect("manageobjects");
