<?php
$_EXCLUDED_EVENT_TYPES = array( 0 );
define( '_EVENT_GROUP_PREFIX', 'eventgroup_');
define( '_EVENT_SINGLE_PREFIX', 'eventid_');

$db = Db::get();
$stm = $db->query("SELECT events_types.type, events_groups.id, events_groups.name
  FROM events_types LEFT JOIN events_groups ON events_types.group = events_groups.id");
$_EVENT_TYPES_GROUPS = array();
while(list($type, $groupId, $groupName) = $stm->fetch(PDO::FETCH_NUM)) {
 if( !is_null( $groupId ) ) {
   $data = new StdClass();
   $data->groupName = $groupName;
   $data->type = $type;
   $_EVENT_TYPES_GROUPS[ $groupId ] []= $data;
 }
}


/////////////////////////////////////////
//prepare table to faster works
/////////////////////////////////////////
$_EVENT_TYPES_GROUPS_COPY = array();
foreach( $_EVENT_TYPES_GROUPS as $groupId => $datas ) {
  foreach( $datas as $data ) {
    $groupData = new StdClass();
    $groupData->name = $data->groupName;
    $groupData->id = $groupId;     
    $_EVENT_TYPES_GROUPS_COPY[ $data->type ] = $groupData;
  }
}
$_EVENT_TYPES_GROUPS_ORGINAL = $_EVENT_TYPES_GROUPS;
$_EVENT_TYPES_GROUPS = $_EVENT_TYPES_GROUPS_COPY;
