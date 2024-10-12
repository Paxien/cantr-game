<script type="text/javascript" src="[$JS_VERSION]/js/char_settings.js"></script>

{include file="template.title.[$lang].tpl" title="[$title_character_settings]"}

<div class="page">
<table>
  <tr>
    <td colspan="5">
      <form method="post" action="index.php?page=char.settings">
        <input type="hidden" name="selectedtab" value="0" />
        <input type="submit" value="[$charsettings_menu_1]" class="button_charmenu{if $selectedtab == 0 }active{/if}" />
      </form>           
      <form method="post" action="index.php?page=char.settings">
        <input type="hidden" name="selectedtab" value="1" />
        <input type="submit" value="[$charsettings_menu_2]" class="button_charmenu{if $selectedtab == 1 }active{/if}" />
      </form>
      <form method="post" action="index.php?page=char.settings">
        <input type="hidden" name="selectedtab" value="2" />
        <input type="submit" value="[$charsettings_menu_3]" class="button_charmenu{if $selectedtab == 2 }active{/if}" />
      </form>
      <form method="post" action="index.php?page=char.settings">
        <input type="hidden" name="selectedtab" value="3" />
        <input type="submit" value="[$charsettings_menu_4]" class="button_charmenu{if $selectedtab == 3 }active{/if}" />
      </form>
    </td>
  <tr>
</table>
</div>