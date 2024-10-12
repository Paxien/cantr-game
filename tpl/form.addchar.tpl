{if $confirmation}

{include file="template.title.[$lang].tpl" title="[$title_character_info]"}
<form METHOD=POST ACTION="index.php?page=addchar">

<div class="page">
<TABLE>
  <TR>
    <TD COLSPAN="2">
      [$page_add_char_confirm]<BR><BR>
    </TD>
  </TR>
  <TR>
    <TD>
      [$form_name]:
    </TD>
    <TD>
      <INPUT TYPE=hidden NAME="name" VALUE="{$name}">{$name}
    </TD>
  </TR>
  <TR>
    <TD>
      [$form_sex]:
    </TD>
    <TD>
      <INPUT TYPE=hidden NAME="sex" VALUE="{$sex}">{if $sex == 1}[$form_male]{else}[$form_female]{/if}
    </TD>
  </TR>
  {if $genesis}
  <TR>
    <TD>
      [$form_world]:
    </TD>
    <TD>
      <INPUT TYPE=hidden NAME="genesis" VALUE=1>Genesis
    </TD>
  </TR>
  {/if}
  <TR>
    <TD COLSPAN=2>
      <INPUT TYPE=hidden NAME="charlanguage" VALUE="{$charlanguage}">
      <INPUT TYPE=hidden NAME="confirmed" VALUE="yes">
      <INPUT TYPE=hidden NAME="data" VALUE="yes">
      <INPUT TYPE=submit VALUE="[$button_register]" class="button_charmenu">
    </TD>
  </TR>
</TABLE>
  <div class="centered"><a HREF="index.php?page=player">[$back_to_player]</a></div>
</div>
</form>

{else}
{include file="template.title.[$lang].tpl" title="[$title_character_info]"}

<form METHOD=POST ACTION="index.php?page=addchar">

<div class="page">
<TABLE>
  <TR>
    <TD COLSPAN="2">
      [$page_add_char_intro]
      <BR><BR>
    </TD>
  </TR>
  <TR>
    <TD>
      [$form_name]
    </TD>
   <TD>
      <INPUT style="width:100%" NAME="name" VALUE="">
    </TD>
  </TR>
  <TR>
    <TD>
      [$form_sex]
    </TD>
    <TD>
      <label><INPUT TYPE="radio" NAME="sex" VALUE="1">[$form_male]</label><BR>
      <label><INPUT TYPE="radio" NAME="sex" VALUE="2">[$form_female]</label>
    </TD>
  </TR>     
  <TR>
    <TD COLSPAN=2>
      [$page_add_char_language]
    </TD>
  </TR>
  <TR>
    <TD>
      [$form_language]
    </TD>
    <TD>
      <SELECT NAME="charlanguage">
        {html_options options=$languages selected=$ownlanguage}  
      </SELECT>
    </TD>
  </TR>
  {if $canCreateOnGenesis}
  <TR>
    <TD>
      [$form_create_on_genesis]
    </TD>
    <TD>
      <INPUT TYPE="checkbox" NAME="genesis" VALUE="1" checked>
    </TD>
  </TR>
  {/if} 
  <TR>
    <TD COLSPAN="2" ALIGN="center">
      <BR>
      <INPUT TYPE="hidden" VALUE="yes" NAME="data">
      <INPUT TYPE="submit" VALUE="[$button_register]" class="button_charmenu">
    </TD>
  </TR>
</TABLE>
  <div class="centered"><A HREF="index.php?page=player">[$back_to_player]</A></div>
</div>
</form>
{/if}
