{include file="template.title.[$lang].tpl" title="[$title_select_project]"}

<div class="page">
<table>
  <TR>
    <FORM METHOD=POST ACTION="index.php?page={$page}">
    <TD WIDTH=300>
      [$form_select_project]
    </TD>
    <TD WIDTH=400>
      <SELECT NAME=project>
      {foreach from=$projects item=project}
        <OPTION VALUE="{$project->id}">{$project->name} ({if $project->char}{$project->char}, {/if}{$project->day}-{$project->turn})</OPTION>
      {/foreach}
      </SELECT>
      <INPUT TYPE=hidden NAME="object_id" VALUE="{$object_id}">
    </TD>
  </TR>
  <TR>
    <TD COLSPAN=2 ALIGN=center>
      <BR>
      <a href="index.php?page=char.inventory&object_id={$object_id}"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_inventory]"></a>
      <INPUT TYPE=image SRC="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
    </TD>
    </FORM>
  </TR>
</TABLE>
</div>