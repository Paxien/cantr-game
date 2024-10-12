<div class="page">
<table>
<tr>
<td colspan="2" style="padding:12px">[$charsettings_description_3]<br></td>
</tr>
<tr style="vertical-align: top">
 <td>
    <script type="text/javascript">
        
      var groupCounter = {$filters|@count};
      var filters = new Object();
      var filterNames = new Object();
      var selectedGroup = -1;
        
    {foreach $filters as $key => $item}
      {if $item@first}
        selectedGroup = {$key};
      {/if}
      filters[{$key}] = new Array({$item->groups}, -1);
      filterNames[{$key}] = '{$item->name}';
    {/foreach}
    </script>
    <div class="toolbarPanel" style="width:160px; height:21px;">
      <img src="[$_IMAGES]/button_small_knock.gif" style="width:21px;height:21px;" title="[$charsettings_button_1]" onClick="newFilter()">
      
      <img src="[$_IMAGES]/button_small_edit.gif" id="edit_button" style="width:21px;height:21px;" title="[$charsettings_button_2]" onClick="editFilterName( selectedGroup, '[$charsettings_message_1]' )">
      <img src="[$_IMAGES]/button_small_end.gif" id="remove_button" style="margin-left:4px;width:21px;height:21px;cursor:default" title="[$charsettings_button_3]" onClick="removeFilter( selectedGroup, '[$charsettings_message_2]' );">
    </div>
    <div id="divlist" class="cantrdivlist" style="width:195px;height:265px;">
       <div class="greenListItem" style="display:none" id="button_to_copy">
          <label onMouseDown="return false;">to copy</label>
        </div>
      {foreach $filters as $key => $item}
        <div class="greenListItem{if $smarty.foreach.filters.first}active{/if}" id="button_{$key}"
          onClick="loadFilter( {$key} );">
        <label onMouseDown="return false;">{$item->name}</label>
        </div>

      {/foreach}
    </div>

  </td>
  <td style="padding-top:7px;">
  <fieldset style="display:none" id="eventGroupPanel">
    <legend style="color:white">[$charsettings_description_2]</legend>
      <div><label style="cursor:pointer" onMouseDown="return false;"><input type="checkbox" id="select_all" />[$select_all]</label></div>
     <div style="float:left">
    {foreach $eventTypeGroups as $key => $item}
      {if ( ($key + 1) % $_COLUMN_SIZE ) == 0 }
      </div>
      <div>
      {/if} 
        <label style="cursor:pointer" onMouseDown="return false;"><input type="checkbox" class="selectFilter" id="group_id_{$item->id}" name="eventGroup[]" />{$item->name}</label><br>
      {/foreach} 
     </div>
  </fieldset>
   </td>
</tr>
</table>
</div>
  
<script type="text/javascript">
  loadFilter( selectedGroup );
  initializeFiltersForm();
  updateToolbar();
</script>
