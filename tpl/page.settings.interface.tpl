{include file="template.title.[$lang].tpl" title="[$title_settings_interface]"}
<div class="page">
  <form method="post" action="index.php?page=settings&category=interface">
    <input type="hidden" name="data" value="yes">
    <table>
    <tr>
      <td colspan="2">
        [$settings_interface_text_1]
      </td>
    </tr>
    {foreach from=$optionsList key=name item=both}
    <tr>
    <td width="50%" align="right"><CANTR REPLACE NAME=settings_interface_{$name}>:</td>
      <td>
        <select name="settings_interface_{$name}">
        {section name=option_id loop=$both.maxValue+1}
          <option value="{$smarty.section.option_id.index}"{if $both.selected==$smarty.section.option_id.index} selected{/if}><CANTR REPLACE NAME=settings_interface_{$name}_{$smarty.section.option_id.index}></option>
        {/section}
        </select>
      </td>
    </tr>
    {/foreach}
    <tr>
      <td width="50%" align="right">[$text_base_css_skin]:</td>
      <td>
        <select name="selected_skin" size="{$skinsList|@count}">
        {foreach from=$skinsList item=skin_name}
          <option {if $selectedSkin == $skin_name} selected{/if}>{$skin_name}</option>
        {/foreach}
        </select>
      </td>
    </tr>
    <tr>
      <td style="padding-top:30px;" colspan="2">
        <label style="cursor:pointer;"><input type="checkbox" name="custom_css" value="yes" {if $isCustomSkin}checked{/if} /> [$text_custom_css_skin]:</label> <br>
        <textarea style="width: 100%" rows="10" id="css_text" name="custom_css_text">{$customSkin}</textarea>
      </td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <input type="submit" value="[$settings_change_interface]" class="button_charmenu">
      </td>
    </tr>
    </table>
  </form>
  
  <div class="centered">
    <a href="index.php?page=player" class="button_charmenu">[$back_to_player]</a>
  </div>
</div>

{literal}
<script type="text/javascript">
  $(document).ready(function () {
  $('#css_text').prop('disabled', !$('input[name=custom_css]').is(':checked') );
    $('input[name=custom_css]').click(function() {
      $('#css_text').prop('disabled', !$('input[name=custom_css]').is(':checked') );
    });
  });
</script>
{/literal}