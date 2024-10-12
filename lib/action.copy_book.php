<?php

$objectId = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($objectId);
  $objectType = ObjectType::loadById($object->getType());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

// check accessibility
if (!$char->hasInInventory($object)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$object->hasAccessToAction("copy_book")) {
  CError::throwRedirect("char.inventory", "error_not_accessible");
}

// It works ONLY for notes

$reqNeeded = $objectType->getBuildRequirements();

$requirements = Parser::rulesToArray($objectType->getBuildRequirements());
$turnsNeeded = 1;
if (array_key_exists("days", $requirements)) {
  $turnsNeeded = $requirements["days"] * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
}
$db = Db::get();
$stm = $db->prepare("SELECT utf8title FROM obj_notes WHERE id = :id");
$stm->bindInt("id", $object->getTypeid());
$bookTitle = $stm->executeScalar();
$projectName = "<CANTR REPLACE NAME=project_copying_book TITLE=" . urlencode($bookTitle) . ">";
$encodedTitle = urlencode($bookTitle);
$bookWeight = $objectType->getUnitWeight();

$result  = "obj_notes.add:setting>1,utf8title>$encodedTitle;";
$result .= "objects.add:location>0,person>var>initiator,type>" . $objectType->getId() . ",typeid>var>firstid,weight>$bookWeight,setting>1;";
$result .= "seals.add:note>var>id[1],name>,anonymous>1,broken>0"; // TODO it's really bad, find a way to execute predefined actions

$notes = CObject::storedIn($object)->type(ObjectConstants::TYPE_NOTE)->findAll();
foreach ($notes as $note) {
  $objNotesId = $note->getTypeid();
  $result .= ";objects.add:location>0,person>0,attached>var>id[1],type>" . ObjectConstants::TYPE_NOTE . ",typeid>$objNotesId,weight>0,setting>1,ordering>" . $note->getOrdering();
}

$generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
$typeSub = new ProjectType(ProjectConstants::TYPE_MANUFACTURING, $objectType->getId(),
  $objectType->getProductionSkill(), ProjectConstants::PROGRESS_MANUAL, 4, 0);
$requirementSub = new ProjectRequirement($turnsNeeded, $reqNeeded);
$outputSub = new ProjectOutput( 0, $result);

$projectObject = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
$projectObject->saveInDb();

redirect("char.inventory");
