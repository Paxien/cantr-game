{include file="template.title.[$lang].tpl" title="[$title_inventory]"}

<script type="text/javascript" src="[$JS_VERSION]/js/notesManagement.js"></script>

  <div class="page" style="text-align: center;" id="navigationSubpanel">
    <a href="index.php?page=char.inventory&show_items=1" class="button_charmenu{if $ShowItems == 1}active{/if}">
      [$inventory_show_notes]
    </a>
    <a href="index.php?page=char.inventory&show_items=2" class="button_charmenu{if $ShowItems == 2}active{/if}">
      [$inventory_show_raws]
    </a>
    <a href="index.php?page=char.inventory&show_items=3" class="button_charmenu{if $ShowItems == 3}active{/if}">
      [$inventory_show_coins]
    </a>
    <a href="index.php?page=char.inventory&show_items=4" class="button_charmenu{if $ShowItems == 4}active{/if}">
      [$inventory_show_keys]
    </a>
    <a href="index.php?page=char.inventory&show_items=5" class="button_charmenu{if $ShowItems == 5}active{/if}">
      [$inventory_show_all]
    </a>
  </div>

  {if !$multiple}
    <div class="page-left">
    <table>
    {foreach from=$objects item=obj}
      <tr>
        <td id="object_{$obj.id}" class="obj_object" data-amount="{$obj.amount}" data-unit-weight="{$obj.unitWeight}" data-is-quantity="{$obj.isQuantity}">
          <table>
            <tr>
              <td class="action-buttons action-buttons-{$obj.buttons|count}">
              {foreach $obj.buttons as $it}
                <form method="post" action="index.php?page={$it.page}">
                  {foreach from=$it.inputs item=hidval key=hkey}
                    <input type="hidden" name="{$hkey}" value="{$hidval}" />
                  {/foreach}
                  <input class="action_{$it.page|regex_replace:"/&.*/":""}" type="image" src="[$_IMAGES]/button_small_{$it.img}.gif" title="<CANTR REPLACE NAME={$it.img_title}>" />
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
    </table>
    {if $ShowItems == 1}
      <div class="centered">
        <form method="post" action="index.php?page={$page}">
          <input type="hidden" name="show_items" value="1" />
          <input type="hidden" name="multiple" value="1" />
          <input type="submit" value="[$inventory_multiple_notes]" class="button_charmenu" />
        </form>
      </div>
    {/if}

  </div>

  {if $isJsInterface}
    <script type="text/javascript">
      var isInventory = true;
    </script>
    <script type="text/javascript" src="[$JS_VERSION]/js/func.objects_inventory.js"></script>
    <script type="text/javascript" src="[$JS_VERSION]/js/page.inventory.js"></script>
    <script type="text/javascript" src="[$JS_VERSION]/js/libs/jsonTable.js"></script>
    <script type="text/javascript" src="[$JS_VERSION]/js/page.objects_inventory.shorten_desc.js"></script>
  {/if}


  {/if} {* if not multiple  - END *}

	{if $multiple}
	<form method="post" action="index.php?page=multinotes">
    <div class="page">
      <fieldset style="min-width:100px;float:right;text-align:center;">
        <legend style="text-align:center;">[$multinotes_title]</legend>
        <input type="button" value="[$multinotes_all]" class="button_charmenu" onclick="allNotes()">
        <input type="button" value="[$multinotes_none]" class="button_charmenu" onclick="noNotes()">
        <input type="button" value="[$multinotes_reverse]" class="button_charmenu" onclick="reverseNotes()">
      </fieldset>
      <table>
      {foreach from=$objects item=obj}

      <tr>
        <td>
          <label style="cursor:pointer;">
            <input name="notes[]" value="{$obj.id}" type="checkbox" class="note_ind">{$obj.name}
          </label>
        </td>
      </tr>
      {/foreach}
      </table>
      <p class="centered">
        <button type="submit" style="border: none;background: none" name="noteaction_submit" title="Put selected objects in an envelop" value="fill_envelop"><img src="[$_IMAGES]/button_small_envelop.gif"></button>
        <button type="submit" style="border: none;background: none" name="noteaction_submit" title="Put selected objects in a storage" value="store_in_storage"><img src="[$_IMAGES]/button_small_store.gif"></button>
      </p>
    </div>
	</form>
	{/if}
