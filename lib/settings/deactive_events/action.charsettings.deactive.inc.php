<?php
// TODO WTF?!?!
include 'settingsdef.inc.php';
$activityEvents = [];
foreach($GLOBALS as $var_name => $value) {
  if( strpos( $var_name, _EVENT_GROUP_PREFIX ) === 0 ) {

    $groupId = intval( substr( $var_name, strlen( _EVENT_GROUP_PREFIX ) ) );

    $group = $_EVENT_TYPES_GROUPS_ORGINAL[ $groupId ];
    foreach( $group as $data ) {
      $activityEvents[] = $data->type;
    }
  }

  if( strpos( $var_name, _EVENT_SINGLE_PREFIX ) === 0 ) {

    $eventTypeId = substr( $var_name, strlen( _EVENT_SINGLE_PREFIX ) );

    $activityEvents[] = $eventTypeId;
  }
}

$activityEvents = implode(",", $activityEvents);

$db = Db::get();

$stm = $db->prepare( "DELETE FROM settings_chars WHERE person = :charId AND type = :type");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("type", CharacterSettings::ACTIVITY_EVENT_FILTER);
$stm->execute();

$stm = $db->prepare( "INSERT INTO settings_chars(person, type, data) VALUES(:charId, :type, :data)" );
$stm->bindInt("charId", $char->getId());
$stm->bindInt("type", CharacterSettings::ACTIVITY_EVENT_FILTER);
$stm->bindStr("data", $activityEvents);
$stm->execute();
