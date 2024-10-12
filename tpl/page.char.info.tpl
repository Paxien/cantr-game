{include file="template.title.[$lang].tpl" title="[$title_character_info]"}

<div class="page-left">
<table>
  <colgroup>
    <col class="charInfoCaptionColumn">
    <col class="charInfoDataColumn">
    <col class="charInfoButtonColumn">
  </colgroup>
  <tr>
    <td>[$form_name]:</td>
    <td>
      <form method="post" action="index.php?page=characterdescription">
        <input id="ownCharId" type="hidden" name="ocharid" value="{$character}">
        <label style="cursor: pointer" id="ownCharacterInfo">
          <input type="image" width="10" height="10" src="[$_IMAGES]/button_small_char_happy.gif"
                 title="[$alt_description_person]">
          <b>{$charname}</b>
        </label>
        ({$age} [$char_info_years_old])
      </form>
      {if $is_near_death}
        <a href="index.php?page=suicide" class="button_charmenu">
          [$near_death_die]
        </a>
      {elseif isset($estimatedDeathDate)}
        <CANTR REPLACE NAME=old_age_death_counter DAY={$estimatedDeathDate->day} HOUR={$estimatedDeathDate->hour} MINUTE={$estimatedDeathDate->minute}>
      {elseif $canDieOfOldAge}
        <a href="index.php?page=death_old_age" class="button_charmenu">
          [$old_age_die]
        </a>
      {/if}
    </td>
    <td rowspan="2" style="text-align: right">
      {if $seasonName}
        <img class="weatherIcon" src="[$_IMAGES]/weather/{$weatherName}.png"
             alt="<CANTR REPLACE NAME=weather_type_{$weatherType}>"
             title="<CANTR REPLACE NAME=weather_type_{$weatherType}>"/>
        <img class="weatherIcon" src="[$_IMAGES]/weather/season_{$seasonName}.png"
             alt="<CANTR REPLACE NAME=text_season_its> <CANTR REPLACE NAME=season_{$season}>"
             title="<CANTR REPLACE NAME=text_season_its> <CANTR REPLACE NAME=season_{$season}>"/>
      {/if}
    </td>
  </tr>
  <tr>
    <td>[$form_carrying]:</td>
    <td id="inventory_weight">{$charload}g</td>
  </tr>
  <tr>
    <td>[$form_location]:</td>
    <td id="charInfoLocation">{$charlocationname}
      {if $hasLocationDescription}
        <img class="inlined-icon" src="[$_IMAGES]/icon_landscape.png" title="[$alt_location_with_custom_description]"/>
      {/if}
      {$locationdesc}
      {if $istravelling}
        ({$travelprogress}%)
    </td>
    <td align="right">
        <form method="post" action="index.php?page=turnaround">
          <input type="image" src="[$_IMAGES]/button_small_turnaround.gif"
                 title="[$alt_turn_around]">
        </form>
        <form method="post" action="index.php?page=speed">
          <input type="image" src="[$_IMAGES]/button_small_speed.gif"
                 title="[$alt_adjust_speed]">
        </form>
      {/if}
    </td>
  </tr>

  <tr id="projectPanel" {if !($projectid > 0)} style="display:none"{/if}>
    <td>[$form_project]:</td>
    <td id="projectPanelName">
      <a href="index.php?page=infoproject&project={$projectid}" class="button_charmenu" style="padding:3px">
        {$projectname} {$percentcomplete}%
      </a>
      {if $projectProblems}
        <img src="[$_IMAGES]/icon_exclamation.gif" style="vertical-align: bottom" title="{foreach $projectProblems as $problem}{$problem}&#xA;{/foreach}" />
      {/if}
    </td>
    <td>
      <form method="post" action="index.php?page=dropproject">
        <input class="action_drop_project" type=image src="[$_IMAGES]/button_small_end.gif"
               title="[$alt_end_participation]">
      </form>
    </td>
  </tr>

  <tr id="draggingPanel" {if !$dragging} style="display:none"{/if}>
    <td>[$form_dragging]:</td>
    <td id="draggingPanelName">{if $dragging}{$dragging} ({$draggingPercent}%){/if}</td>
    <td>
      <form method="post" action="index.php?page=dropdragging">
        <input class="action_drop_dragging" type=image src="[$_IMAGES]/button_small_end.gif"
               title="[$alt_end_participation_dragging]">
      </form>
    </td>
  </tr>
</table>
</div>

<div id="navigationPanel" class="page">
  {foreach $mainPanel as $menuAction => $menuText}
    <form method="post" action="index.php?page={$menuAction}">
      <input type="submit" value="{$menuText}" class="button_charmenu">
    </form>
  {/foreach}
</div>

<script type="text/javascript" src="[$JS_VERSION]/js/page.char.info.js"></script>
