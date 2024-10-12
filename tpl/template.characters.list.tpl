<table>
  {foreach $chars as $key => $char}
    <tr valign="center">
      <td style="width:30px;height:30px;vertical-align:middle;">
        <form method="post" action="{$char->link}">
          <input id="char_{$char->id}" type="image" src="[$_IMAGES]/button_{$char->buttonname}.gif"
                 title="{if $char->new_events}[$alt_new_events]{/if} [$alt_go_to_page]" style="vertical-align:middle">
        </form>
      </td>
      <td style="width:20%;height:30px;vertical-align:middle;">
        <label class="characterOnList {if !$char->new_events}characterOnList-inactive{/if}"
               data-{$charIdDataKey}="{$char->id}" for="char_{$char->id}">
          <span>{$char->name}</span>
        </label>
      </td>
      <td width="20">
        <img src="[$_IMAGES]/{if $char->sex != 1}fe{/if}male.gif" {if $char->sex != 1}  alt="[$form_female]"{else} alt="[$form_male]"{/if}
             border="0"/>
      </td>
      <td align="left">
        {$char->loc_name}
      </td>
      <td width="36">
        {assign var="opacity" value=0}
        {if $char->near_death_state == constant("CharacterConstants::NEAR_DEATH_NOT_HEALED")}
          <img src="[$_IMAGES]/icon_small_state_near_death.gif" title="[$alt_near_death]"/>
        {elseif $char->near_death_state == constant("CharacterConstants::NEAR_DEATH_HEALED")}
          <img src="[$_IMAGES]/icon_small_state_near_death_cured.gif" title="[$alt_near_death_cured]"/>
        {elseif $char->health <= [$_STATE_WOUNDS_3]}{assign var="opacity" value=1}{elseif $char->health <= [$_STATE_WOUNDS_2]}{assign var="opacity" value=0.5}{elseif $char->health <= [$_STATE_WOUNDS_1]}{assign var="opacity" value=0.2}{/if}
        {if $opacity && $char->health != 0}
          <img src="[$_IMAGES]/icon_small_state_wounded.gif"
               title="{if $opacity==0.2}[$alt_state_char_scratched]{elseif $opacity==0.5}[$alt_state_char_hurt]{else}[$alt_state_char_wounded]{/if}"
               border=0 style="opacity:{$opacity};"/>
        {/if}
        {assign var="opacity" value=0}
        {if $char->hunger >= [$_STATE_HUNGER_3]}{assign var="opacity" value=1}{elseif $char->hunger >= [$_STATE_HUNGER_2]}{assign var="opacity" value=0.5}{elseif $char->hunger >= [$_STATE_HUNGER_1]}{assign var="opacity" value=0.2}{/if}
        {if $opacity}
          <img src="[$_IMAGES]/icon_small_state_starving.gif"
               title="{if $opacity==0.2}[$alt_state_char_starving]{elseif $opacity==0.5}[$alt_state_char_emaciated]{else}[$alt_state_char_very_hungry]{/if}"
               border=0 style="opacity:{$opacity};"/>
        {/if}
        {strip}
          {assign var="opacity" value=0}
          {if $char->drunkenness >= $CONSTANTS.PASSED_OUT_MIN}
            <img src="[$_IMAGES]/icon_small_state_unconscious.gif" title="[$alt_state_char_unconscious]"/>
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
          <img src="[$_IMAGES]/icon_small_state_beer.gif"
               title="{if $opacity==0.1}[$alt_state_char_tipsy]{elseif $opacity==0.2}[$alt_state_char_half_drunk]{elseif $opacity==0.35}[$alt_state_char_drunk]{elseif $opacity==0.6}[$alt_state_char_very_drunk]{else}[$alt_state_char_falling_down_drunk]{/if}"
               border="0" style="opacity:{$opacity};"/>
        {/if}
      </td>
      <td width="16">
        {if $char->project_name}
          <img src="[$_IMAGES]/icon_small_state_working.gif" title="{$char->project_name}"/>
        {/if}
      </td>
      <td width="80" align="right">
        {$char->progress}
      </td>
    </tr>
    {foreachelse}
    {if $displayInfoOnEmpty}
      <tr valign="center">
        <td>
          [$form_no_character_yet]
        </td>
      </tr>
    {/if}
  {/foreach}
</table>
