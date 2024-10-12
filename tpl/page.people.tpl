{include file="template.title.[$lang].tpl" title="[$title_people]"}

<div class="page-left">
<table class="peopleTable">
  {foreach from=$chars item=char}
    {assign var=numberOfButtons value= 4 + $char->is_travelling + ($charlocation && !$charBusy) * 2}
    <TR VALIGN="center">
      <TD class="peopleTableButtons action-buttons action-buttons-{$numberOfButtons}">
			  <a href="index.php?page=characterdescription&ocharid={$char->id}">
				  <img src="[$_IMAGES]/button_small_char_happy.gif" title="[$alt_description_person]"/></a>
			  <a href="index.php?page=talk&to={$char->id}">
				  <img src="[$_IMAGES]/button_small_talk.gif" title="[$alt_talk_to_person]"/></a>
			  <a href="index.php?page=pointat&to={$char->id}">
				  <img src="[$_IMAGES]/button_small_pointat.png" title="[$alt_point_at_person]"/></a>
    {if $char->is_travelling}
			  <a href="index.php?page=matchspeed&to={$char->id}">
				  <img src="[$_IMAGES]/button_small_match.gif" title="[$alt_match_speed]"/></a>
    {/if}
    {if $charlocation && !$charBusy}
      {if $char->near_death}
        <a href="index.php?page=heal_near_death&to={$char->id}">
            <img src="[$_IMAGES]/button_small_heal.gif" title="[$alt_join_heal_nds_person]"/></a>
      {else}
        {if $char->canjoinitsproject}
          <a href="index.php?page=joinproject&project={$char->project}">
            <img src="[$_IMAGES]/button_small_join.gif" title="[$alt_join_person_project]"/></a>
        {else}
          <a href="index.php?page=helpdrag&ocharacter={$char->id}">
            <img src="[$_IMAGES]/button_small_help.gif" title="[$alt_help_person]"/></a>
        {/if}
      {/if}

      <a href="index.php?page=drag&ocharacter={$char->id}">
        <img src="[$_IMAGES]/button_small_drag.gif" title="[$alt_drag_person]"/></a>
    {/if}
      {if $char->near_death}
        <a href="index.php?page=finish_off&to={$char->id}" onclick="return confirm('Are you sure?')">
          <img src="[$_IMAGES]/button_small_finish_off.gif" title="[$alt_finish_off_person]"/></a>
      {else}
        <a href="index.php?page=hit&to={$char->id}">
          <img src="[$_IMAGES]/button_small_hit.gif" title="[$alt_hit_person]"/></a>
      {/if}
      </TD>
      <TD WIDTH="40">
        {assign var="opacity" value=0}
        {if $char->near_death == constant("CharacterConstants::NEAR_DEATH_NOT_HEALED")}
          <img src="[$_IMAGES]/icon_small_state_near_death.gif" title="[$alt_near_death]" />
        {elseif $char->near_death == constant("CharacterConstants::NEAR_DEATH_HEALED")}
          <img src="[$_IMAGES]/icon_small_state_near_death_cured.gif" title="[$alt_near_death_cured]" />
        {elseif $char->health <= [$_STATE_WOUNDS_3]}{assign var="opacity" value=1}{elseif $char->health <= [$_STATE_WOUNDS_2]}{assign var="opacity" value=0.5}{elseif $char->health <= [$_STATE_WOUNDS_1]}{assign var="opacity" value=0.2}{/if}
        {if $opacity && $char->health != 0}
          <img src="[$_IMAGES]/icon_small_state_wounded.gif" title="{if $opacity==0.2}[$alt_state_char_scratched]{elseif $opacity==0.5}[$alt_state_char_hurt]{else}[$alt_state_char_wounded]{/if}"style="opacity:{$opacity};" />
        {/if}
        {assign var="opacity" value=0}
        {if $char->hunger >= [$_STATE_HUNGER_3]}{assign var="opacity" value=1}{elseif $char->hunger >= [$_STATE_HUNGER_2]}{assign var="opacity" value=0.5}{elseif $char->hunger >= [$_STATE_HUNGER_1]}{assign var="opacity" value=0.2}{/if}
        {if $opacity}
          <img src="[$_IMAGES]/icon_small_state_starving.gif" title="{if $opacity==0.2}[$alt_state_char_starving]{elseif $opacity==0.5}[$alt_state_char_emaciated]{else}[$alt_state_char_very_hungry]{/if}" style="opacity:{$opacity};" />
        {/if}
        {assign var="opacity" value=0}
        {strip}
        {if $char->drunkenness >= $CONSTANTS.PASSED_OUT_MIN}
          <img src="[$_IMAGES]/icon_small_state_unconscious.gif" title="[$alt_state_char_unconscious]" />
        {elseif $char->drunkenness >= $CONSTANTS.DRUNK_STATE_MIN.4}
          {assign var="opacity" value=1}
        {elseif $char->drunkenness >= $CONSTANTS.DRUNK_STATE_MIN.3}
          {assign var="opacity" value=0.6}
        {elseif $char->drunkenness >= $CONSTANTS.DRUNK_STATE_MIN.2}
          {assign var="opacity" value=0.35}
        {elseif $char->drunkenness >= $CONSTANTS.DRUNK_STATE_MIN.1}
          {assign var="opacity" value=0.2}
        {elseif $char->drunkenness >= $CONSTANTS.DRUNK_STATE_MIN.0}
          {assign var="opacity" value=0.1}
        {/if}
        {/strip}{if $opacity}
          <img src="[$_IMAGES]/icon_small_state_beer.gif" title="{if $opacity==0.1}[$alt_state_char_tipsy]{elseif $opacity==0.2}[$alt_state_char_half_drunk]{elseif $opacity==0.35}[$alt_state_char_drunk]{elseif $opacity==0.35}[$alt_state_char_very_drunk]{else}[$alt_state_char_falling_down_drunk]{/if}" border="0" style="opacity:{$opacity};" />
        {/if}
        {if $char->workingonproject}
          <form action="index.php?page=infoproject" method="post">
            <input type="hidden" name="project" value="{$char->project}">
            <input type="hidden" name="from_page" value="char.people">
            <input type="image" src="[$_IMAGES]/icon_small_state_working.gif" title="{$char->project_name}"/>
          </form>
        {/if}
      </TD>
      <TD WIDTH="20" ALIGN="RIGHT">
        <img src="[$_IMAGES]/{if $char->sex == 2}fe{/if}male.gif" /> {*translate*}
      </TD>
      <TD>
        {$char->name} {$char->additional}
        {if $char->description != ""}<div class="charPersDesc">{$char->description}</div>{/if}
      </TD>
    </TR>
  {/foreach}
</TABLE>
</div>
