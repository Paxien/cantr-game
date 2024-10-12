<?php
// SANITIZE INPUT
$material = HTTPContext::getInteger('material');
$amount = HTTPContext::getInteger('amount');
$objecttype_id = HTTPContext::getInteger('objecttype_id');
$requirements = $_REQUEST['requirements'];
$multiply = HTTPContext::getInteger('multiply');
$project_name = $_REQUEST['project_name'];
$maxparticipants = HTTPContext::getInteger('maxparticipants');
$skill = HTTPContext::getInteger('skill');
$automatic = HTTPContext::getInteger('automatic');
$machine_id = HTTPContext::getInteger('machine_id');
$description = HTTPContext::getRawString("description");

/********* CHECKING WHETHER PLAYER HAS ACCESS TO THIS PAGE **************/

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_MACHINE_PROJECTS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

  $db = Db::get();
	$result_part[0] = $material;
	$result_part[1] = $amount;
	
	$result = implode (":", $result_part);
	
	$stm = $db->prepare("UPDATE machines SET type = :objectType, requirements = :requirements, result = :result, multiply = :multiply,
    name = :name, max_participants = :maxParticipants, skill = :skill, automatic = :automatic WHERE id = :id");
	$stm->execute([
    "objectType" => $objecttype_id,
    "requirements" => $requirements,
    "result" => $result,
    "multiply" => $multiply,
    "name" => $project_name,
    "maxParticipants" => $maxparticipants,
    "skill" => $skill,
    "automatic" => $automatic,
    "id" => $machine_id,
  ]);

  $playerInfo = Request::getInstance()->getPlayer();
  $machineName = ObjectType::loadById($objecttype_id)->getName();

	$rawName = ObjectHandler::getRawNameFromId($material);

	$stm = $db->prepare("SELECT name FROM state_types WHERE id = :skill");
	$stm->bindInt("skill", $skill);
	$stateName = $stm->executeScalar();
	
	$message = "{$playerInfo->getFullName()} changed the following in the machines database:\n\n";
	$message .= "ID:\t$machine_id\n";
	$message .= "Machine:\t$machineName\n";
	$message .= "Requirements:\t$requirements\n";
	$message .= "Automatic:\t$automatic (0 = manual; 1 = automatic; 2 = both)\n";
	$message .= "Result:\t$amount grams of $rawName\n";
	$message .= "Multiply:\t$multiply\n";
	$message .= "Max participants:\t$maxparticipants\n";
	$message .= "Skill:\t$stateName\n";
	$message .= "Project name:\t$project_name\n";
	$message .= "\nReason:\n\n";
	$message .= "$description\n\n";
	$message .= "(This is an automatically created message.)";

	$env = Request::getInstance()->getEnvironment();
	$mailService = new MailService("Resources Department", $GLOBALS['emailResources']);
	$mailService->sendPlaintext($GLOBALS['emailResources'], $env->getFullName() ." Machine project on $machineName changed", $message);

redirect("managemachines");
