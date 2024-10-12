{include file="template.title.[$lang].tpl" title="$rawtitle"}
<form method="post" action="index.php?page=dig">
  <input type=hidden name="rawtype" value="{$rawtype}">
  <input type=hidden name="data" value="yes">
  <div class="page">
  <table>
    <tr>
      <td colspan="2">
        [$page_dig_1]<br>
      {foreach from=$tools item=tool}
        {$tool}<br>
      {/foreach}
      </td>
    </tr>
    <tr>
      <td width=240>
        <br><CANTR REPLACE NAME=form_dig_amount ACTION={$rawaction}>:
      </td>
      <td>
        <br><input type=text name="amount" style="width:100%" value="{$amount}">
        [$gram]
      </td>
    </tr>
    <tr>
      <td>
        <br>[$form_dig_repeat]:
      </td>
      <td>
        <br><input type="text" name="repeat" style="width:100%" value="0">
      </td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <br>
        <a href="index.php?page=char.description"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"/></a>
        <input type=image src="[$_IMAGES]/button_forward2.gif" title="[$button_continue]">
      </td>
    </tr>
  </table>
  </div>
</form>
