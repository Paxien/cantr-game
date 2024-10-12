{include file="template.title.[$lang].tpl" title="[$title_pack_join]"}

<div class="page">
<form method="post" action="index.php?page=pack_join">
  <table>
    <tr>
      <td>[$animal_pack_join_text]</td>
    </tr>
    <tr>
      <td style="text-align:center;">
        <input type="hidden" name="validate" value="1" />
        <input type="hidden" name="object_id" value="{$object_id}" />
        <a href="index.php?page=char.objects"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]" /></a>
        <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_pack_join]">
      </td>
    </tr>
  </table>
</form>
</div>