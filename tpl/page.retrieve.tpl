{include file="template.title.[$lang].tpl" title="[$title_retrieve]"}

<div class="page" style="overflow:auto">
  <p style="display: table;">
    <a href="index.php?page={$upLink}" style="padding:3px"><img src="[$_IMAGES]/button_small_up.gif" title="Go up in storage hierarchy"></a>
    <span style="display: table-cell;vertical-align: middle;">
      [$page_retrieve_looking_into]
      {strip}
        {foreach from=$storageHierarchy item=storage name=storages}
          {$storage->transfer}{if not $smarty.foreach.storages.last} -> {/if}
        {/foreach}
      {/strip}
    </span>
  </p>
  {if $seals}
    <p>
      [$form_envelope_broken_seals]:
      <ul class="plain" style="margin-bottom:20px;">
        {foreach from=$seals item=seal}
          <li><img src="[$_IMAGES]/sealwax.png" align="absmiddle" title="[$seal_description]" /> <span style="color:yellow;">{$seal}</span></li>
        {/foreach}
      </ul>
    </p>
  {/if}
  {if !$canManipulate}
    <p>
      <CANTR REPLACE NAME={$cannotManipulateInfo}>
    </p>
  {/if}
  <input id="storage_id" type="hidden" value="{$storage_id}">
</div>

<div class="page-left">
<table id="objectsList">
  <tbody>
  {foreach from=$objects item=obj}
    <tr>
      <td id="object_{$obj.id}" class="obj_object" data-amount="{$obj.amount}" data-unit-weight="{$obj.unitWeight}"
          data-is-quantity="{$obj.isQuantity}">
        <table>
          <tr>
            <td class="action-buttons action-buttons-{$obj.buttons|count}">
              {foreach $obj.buttons as $it}
                <form method="post" action="index.php?page={$it.page}">
                  {foreach from=$it.inputs item=hidval key=hkey}
                    <input type="hidden" name="{$hkey}" value="{$hidval}"/>
                  {/foreach}
                  <input class="action_{$it.page|regex_replace:"/&.*/":""}" type="image" src="[$_IMAGES]/button_small_{$it.img}.gif"
                         title="<CANTR REPLACE NAME={$it.img_title}>"/>
                </form>
              {/foreach}
            </td>
            <td class="obj_name">
              {$obj.name}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
</div>

{if $canManipulate && ($canRetrieveAll || $canRemoveDuplicates || $canBeReordered)}
  <div class="page" style="text-align:right;">
    {if $canRetrieveAll}
      <a href="index.php?page=retrieve&object_id={$storage_id}&data=yes&retrieve_all=1" class="button_charmenu basic_toolbar"
         id="retrieve_all_button">[$take_all_button]</a>
    {/if}
    {if $canRemoveDuplicates}
    {if $removed > 0}
    <CANTR REPLACE NAME=remove_duplicates_info NUMBER={$removed}>
      {else}
      <a href="index.php?page=remove_note_duplicates&object_id={$storage_id}" class="button_charmenu basic_toolbar">[$remove_note_duplicates]</a>
      {/if}
      {/if}
      {if $canBeReordered}
        <span id="resetReordering" class="button_charmenu basic_toolbar" style="padding:3px;margin:5px">Reset to default</span>
        <span id="startReordering" class="button_charmenu basic_toolbar" style="padding:3px;margin:5px">Change order</span>
        <span id="cancelReordering" class="button_charmenu" style="padding:3px;margin:5px">Cancel</span>
        <span id="confirmReordering" class="button_charmenu" style="padding:3px;margin:5px">Confirm</span>
      {/if}
  </div>
{/if}


<script type="text/javascript" src="[$JS_VERSION]/js/func.objects_inventory.js"></script>
<script type="text/javascript" src="[$JS_VERSION]/js/page.retrieve.js"></script>
<script type="text/javascript" src="[$JS_VERSION]/js/libs/jsonTable.js"></script>
