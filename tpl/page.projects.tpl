{include file="template.title.[$lang].tpl" title="[$title_projects]"}

{if $locationType==1}
<div class="page" style="text-align:center">
    [$digging_slot_summary]
</div>
{/if}

<div class="page">
<table id="projectsList">
  {foreach from=$projects item=project}

  <tr valign="center">
    <td width="30">
    <form method="post" action="index.php?page=pointat">
      <input type="hidden" name="to_project" value="{$project.id}">
      <input type="image" src="[$_IMAGES]/button_small_pointat.gif" title="[$title_pointat_project]">
    </form>
    </td>
    <td width="30" valign="center">
    <form method="post" action="index.php?page=infoproject">
      <input type="hidden" name="project" value="{$project.id}">
      <input type="image" src="[$_IMAGES]/button_small_info.gif" align="middle"
         title="[$alt_projects_get_info]">
    </form>
    </td>
  {if !$projectid && !$draggoal && $project.joinable}
    <td width="30">
    <form method="post" action="index.php?page=joinproject">
      <input type="hidden" name="project" value="{$project.id}">
      <input type="image" src="[$_IMAGES]/button_small_join.gif" align="middle" title="[$alt_projects_register_part]">
    </form>
    </td>
  {else}
    <td width="30">
    </td>
  {/if}

    <td width="30">
    <form method="post" action="index.php?page=delproject">
      <input type="hidden" name="project" value="{$project.id}">
      <input type="image" src="[$_IMAGES]/button_small_end.gif" align="middle" title="[$alt_projects_remove]">
    </form>
    </td>
    <td>
    {if $project.color == 0}
    <p class="project_worked_on">
    {elseif $project.color == 1}
    <p class="project_not_worked_on">
    {else}
    <p class="project_self_works_on">
    {/if}
    {$project.name} (<span style="font-size:8pt">{if $project.hasneededstuff}√{else}x{/if} {if isset( $project.percents ) }{$project.percents}%{else}-{/if}</span>, 
    {if $project.initiator}
      <CANTR CHARNAME ID={$project.initiator}>,
    {/if}
     {$project.day}-{$project.hour})</p>

    </td>
  </tr>
  {/foreach}

</table>
</div>
