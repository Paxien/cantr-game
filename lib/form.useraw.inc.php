<?php

// SANITIZE INPUT
$objectId = HTTPContext::getInteger('object_id');
$projectId = HTTPContext::getInteger('project');

try {
  $object = CObject::loadById($objectId);
  $project = Project::loadById($projectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

$objectNear = $char->isInSameLocationAs($object) || $char->hasInInventory($object);
$projectInSameLoc = $char->isInSameLocationAs($project);
if (!$objectNear || !$projectInSameLoc) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

$forProject = new UseForProject($char, $project);

$rawName = ObjectHandler::getRawNameFromId($object->getTypeid());
$amountNeeded = $forProject->getRawNeeded($rawName);

if ($amountNeeded) {
  $max = min($amountNeeded, $object->getWeight());

  $smarty = new CantrSmarty; 

  $smarty->assign ("NEEDED", $amountNeeded);
  $smarty->assign ("RAWNAME", $rawName);
  $smarty->assign ("POSSESED", $object->getWeight());
  $smarty->assign ("max", $max);
  $smarty->assign ("project", $project->getId());
  $smarty->assign ("object_id", $object->getId());

  $smarty->displayLang ("form.useraw.tpl", $lang_abr);
} else {
  CError::throwRedirect("char.events", "This project does not need any (more) ". $object->getTypeid());
}
