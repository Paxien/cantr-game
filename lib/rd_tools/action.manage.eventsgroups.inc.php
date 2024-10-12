<?php

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_EVENT_GROUPS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$move_action = HTTPContext::getString('move_action');
$rename_group = HTTPContext::getString('rename_group');
$newgroup_action = HTTPContext::getString('newgroup_action');
$newgroup_name = HTTPContext::getString('newgroup_name');
$remove_group = HTTPContext::getString('remove_group');

$db = Db::get();

if (isset($move_action)) {
  $targetgroup = HTTPContext::getInteger('targetgroup');
  if ($targetgroup == -1) {
    $targetgroup = null;
  }
  $eventsToMove = array();
  foreach ($GLOBALS as $var_name => $value) {
    if (strpos($var_name, "event_") === 0) {

      $evTypeId = intval(substr($var_name, strlen("event_")));
      array_push($eventsToMove, $evTypeId);
    }
  }

  if (count($eventsToMove) > 0) {
    $stm = $db->prepareWithIntList("UPDATE events_types SET `group` = :group WHERE type IN (:ids)", [
      "ids" => $eventsToMove,
    ]);
    $stm->bindInt("group", $targetgroup, true);
    $stm->execute();
  }
} elseif (isset($newgroup_action)) {
  $stm = $db->prepare("SELECT COUNT(*) FROM events_groups WHERE name = :name");
  $stm->bindStr("name", $newgroup_name);
  $alreadyExistingGroups = $stm->executeScalar();
  if ($alreadyExistingGroups == 0) {
    $stm = $db->prepare("INSERT INTO events_groups (name) VALUES (:name)");
    $stm->bindStr("name", $newgroup_name);
    $stm->execute();
  }
} elseif (isset($rename_group)) {
  $stm = $db->prepare("UPDATE events_groups SET `name` = :name WHERE `id` = :id");
  $stm->bindStr("name", $newgroup_name);
  $stm->bindInt("id", $rename_group);
  $stm->execute();
} elseif (isset($remove_group)) {
  $stm = $db->prepare("UPDATE events_types SET `group` = NULL WHERE `group`= :id");
  $stm->bindInt("id", $remove_group);
  $stm->execute();

  $stm = $db->prepare("DELETE FROM events_groups WHERE `id` = :id");
  $stm->bindInt("id", $remove_group);
  $stm->execute();
}

redirect("manage_events");