{include file="template.title.[$lang].tpl" title="[$title_talk_menu] <CANTR CHARNAME ID=$to>"}

<FORM METHOD="POST" ACTION="index.php?page={$page}">

<div class="page">
<table>
  <tr>
    <td width=500>
      <input type="hidden" name="player" value="{$player}" />
      <input TYPE="hidden" name="to" value="{$to}">
   {if $large_box == 1}
      <input type="hidden" name="large_box" value="2" />
      <input type="submit" value="[$regular_text_box]" CLASS="button_charmenu" />
   {else}
      <input type="hidden" name="large_box" value="1" />
      <input type="submit" value="[$large_text_box]" CLASS="button_charmenu" />
   {/if}
    </td>
  </tr>
</table>
</div>
</FORM>

<FORM METHOD="POST" ACTION="index.php?page={$page}">
<div class="page">
<table>
  <TR>
    <TD WIDTH="200">[$form_talk_menu]</TD>
    <TD WIDTH="500">
    {if $large_box == 1}
      <TEXTAREA NAME=message cols="70" rows="7"></TEXTAREA>
    {else}
      <INPUT TYPE=text NAME=message SIZE="80">
    {/if}
    </TD>
  </TR><TR>
    <TD ALIGN="center" COLSPAN="2">
      <INPUT TYPE="hidden" NAME="data" VALUE="yes">
      <INPUT TYPE="hidden" NAME="to" VALUE="{$to}">
      <BR>
      <a href="index.php?page=char"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]" ></a>
      <INPUT TYPE=image SRC="[$_IMAGES]/button_forward2.gif" title="[$form_say]">
    </TD>
  </TR>
</TABLE>
</div>
</FORM>