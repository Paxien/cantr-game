{include file="template.title.[$lang].tpl" title="[$title_store_material]"}

<div class="page">
  <form method="post" action="index.php?page=store">
    <table>
      <tr>
        <td width="350" style="vertical-align:top;padding-right:30px;">[$page_store_1]<br>
            {if $invStorage|@count > 0}
              [$page_store_2]
              <ul class="storagesList" style="list-style-type:none;margin:3px;">
              {foreach from=$invStorage key=sid item=obj}
                <li>
                  <label style="cursor:pointer;">
                    <input type="radio" name="target" value="{$sid}">{$obj.name} ({if $obj.space == 0}[$page_store_3]{elseif $obj.space == "locked"}[$lock_locked]{else}{$obj.space}{/if}){$obj.description}
                  </label>
                </li>
              {/foreach}
              </ul>
            {/if}
            {if $groundStorage|@count > 0}
              [$page_store_4]
              <ul class="storagesList" style="list-style-type:none;margin:3px;">
              {foreach from=$groundStorage key=sid item=obj}
                <li>
                  <label style="cursor:pointer;">
                    <input type="radio" name="target" value="{$sid}">{$obj.name} ({if !$obj.space}[$page_store_3]{elseif $obj.space == "locked"}[$lock_locked]{else}{$obj.space}{/if}){$obj.description}
                  </label>
                </li>
              {/foreach}
              </ul>
            {/if}
        </td>
        <td width="350">
          
            {if $isamount}<CANTR REPLACE NAME=page_store_you_have MATERIAL={$name}> <br>
            {else}
            <CANTR REPLACE NAME=page_store_object WEIGHT={$max} OBJECT={$name}> <br>
            {/if}
            [$page_store_free_space]: <span id="max_amt">- </span>g <br><br>
            {if $isamount}
              [$page_store_select_amount]: <br>
              [$form_amount]: <input type="text" id="amount" name="amount" value="0" size="12"> <input
                type="button" id="maxButton" class="button_charmenu" value="max">
            {else}
              <input type="hidden" name="amount" value="{$max}">
            {/if}
            <div style="text-align: center;margin-top:10px;">
            <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"/></a>
            <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$button_continue]">
          </div>
          <input type="hidden" name="object_id" value="{$object_id}">
          <input type="hidden" name="data" value="yes">
        </td>
      </tr>
    </table>
  </form>
</div>

<script type="text/javascript">
  var list = [];
  var max_res = {$max};
  var max_amount = 0;
  {foreach from=$invStorage key=kid item=obj}
    list[{$kid}] = {if $obj.space != "locked"}{if $obj.space < $canCarry || $rawsInInventory}{$obj.space}{else}{$canCarry}{/if}{else}0{/if};
  {/foreach}
  {foreach from=$groundStorage key=kid item=obj}
    list[{$kid}] = {if $obj.space != "locked"}{$obj.space}{else}0{/if};
  {/foreach}
</script>

<script type="text/javascript" src="[$JS_VERSION]/js/page.store.js"></script>
