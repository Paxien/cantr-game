{include file="template.title.[$lang].tpl" title="[$title_take]"}

<center>
<form method="post" action="index.php?page=take">
  <table>
    <tr>
      <td width="200">[$form_carrying]:</td>
      <td width="500">{$charload}g</td>
    </tr>
    <tr>
      <td colspan="2">
        [$page_drop_1]<br>
      </td>
    </tr>
    <tr>
      <td>
        [$form_drop_amount]:
      </td>
      <td>
        <input type="text" size="20" name="amount" value="{$max_amount}">
        <input type="hidden" name="object_id" value="{$object_id}">
      </td>
    </tr>
  </table>
  <div style="text-align:center">
    <a href="index.php?page=char.objects">
      <img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"></a>
    <input type=image src="[$_IMAGES]/button_forward2.gif" title="[$button_continue]">
  </div>
</form>
</center>
