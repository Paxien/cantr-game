{include file="template.title.[$lang].tpl" title="[$title_alter_sailing]"}

<div class="page">
<table>
  <tr>
    <td colspan="2">
      <form method="post" action="index.php?page=adjustsailing">
        <input type="hidden" name="data" value="yes">
        <input type="hidden" name="sailing_id" value="{$sailing_id}">
        [$page_alter_sailing_1]<br /><br />
    </td>
  </tr>
  <tr>
    <td width="200">
      [$form_new_direction]:
    </td>
    <td>
      <input type="text" name="direction" value="{$saildirection}" size="20"> [$degrees]
    </td>
  </tr>
  <tr>
    <td>
      [$form_new_speed]:
    </td>
    <td>
      <input type="text" name="speed" value="{$sailspeed}" size="20"> [$percent]
    </td>
  </tr>
  <tr>
    <td>
      [$form_keep_going]:
    </td>
    <td>
      <input type="text" name="turns" value="{$sailhours}" size="20"> [$turns]
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <br /><br />
      <input type="submit" value="[$button_continue]" class="button_charmenu">
      </form>
    </td>
  </tr>
</table>
</div>
