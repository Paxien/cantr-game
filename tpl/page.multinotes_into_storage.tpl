{include file="template.title.[$lang].tpl" title="[$title_fill_envelop]"}

<form method=post action="index.php?page=multinotes_into_storage">
  <div class="page">
    <table>
      <tr>
        <td width=200>
          [$js_form_store_into]
        </td>
        <td>
          <select name="storage">
            <optgroup label="[$js_form_storages_in_inventory]">
            {foreach $storagesInInventory as $storage}
              <option value="{$storage.id}" {if $storage.description}title="{$storage.description|strip_tags}"{/if}>{$storage.name}{if $storage.description} *{/if}</option>
            {/foreach}
            </optgroup>
            <optgroup label="[$js_form_storages_on_ground]">
            {foreach $storagesOnGround as $storage}
              <option value="{$storage.id}" {if $storage.description}title="{$storage.description|strip_tags}"{/if}>{$storage.name}{if $storage.description} *{/if}</option>
            {/foreach}
            </optgroup>
          </select>
          <input type=hidden name="data" value="yes">
          <input type=hidden name="notes" value="{$notes}">
        </td>
      </tr>
    </table>
    <div class="centered">
      <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_inventory]"></a>
      <input type=image src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
    </div>
  </div>
</form>