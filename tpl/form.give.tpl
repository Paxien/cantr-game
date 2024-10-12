{include file="template.title.[$lang].tpl" title="[$title_give]"}

<center>
  <form method=post action="index.php?page=give">
  <table>
    <tr>
      <td width="300">
        [$form_give_receiver]:
      </td>
      <td width="400">
        <select name="receiver">
        {foreach from=$receivers item=receiver}
          <option value="{$receiver.id}">{$receiver.name}</option>
        {/foreach}
        </select>
      </td>
    </tr>
    {if $is_quantity}
      <tr>
        <td colspan=2>
          [$page_drop_1]<br>
        </td>
      </tr>
      <tr>
        <td>
            [$form_give_amount]:
        </td>
        <td>
          <input type=text size=20 name="amount" value="{$WEIGHT}">
        </td>
      </tr>
    {/if}
    <tr>
      <td align=right>
        <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]"></a>
      </td>
      <td align=left>
        <input type=hidden name="object_id" value="{$object_id}">
        <input type=image src="[$_IMAGES]/button_forward2.gif" title="[$button_continue]" >
      </td>      
    </tr>
  </table> 
  </form>
</center>

