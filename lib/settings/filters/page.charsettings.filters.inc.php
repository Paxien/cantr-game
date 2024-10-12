<?php

$db = Db::get();
$stm = $db->prepare("SELECT data FROM settings_chars
  WHERE person = :charId AND type = :type");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("type", CharacterSettings::EVENT_FILTER);
$stm->execute();

$filters = array();
foreach ($stm->fetchScalars() as $filterData) {
  $filter = new stdClass();
  $dArray = explode('|', $filterData);
  $filter->name = $dArray[0];
  $filter->groups = $dArray[1];
  if (!$filter->groups) {
    $filter->groups = '-1';
  }
  $filters[] = $filter;
}

$stm = $db->query("SELECT id, name FROM events_groups");

$eventTypeGroups = $stm->fetchAll();

$smarty->assign('filters', $filters);
$smarty->assign('eventTypeGroups', $eventTypeGroups);
