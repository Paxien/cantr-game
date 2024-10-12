<?php

// SANITIZE INPUT
$tmp_tool = HTTPContext::getString('tool', null);
if ($tmp_tool && $tmp_tool != 'new') {
  $tmp_tool = HTTPContext::getInteger('tool'); // what does it do?
}
$tool = $tmp_tool; // it's not tool, it's `rawtools` primary key
$rawtype = HTTPContext::getInteger('rawtype');
$perday = HTTPContext::getInteger('perday');
$objectType = HTTPContext::getInteger('object_id');
$description = HTTPContext::getRawString("description");

/********* CHECKING WHETHER PLAYER HAS ACCESS TO THIS PAGE **************/

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_RAW_MATERIALS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$db = Db::get();

/************* STORING THE DATA ******************************************/
	if ($tool == 'new') {
	
		$message = "The following new tool has been assigned to a raw material:\n\n";

    $stm = $db->prepare("INSERT INTO rawtools (tool,rawtype,perday,projecttype) VALUES (:objectType, :rawType, :perDay, :projectType)");
    $stm->execute([
      "objectType" => $objectType,
      "rawType" => $rawtype,
      "perDay" => $perday,
      "projectType" => ProjectConstants::TYPE_GATHERING,
    ]);
	} else {
		$message = "The following tool assignment has been changed:\n\n";
	
		$id = $tool;
		$stm = $db->prepare("UPDATE rawtools SET tool = :objectType, rawtype = :rawType, perday = :perDay, projecttype = :projectType WHERE id = :id");
		$stm->execute([
      "objectType" => $objectType,
      "rawType" => $rawtype,
      "perDay" => $perday,
      "projectType" => ProjectConstants::TYPE_GATHERING,
      "id" => $id,
    ]);
	}

	$rawName = ObjectHandler::getRawNameFromId($rawtype);

	$objectTypeInfo = ObjectType::loadById($objectType);
	
	$message .= "ID:\t$id\n";
	$message .= "Rawtype:\t$rawName\n";
	$message .= "Tool:\t{$objectTypeInfo->getName()}\n";
	$message .= "Amount per day:\t$perday\n";
	$message .= "Comments:\n\n";
	$message .= "$description\n\n";
	$message .= "{$playerInfo->getFullName()}\n\n";
	$message .= "(This is an automatically created message)";

	$mailService = new MailService("Cantr Resources", $GLOBALS['emailResources']);
	$mailService->sendPlaintext($GLOBALS['emailResources'], "Raw material tool assignment change", $message);

redirect("managerawtypes");
