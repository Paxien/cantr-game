<?php

// SANITIZE INPUT
$kopy = HTTPContext::getInteger('kopy');

$plr = Request::getInstance()->getPlayer();
if (!$plr->hasAccessTo(AccessConstants::MANAGE_MACHINE_PROJECTS)) {
	CError::throwRedirectTag("player", "error_manage_machines_denied");
}
$db = Db::get();
$stm = $db->prepare("SELECT * FROM machines WHERE id = :id");
$stm->bindInt("id", $kopy);
$stm->execute();
$machine_info = $stm->fetchObject();

$stm = $db->prepare("INSERT INTO machines (type,requirements,result,multiply,name,max_participants,skill)
	VALUES (:type, :requirements, :result, :multiply, :name, :maxParticipants, :skill)");
$stm->execute([
  "type" => 0, "requirements" => $machine_info->requirements, "result" => $machine_info->result, "multiply" => $machine_info->multiply,
  "name" => $machine_info->name, "maxParticipants" => $machine_info->max_participants, "skill" => $machine_info->skill,
]);

$id = $db->lastInsertId();

redirect("managemachines", ["machine" => $id]);
