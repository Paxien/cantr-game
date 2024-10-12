{include file="template.title.[$lang].tpl" title="[$title_fill_envelop]"}

<form method="post" action="index.php?page=multinotes">
<div class="page">
  <table>
    <tr>
      <td width=200>
        [$form_select_envelop]:
      </td>
      <td>
        <select name="envelop">
        {foreach $envelopes as $envelope}
          <option value="{$envelope->id}">{$envelope->title} ({$envelope->name})</option>
        {/foreach}
        </select>
        <input type=hidden name="data" value="yes">
        <input type=hidden name="note" value="{$notes}">
      </td>
    </tr>
  </table>
  <div class="centered">
    <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_inventory]"></a>
    <input type=image src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
  </div>
</div>
</form>
