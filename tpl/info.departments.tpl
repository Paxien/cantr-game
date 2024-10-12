{include file="template.title.[$lang].tpl" title="[$title_organisation]"}

<div class="page">
<table>
  <TR>
    <TD COLSPAN=4>
      [$organisation_desc1] <BR><BR>
      [$organisation_desc2] <BR><BR>
      [$organisation_desc3] <BR><BR>
    </TD>
  </TR>
  <TR>
    <TD COLSPAN=4>
      <BR><BR>
    </TD>
  </TR>
  <TR>
    <TD>
      <I>[$organisation_forum_nick]</I>
    </TD>
    <TD>
      <I>[$organisation_irc_nick]</I>
    </TD>
    <TD>
      <I>[$organisation_staff_member_position]</I>
    </TD>
  </TR>
  
{foreach from=$Councils item=Council}
  <TR>
    <TD COLSPAN=4>
      <BR><BR><A HREF="index.php?page=contact&council={$Council->id}"><B>{$Council->name}</B></A>
      <BR>{$Council->description}<BR><BR>
      <A HREF="index.php?page=contact&council={$Council->id}">[$organisation_contact]</A><BR><BR>
    </TD>
  </TR>
  {foreach from=$Council->Members item=Member}
    {if $Member->player == 999999}
    <TR>
      <TD>
        <B>[$organisation_staff_position_open]</B>
      </TD>
    </TR>
    {else}
    <TR>
      <TD VALIGN=top>
        {$Member->forumnick}
      </TD>
      <TD>
        {$Member->nick}
      </TD>
      <TD VALIGN=top>
        {if     $Member->status == 1}[$staff_postion_chairman]
        {elseif $Member->status == 2}[$staff_postion_vicechairman]
        {elseif $Member->status == 3}[$staff_postion_member]
        {elseif $Member->status == 4}[$staff_postion_special]
        {elseif $Member->status == 5}[$staff_postion_aspirant]
        {elseif $Member->status == 6}[$staff_postion_onleave]
        {/if}
        <SMALL>{$Member->special}</SMALL>{if $admin == 1} <A HREF="index.php?page=infoplayer&player_id={$Member->player}">[i]</A>{/if}
      </TD>
    </TR>
    {/if}
  {/foreach}
{/foreach}

  <TR>
    <TD COLSPAN=4 ALIGN=center>
      <BR>
      {if $s}
        <A HREF="index.php?page=player">[$back_to_player]</A>
      {else}
        <A HREF="index.php">[$back_to_introduction]</A>
      {/if}
    </TD>
  </TR>
</TABLE>
</div>