<?php

// SANTITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if ($object->getRepairRate() > 0) {
  $fullRepair = ceil($object->getDeterioration() / $object->getRepairRate());
} else {
  CError::throwRedirectTag("char.events", "error_cant_be_repaired");
}

$alreadyBeingRepaired = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_REPAIRING)->subtype($object_id)->exists();
if($alreadyBeingRepaired != null) {
  CError::throwRedirectTag("char.inventory", "error_already_being_repaired");
}

$smarty = new CantrSmarty;

$smarty->assign ("itemname", "<CANTR REPLACE NAME=item_{$object->getUniqueName()}_o>");
$smarty->assign ("HOURS", ceil ($fullRepair));
$smarty->assign ("object_id", $object_id);
$smarty->assign ("fullRepair", $fullRepair);
$smarty->assign ("object", $object->getId());

$smarty->displayLang ("form.repair.tpl", $lang_abr); 
