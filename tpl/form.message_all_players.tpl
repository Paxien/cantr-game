{include file="template.title.[$lang].tpl" title="[$title_talk_menu] <CANTR CHARNAME ID=$to>"}

Please <strong>remove</strong> messages older than <strong>two weeks</strong>.

<FORM METHOD="POST" ACTION="index.php?page={$page}">
	<div class="page">
	    <input type="hidden" name="player" value="{$player}" />
	    <input TYPE="hidden" name="to" value="{$to}">
	    {if $large_box == 1}
	    <input type="hidden" name="large_box" value="2" />
	    <input type="submit" value="[$regular_text_box]" CLASS="button_charmenu" />
	    {else}
	    <input type="hidden" name="large_box" value="1" />
	    <input type="submit" value="[$large_text_box]" CLASS="button_charmenu" />
	    {/if}
	  </div>
</FORM>

<FORM METHOD="POST" ACTION="index.php?page={$page}">
	<div class="page">
<table>
	<TR>
	  <TD WIDTH="200">[$form_talk_menu]</TD>
	  <TD>
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
	    <INPUT TYPE=submit VALUE="[$form_say]">
	    <P>Or go <A HREF="index.php?page=player">back</A> to the player page.
	  </TD>
	</TR>
</TABLE>
</div>
</FORM>

  <!-- list of existing messages -->
<div class="page">
<table>
	<TR><TD>

	  {section name=messNo loop=$messages}
	  <TABLE>
            <TR>
              <TD width=100%>
		{$messages[messNo]->author} ({$messages[messNo]->date})
              </TD>
              <FORM METHOD=post ACTION="index.php?page=remove_all_messages">
		<TD align=right>
		  <INPUT TYPE=hidden NAME=message_id VALUE={$messages[messNo]->id}>
		  <INPUT TYPE=submit VALUE=[$button_remove_message] CLASS=button_charmenu>
		</TD>
              </FORM>
            </TR>
	  </TABLE>
	  <HR>{$messages[messNo]->content}<HR><BR>
	  {/section}
	</TD></TR>
</TABLE>
</div>