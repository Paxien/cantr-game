{include file="template.title.[$lang].tpl" title="[$title_build_menu]"}

<form method="post" action="index.php?page=build" {if $isDescribable}onsubmit="return document.getElementById('descArea').value.length <= {$DESC_MAX_LEN}"{/if}>
  <div class="page">
  <table>
    <tr>
      <td width="200">
        [$build_object_name]:
      </td>
      <td>
        {$objectNameTag}
      </td>
    </tr>
    {if $objectImage}
      <tr>
        <td style="vertical-align:top">
          [$build_object_image]:
        </td>
        <td>
          <img src="[$_IMAGES_OBJECTS]{$objectImage}" alt="{$objectNameTag}">
        </td>
      </tr>
    {/if}
    <tr>
      <td>
        [$days_needed]
      </td>
      <td>
        {$days}
      </td>
    </tr>
    {if $raws}
      <tr>
        <td style="vertical-align:top">
          [$raws_needed]
        </td>
        <td>
          {foreach from=$raws key=rawName item=rawAmount}
            {$rawAmount} [$grams_of] <CANTR REPLACE NAME=raw_{$rawName}><br>
          {/foreach}
        </td>
      </tr>
    {/if}
    {if $objects}
      <tr>
        <td style="vertical-align:top">
          [$form_objects]
        </td>
        <td>
          {foreach from=$objects key=objectTag item=number}
            {$objectTag} (<CANTR REPLACE NAME=items_needed AMOUNT={$number}>)<br>
          {/foreach}
        </td>
      </tr>
    {/if}
    {if $tools}
      <tr>
        <td style="vertical-align:top">
          [$tools_needed]
        </td>
        <td>
          {foreach from=$tools item=toolName}
            {$toolName}<br>
          {/foreach}
        </td>
      </tr>
    {/if}
    {if $machines}
      <tr>
        <td style="vertical-align:top">
          [$machines_needed]
        </td>
        <td>
          {foreach from=$machines item=machineTag}
            {$machineTag}<br>
          {/foreach}
        </td>
      </tr>
    {/if}
  {foreach from=$items item=item}
    <tr>
      <td style="padding-top:20px;padding-bottom:5px" colspan="2">
        [$page_build_menu_1]
      </td>
    </tr>
    <tr>
      <td style="padding-bottom:20px">
        <CANTR REPLACE NAME=form_{$item.b}>:
      </td>
      <td style="padding-bottom:20px">
        <input style="width:90%" name="{$item.a}">
      </td>
    </tr>
  {/foreach}
  {if $massProduction}
    <tr>
      <td style="padding-top:20px" colspan="2">
        [$mass_production_info]
      </td>
    </tr>
    <tr>
      <td>
        [$mass_production_number]
      </td>
      <td>
        <div id="slider" style="width:60%;display:inline-block"></div>
        <select name="number" id="number" style="margin-left:10px;display:inline-block">
        {foreach from=$ALLOWED_NUMBERS item=num}
          <option value="{$num}">{$num}</option>
        {/foreach}
        </select>
      </td>
    </tr>
  {/if}
  {if $isDescribable}
    <tr>
      <td style="padding-top:20px" colspan="2">
        [$build_menu_enter_object_desc]
      </td>
    </tr>
    <tr>
      <td style="padding-bottom:5px" colspan="2">
        <label style="cursor:pointer;">
          <input type="checkbox" value="1" id="useCustomDesc" name="useCustomDesc">[$build_menu_set_custom_desc]
        </label>
        <div id="customDesc">
          <textarea id="descArea" rows="3" name="description"></textarea><br>
          <span id="charsLeft">{$DESC_MAX_LEN}</span> [$desc_chars_left]
          <a href="index.php?page=objdesc_guide">&#91;[$link_objdesc_guide]&#93;</a>
        </div>
      </td>
    </tr>
  {/if}
    <tr>
       <td align="center" colspan="2">
         {if $show_allocation}
         [$form_resource_allocation_choice]
         <div>
           <div class="resourceAllocationColumn">
             <label><input type="radio" name="resource_allocation" value="none"> [$form_resource_allocation_none]</label>
           </div><div class="resourceAllocationColumn">
             <label><input type="radio" name="resource_allocation" value="full" checked> [$form_resource_allocation_full]</label>
           </div><div class="resourceAllocationColumn">
             <label><input type="radio" name="resource_allocation" value="regardless"> [$form_resource_allocation_regardless]</label>
           </div>
         {else}
            <input type="hidden" name="resource_allocation" value="none">
         {/if}
       </td>
    </tr>
    <tr>
      <td align="center" colspan="2">
        <input type="hidden" name="data" value="yes">
        <input type="hidden" name="targetcontainer" value="{$targetcontainer}">
        <input type="hidden" name="objecttype" value="{$objecttype}">
        <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
      </td>
    </tr>
  </table>
  </div>
</form>

{if $isDescribable}
<script type="text/javascript">
  var CHAR_LIMIT = {$DESC_MAX_LEN};
  {literal}
  $(function() {
    $("#customDesc").toggle($("#useCustomDesc").prop("checked"));
    $("#useCustomDesc").click(function() {
      $("#customDesc").toggle($("#useCustomDesc").prop("checked"));
    });

    $("#descArea").keyup(function() {
      $("#charsLeft").text(CHAR_LIMIT - $("#descArea").val().length);
    });
  });
  {/literal}
</script>
{/if}
{if $massProduction}
<script type="text/javascript">
  var maxNumber = {$MASS_PRODUCTION_MAX};
  {literal}
  $(function() {
    var prodNum = $("#number");
    var slider = $("#slider");
    slider.slider({
      min: 1,
      max: maxNumber,
      range: "min",
      value: prodNum.val(),
      slide: function( event, ui ) {
        prodNum.val(ui.value);
      }
    });
    prodNum.change(function() {
      slider.slider("value", prodNum.val());
    });
  });
  {/literal}
</script>
{/if}
