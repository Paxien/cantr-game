<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_project_not_same_location");
}

// check whether it's a valid object  -- wrong are raws, notes and eveloppes, coins, keys
$array_of_invalid_types = array(1, 2, 30, 37, 578, 418, 414, 577, 416, 417, 413, 415, 580, 579, 576, 808);

if (in_array($object->getType(), $array_of_invalid_types)) {
  CError::throwRedirectTag("char.events", "error_object_not_needed");
}

//check if the machine/object is in the same location
if (!$char->isInSameLocationAs($object)) {
  CError::throwRedirectTag("char.objects", "error_project_not_same_location");
}

$innerLock = KeyLock::loadByObjectId($object_id);
if ($innerLock->hasId()) { // lock exists
  if ($innerLock->isLocked()) { // you have to first pick this inner lock
    $innerLock->redirectToLockpicking();
  } else { // you have to first disassemble this inner lock
    redirect("dest", ["object_id" => $innerLock->getId()]);
    exit;
  }
}

//check if the object can be disassembled
$objectRules = Parser::rulesToArray($object->getRules());
if ($objectRules['recyclable'] == null) {
  CError::throwRedirectTag("char.events", "error_object_cannot_be_disassembled");
}


// checking if the selected object is in use
if ($object->isInUse()) {
  CError::throwRedirectTag("char.objects", "error_recycling_machine_in_use");
}

// checking if the initiator has got all needed tools. If not, inform

$recRules = Parser::rulesToArray($objectRules['recyclable'], ",>");
$buildRules = Parser::rulesToArray($object->getBuildRequirements());

if (isset($recRules['tools'])) { // tools set only for disassembling machine
  $tools = explode("/", $recRules['tools']);
} elseif (isset($buildRules['tools'])) { // if not set then tools used to build machine
  $tools = explode(",", $buildRules['tools']);
}

if (count($tools) > 0) {
  $missingTools = [];
  foreach ($tools as $toolName) { // all needed tools

    $hasTool = CObject::inInventoryOf($char)->name($toolName)->exists();
    if (!$hasTool) { // needed tool not in inventory
      $missingTools[] = $toolName;
    }
  }

  if (count($missingTools) > 0) {
    $translated = [];
    $db = Db::get();
    $stm = $db->prepareWithList("SELECT unique_name FROM objecttypes WHERE name IN (:names) GROUP BY name", [
      "names" => $missingTools,
    ]);
    $stm->execute();
    foreach ($stm->fetchScalars() as $objName) {
      $translated[] = "<CANTR REPLACE NAME=item_{$objName}_o>";
    }
    $toolsText = urlencode(implode(", ", $translated));
    CError::throwRedirect("char.events", "<CANTR REPLACE NAME=error_lack_tools TOOLS=$toolsText>");
  }
}

// everything ok
$smarty = new CantrSmarty();
$smarty->assign("object_id", $object->getId());
$smarty->displayLang("form.promptrecycling.tpl", $lang_abr);
