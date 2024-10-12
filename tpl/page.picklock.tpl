{include file="template.title.[$lang].tpl" title="[$title_decide_picklock]"}

<div class="page">
<form method="post" action="index.php?page=picklock">
  <table>
    <tr>
      <td>
        <input type="hidden" name="lock" value="{$lock.id}">
        <input type="hidden" name="lockpick" value="{$lockpick.type}">
        <input type="hidden" name="data" value="yes">
        <input type="hidden" name="{$lock_location_name}">
        [$desc_pick_the_lock_1] {$lockpick.name}. [$desc_pick_the_lock_2] <b>{$lock.id}</b> [$desc_pick_the_lock_3] {$lock.name}.<br><br>
        [$desc_pick_the_lock_4]<br><br>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_pick_the_lock]">
      </td>
    </tr>
  </table>
</form>
</div>