{include file="template.title.[$lang].tpl" title="[$title_confirm_unsubscribe]"}

<div class="page">
<TABLE>
  <TR>
    <TD>
      [$page_confirm_unsubscribe_1]<BR><BR>
   {if $player_id}
      Player: <B>{$firstname} {$lastname}</B> ({$email})<BR><BR>
   {/if}
      [$page_confirm_unsubscribe_2]<BR><BR>
      <p style="color:#ff0000;font-size:18px;text-align:center;font-weight:bold;">[$page_confirm_unsubscribe_3]</p>
      <BR><BR>
    </TD>
  </TR>
  <TR>
    <TD ALIGN="center">
      <FORM METHOD=POST ACTION="index.php?page=unsubscribe">
        <INPUT TYPE=hidden NAME="holder" VALUE="yes">
        <INPUT TYPE=hidden NAME="data" VALUE="yes">
        {if !$player_id}
          [$form_pass_unsubscribe]:<BR>
          <INPUT TYPE=password NAME="password">
          <INPUT TYPE=hidden NAME="player_id" VALUE="{$player}">
          <BR><BR>
        {else}
          <INPUT TYPE=hidden NAME="player_id" VALUE="{$player_id}">
          <BR>[$form_reason_unsubscribe]:<BR>
          <TEXTAREA NAME=reason COLS=60 ROWS=8></TEXTAREA>
        {/if}
        <BR><BR>
        <a href="index.php?page=player"><img SRC="[$_IMAGES]/button_back2.gif" title="[$back_to_player]"></a>
        <INPUT TYPE=image SRC="[$_IMAGES]/button_forward2.gif" title="[$alt_continue_unsubscribe]">
      </FORM>
    </TD>
  </TR>
</TABLE>
</div>
