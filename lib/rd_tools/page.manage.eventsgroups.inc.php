<?php

define("_PAGE_WIDTH", 800);
define("_COLUMN_WIDTH", (_PAGE_WIDTH / 2) - 40);

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_EVENT_GROUPS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

show_title("MANAGE EVENTS GROUPS");
$db = Db::get();
$hEventTypesGroups = $db->query("select eg.* " .
  "FROM events_groups eg LEFT JOIN events_types et ON eg.id=et.group " .
  "GROUP BY eg.id ORDER BY count(*) DESC");

$eventGroups = array();
while (list($id, $name) = $hEventTypesGroups->fetch(PDO::FETCH_NUM)) {
  $item = new StdClass();
  $item->name = $name;
  if (empty($id)) $id = -1;
  $eventGroups[$id] = $item;
}


$hEventTypes = $db->query("SELECT * FROM events_types WHERE events_types.type > 0");
$eventGroups[-1] = new stdClass();
$eventGroups[-1]->name = '[Not in group]';
$eventGroups[-1]->events = array();
while (list($type, $name, $group) = $hEventTypes->fetch(PDO::FETCH_NUM)) {
  $data = new StdClass();
  $data->name = $name;
  $data->type = $type;
  if (empty($group)) $group = -1;
  $eventGroups[$group]->events [] = $data;
}
?>

<script type="text/javascript">
 function getNewGroupName( oldName ) {
   var newName = prompt("What is your name?", oldName );
   
   if( newName == null || newName == oldName ) return false;
   
   var el = document.getElementById( 'hiddenNewGroupName' );
   el.value = newName;
   return true;
 }
</script>

<form action="index.php?page=manage_events" method="post">

<input type="hidden" value="" id="hiddenNewGroupName" name="newgroupname"/>  
<div style="width:<?php echo _PAGE_WIDTH ?>px;margin-right:auto; margin-left:auto;">
<p>You can create new groups, or edit existing. To give translate to event group, that will be visible in player options, you should use tag: events_group_{groupid}.
You can see group id in square brackets.</p>
<p>Select events that you want move to diffrent group: </p>

<div>
<?php
$fieldIndex = 0;
$float = "left";
foreach( $eventGroups as $id => $item ) {
$fieldIndex++;
//if( ( $fieldIndex % _COLUMNS_COUNT ) == 0 ) echo "</td><td>";
    
?>
  <fieldset style="width:<?php echo _COLUMN_WIDTH . "px;float:$float;" ?>margin:6px;">
  
  <legend>
    <?php if( $id != -1 ) { ?>                                                                                                                           
    <input type="image" src="<?php echo _IMAGES ?>/button_small_end.gif" name="remove_group" value="<?php echo $id ?>" width="24" height="24" title="Remove group" onClick="return confirm('You are sure?')"/>
    <input type="image" src="<?php echo _IMAGES ?>/button_small_edit.gif" name="rename_group" value="<?php echo $id ?>" width="24" height="24" title="Change group name" onClick="return getNewGroupName( '<?php echo $item->name ?>' )"/>
    <?php } ?>
    
    <?php echo "[$id] $item->name" ?>
  </legend>
  
<?php
  if( $item && $item->events ) {
    foreach( $item->events as $entry ) {
?>
      <label><input type="checkbox" name="event_<?php echo $entry->type ?>"> <?php echo $entry->name; ?> </label><br>
<?php
    }
  }
?>
  </fieldset>
<?php  
$float = ( $float == "left" ) ? "right" : "left";
}
?>
</div>
<?php show_title ("ACTIONS"); ?>
<div style="float:left;margin-bottom:50px">
  
  <input type="hidden" name="data" value="yes">
  <input type="hidden" name="manage_action" value="move">
    
  <p>
  Move selected eventtypes to group: 
  <select name="targetgroup" size="1">
<?php 
  foreach( $eventGroups as $id => $item )
    echo "<option value=\"$id\">$item->name</option>\n";
?>
  </select>
  <input type="submit" name="move_action" value="Move"/>
  
  </p>
  <p>
  Create new eventtype group: <input type="text" name="newgroup_name"  value="Unique group name"/><input type="submit"  name="newgroup_action" value="Create"/>
  </p>
</div>

</div>

<div style="width: 100%; position: fixed; bottom: 10px; left: 10px;">
   <a href="index.php?page=player">             
   <img src="<?php echo _IMAGES; ?>/button_back2.gif" title="Back"></a>
</div>


</form>
  
