{include file="template.title.[$lang].tpl" title="[$title_adjust_speed]"}

<form method=post action="index.php?page=speed">
<div class="page">
<table>
  <tr>
    <td colspan=2>
      [$page_adjust_speed_1]<br><br>
    <td>
  </tr>
  <tr>
    <td width=300>
      [$form_new_speed]:
    </td>
    <td>
      <input type=text name="newspeed" value="{$CURRSPEED}"><br><br>
    </td>
  </tr>
  <tr>
    <td colspan=2 align=center>
      <input type=hidden name=data value=yes>
      <input type=image src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
    </td>
  </tr>

</table>
</div>
</form>
