{include file="template.title.[$lang].tpl" title="[$title_select_region]"}

<div class="page">
<table>
  <TR>
    <TD>
      [$form_region]:
      <FORM METHOD=post ACTION="index.php?page={$page}">
        <SELECT NAME="region">
        {foreach from=$regions item=region}
          <OPTION VALUE="{$region->id}">{$region->name}
        {/foreach}
        </SELECT>
      {if $showempty}
        <BR>Display non-populated locations only: <INPUT TYPE=checkbox NAME="display_empty">
      {/if}
      <BR><BR>
      <TABLE>
        <TR>
          <TD WIDTH=60>
            <INPUT TYPE=image SRC="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
          </TD>
        </FORM>
        <FORM METHOD=post ACTION="index.php?page=player">
          <TD WIDTH=60>
            <INPUT TYPE=image SRC="[$_IMAGES]/button_back2.gif" title="[$back_to_player]">
          </TD>
        </FORM>
        </TR>
      </TABLE>
    </TD>
  </TR>
</TABLE>
</div>

{if $showlang}
{include file="template.title.[$lang].tpl" title="OR... SELECT A LANGUAGE GROUP"}

<div class="page">
<TABLE>
  <TR>
    <FORM METHOD=post ACTION="index.php?page={$page}">
    <TD>
      Language:
      <INPUT TYPE=hidden NAME="language_lookup" VALUE="1">
      <SELECT NAME=region>
      {foreach from=$langs item=lang}
        <OPTION VALUE="{$lang->id}">{$lang->name}
      {/foreach}
      </SELECT><BR><BR>
      <TABLE>
        <TR>
          <TD WIDTH=60>
            <INPUT TYPE=image SRC="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
          </TD>
        </FORM>
        <FORM METHOD=post ACTION="index.php?page=player">
          <TD WIDTH=60>
            <INPUT TYPE=image SRC="[$_IMAGES]/button_back2.gif" title="[$back_to_player]">
          </TD>
        </FORM>
        </TR>
      </TABLE>
    </TD>
  </TR>
</TABLE>
</div>
{/if}

{include file="template.title.[$lang].tpl" title="OR... SELECT AN ISLAND"}

<div class="page">
<table>
  <TR>
    <TD>
      Island:
      <FORM METHOD=post ACTION="index.php?page={$page}">
      <INPUT TYPE=hidden NAME="island_lookup" VALUE="1">
        <SELECT NAME="region">
        {foreach from=$islands item=island}
          <OPTION VALUE="{$island->id}">{$island->id}. ({$island->minid} - {$island->maxid}) {$island->name} - landmass {$island->mass}
        {/foreach}
        </SELECT>
      {if $showempty}
        <BR>Display non-populated locations only: <INPUT TYPE=checkbox NAME="display_empty">
      {/if}
      <BR><BR>
      <CENTER>
      <TABLE>
        <TR>
          <TD WIDTH=60>
            <INPUT TYPE=image SRC="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
          </TD>
        </FORM>
        <FORM METHOD=post ACTION="index.php?page=player">
          <TD WIDTH=60>
            <INPUT TYPE=image SRC="[$_IMAGES]/button_back2.gif" title="[$back_to_player]">
          </TD>
        </FORM>
        </TR>
      </TABLE>
      </CENTER>  
    </TD>
  </TR>
</TABLE>
</div>