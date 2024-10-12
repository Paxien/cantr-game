<?php

include 'settingsdef.inc.php';
$db = Db::get();
$eventTypeGroups = array();
$stm = $db->prepare("SELECT data FROM settings_chars WHERE person = :charId AND type = :type");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("type",  CharacterSettings::ACTIVITY_EVENT_FILTER);
$nowActive = $stm->executeScalar();
$nowActive = explode(',', $nowActive);

$stm = $db->query("SELECT type, description FROM events_types ORDER BY description");

$groups = array();
foreach ($stm->fetchAll() as $eventType) {
  if (isset($_EXCLUDED_EVENT_TYPES[$eventType->type])) {
    continue;
  }

  if (isset($_EVENT_TYPES_GROUPS[$eventType->type])) {
    $groupName = $_EVENT_TYPES_GROUPS[$eventType->type]->name;
    $id = $_EVENT_TYPES_GROUPS[$eventType->type]->id;
    if (isset($groups[$id])) {
      continue;
    }
    //grouped events
    $groupData = new StdClass();
    $groupData->name = $groupName;
    $groupData->checked = in_array($eventType->type, $nowActive);
    $groups[$id] = $groupData;
  }
}

foreach ($groups as $groupId => $groupData) {
  $fakeType = new StdClass();
  $fakeType->type = _EVENT_GROUP_PREFIX . "$groupId";

  $uniqueName = str_replace(' ', '_', $groupData->name);
  $translateTag = new tag("<CANTR REPLACE NAME=events_group_$uniqueName>");
  $translatedGroupName = $translateTag->interpret();
  if ($translatedGroupName) {
    $groupData->name = $translatedGroupName;
  }

  $fakeType->description = $groupData->name;
  $fakeType->selected = $groupData->checked;
  $eventTypeGroups [] = $fakeType;
}
sort($eventTypeGroups);

$smarty->assign("eventTypeGroups", $eventTypeGroups);