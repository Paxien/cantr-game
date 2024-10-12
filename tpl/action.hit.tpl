{include file="template.title.[$lang].tpl" title="[$title_violence]"}

<div class="page">
<TABLE>
  <TR>
    <TD COLSPAN=2>[$page_hurt_weapon]</TD>
  </TR>
  <TR VALIGN=top>
    <TD WIDTH=200>[$form_weapon]:</TD>
    <TD WIDTH=500>
      <FORM METHOD=post ACTION="index.php?page=hit">
        <INPUT TYPE=hidden NAME="to" VALUE="{$victim}">
        <SELECT NAME=tool>
        {foreach from=$weapons item=weapon}
          <OPTION VALUE="{$weapon.id}">{$weapon.name}</OPTION>
        {/foreach}
        </SELECT>
      </TD>
    </TR>
    <TR VALIGN=top>
      <TD WIDTH=200>[$form_force]</TD>
      <TD WIDTH=500>
        <label><INPUT TYPE="radio" NAME="force" VALUE="0">0%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="1">10%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="2">20%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="3">30%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="4">40%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="5">50%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="6">60%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="7">70%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="8">80%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="9">90%</label><BR>
        <label><INPUT TYPE="radio" NAME="force" VALUE="10" CHECKED>100%</label>
      </TD>
    </TR>
    <TR>
      <td colspan="2" align="center">
        <br>
          <a href="index.php?page=char"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]"></a>
          <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$button_continue]" >
      </td>
    </TR>
</TABLE>
</div>