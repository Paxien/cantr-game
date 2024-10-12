{include file="template.title.[$lang].tpl" title="[$title_polls]"}

{foreach from=$polls item=poll}
<div class="page">
<table>
  <TR>
    <TD>
    {if     $poll->type == 1}[$poll_intro_1]
    {elseif $poll->type == 2}[$poll_intro_2]
    {elseif $poll->type == 3}[$poll_intro_3]
    {elseif $poll->type == 4}[$poll_intro_4]
    {elseif $poll->type == 5}[$poll_intro_5]
    {/if}<BR>
      <FORM METHOD=post ACTION="index.php?page=submitpoll&pollid={$poll->id}">
      {foreach from=$poll->items item=p}
        {if $p->type == 'text'}
          <BR><I>{$p->val}</I><BR>
        {elseif $p->type == 'closed'}
          <INPUT TYPE=radio NAME="answer{$p->question}" VALUE="{$p->answer}">{$p->val}
        {else}
          {$p->val}<INPUT TYPE=text NAME="answer{$p->question}_{$p->answer}" SIZE=50>
        {/if}
        <BR>
      {/foreach}
        <BR>
        <CENTER>
        <INPUT TYPE=submit VALUE="[$button_submit_vote]">
        </CENTER>
      </FORM>
    </TD>
  </TR>
</TABLE>
</div>
{/foreach}