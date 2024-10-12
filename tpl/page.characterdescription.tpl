<div class="page">
<table>
<tr><td align="left">

{if !$isnear}
  [$form_chardesc_char_far]
{else}
  {capture name="titleName"}[$page_chardesc_title]: {$char.name}{/capture}
  {include file="template.title.[$lang].tpl" title=$smarty.capture.titleName}
  {strip}
    {$char.he} {if $char.ageInDecades == 0}<CANTR REPLACE NAME=char_desc_child>{elseif $char.ageInDecades <= 9}<CANTR REPLACE NAME=char_desc_is_in> {$char.his} <CANTR REPLACE NAME=char_desc_{$char.ageInDecades}0>{elseif $char.ageInDecades < 15}<CANTR REPLACE NAME=char_desc_old>{elseif $char.ageInDecades < 20}<CANTR REPLACE NAME=char_desc_very_old>{elseif $char.ageInDecades < 25}<CANTR REPLACE NAME=char_desc_extremely_old>{elseif $char.ageInDecades < 30}<CANTR REPLACE NAME=char_desc_ancient>{elseif $char.ageInDecades < 35}<CANTR REPLACE NAME=char_desc_very_ancient>{else}<CANTR REPLACE NAME=char_desc_extremely_ancient>{/if}
    {if $char.projectId > 0}
      &nbsp;[$char_desc_and_is]{if $char.skillLevelName} <CANTR REPLACE NAME=skill_adjective_{$char.skillLevelName}>{/if} [$char_desc_working_on_project] <a href="index.php?page=infoproject&project={$char.projectId}">'{$char.projectName}'</a>
    {/if}.
    {if $char.strengthLevelName}
      &nbsp;<CANTR REPLACE NAME=strength_adjective_{$char.sex}_{$char.strengthLevelName}>.
    {/if}
    &nbsp;{$char.hungerDescription}&nbsp;{$char.drunkennessDescription}
    {if $char.isNearDeath}
      {if $char.nearDeathCured}
        &nbsp;{$char.he} [$char_desc_in_near_death_state_cured]
      {else}
        &nbsp;{$char.he} [$char_desc_in_near_death_state]
      {/if}
    {/if}
    {if $lookingAtYourself && $char.diseases.faint}
      &nbsp;{$char.he} [$char_desc_feels_faint].
    {/if}
    {if $char.location > 0}
    <p>
      {$char.he} [$char_desc_is_in_location] <CANTR LOCNAME ID={$char.location}> &#91;<CANTR LOCDESC ID={$char.location}>&#93;.
    </p>
    {/if}
    {if $lookingAtYourself}
      <p>
        [$form_char_born]: {if $BORNLOC}[$form_char_born_details]{elseif $BORNDATE}[$form_char_born_unknown_place]{else}[$form_char_born_unknown]{/if}
      </p>
    {/if}
  {/strip}

  {* travel info *}

  {$char.travelText}

  {* character custom description *}

  {if $char.description|strlen > 0}
    <p style="font-style:italic">"{$char.description|nl2br}"</p>
    {if !$lookingAtYourself}
      <form method="post" action="index.php?page=reportabuse">
        <input type="hidden" name="offense_subject" value="Custom Description">
        <input type="hidden" name="offender" value="{$char.id}">
        <input type="hidden" name="offending_text" value="{$char.description|nl2br|escape:url}">
        <input type="hidden" name="returnTo" value="characterdescription&ocharid={$char.id}">
    <p><small>
    ([$char_desc_public_info]) <a href="index.php?page=publicdesc_guide&ocharid={$char.id}">
    &#91;[$char_public_desc_guidelines_link]&#93;</a> <input type=submit class="button_charmenu" value="[$report_abuse]">
  </small></p>
      </form>
    {/if}
  {/if}

  {* clothes list *}

  {if $char.clothes|@count == 0}
    <p>{$char.he} <CANTR REPLACE NAME=char_desc_wears_nothing>.</p>
  {else}
    <p>{$char.he} <CANTR REPLACE NAME=char_desc_wears>:</p>
    <ul>
    {foreach from=$char.clothes item=clothing}
      {strip}
        {if $lookingAtYourself || !$clothing.hidden}
          <li>
          {if $lookingAtYourself && $clothing.hidden}
            <span style='color:#ccc'>
          {/if}
          <span class="clothing-name">{$clothing.name}</span><span class="txt-label"> - {$clothing.desc}</span>
          {if $lookingAtYourself && $clothing.hidden}
            </span>
          {/if}
          {if $lookingAtYourself}
            <a href="index.php?page=unwear&object_id={$clothing.id}"> &#91;[$char_desc_undress]&#93;</a>
          {/if}
          </li>
        {/if}
      {/strip}
    {/foreach}
    </ul>
  {/if}
  {if $lookingAtYourself}
    <a href="index.php?page=char.inventory">&#91;[$char_desc_inventory]&#93;</a>
  {/if}

  {* character inventory *}

  {if $char.inventory|@count > 0}
    <p>[$char_desc_you_can_see] {$char.him} <CANTR REPLACE NAME=char_desc_holding>:</p>
    <ul>
    {foreach from=$char.inventory item=item}
      <li>
        <span class="object-name">{$item.name}</span>{if $item.description}<span class="txt-label"> - {$item.description}</span>{/if}{$item.bottom}
      </li>
    {/foreach}
    </ul><br>
  {/if}
  </td></tr>
  </table>
  </div>

  <div class="centered">
    <a href="index.php?page=talk&to={$char.id}">
    <img src="[$_IMAGES]/button_small_talk.gif" title="[$alt_talk_to_person]"></a>

    <a href="index.php?page=pointat&to={$char.id}">
    <img src="[$_IMAGES]/button_small_pointat.png" title="[$alt_point_at_person]"></a>

    {if $travel.isTravelling}
      <a href="index.php?page=matchspeed&to={$char.id}">
      <img src="[$_IMAGES]/button_small_match.gif" title="[$alt_match_speed]"></a>
    {/if}

    {if $you.canJoinProject}
      <a href="index.php?page=joinproject&project={$char.project}">
      <img src="[$_IMAGES]/button_small_join.gif" title="[$alt_join_person_project]"></a>
    {/if}
    {if $you.canJoinDragging}
      <a href="index.php?page=helpdrag&ocharacter={$char.id}">
      <img src="[$_IMAGES]/button_small_help.gif" title="[$alt_help_person]"></a>
    {/if}
    {if $you.canDrag}
    <a href="index.php?page=drag&ocharacter={$char.id}">
    <img src="[$_IMAGES]/button_small_drag.gif" title="[$alt_drag_person]"></a>
    {/if}
    {if $char.isNearDeath}
      {if !$char.nearDeathCured}
        <a href="index.php?page=heal_near_death&to={$char.id}">
          <img src="[$_IMAGES]/button_small_heal.gif" title="[$alt_join_heal_nds_person]"></a>
      {/if}
      <a href="index.php?page=finish_off&to={$char.id}" onclick="return confirm('Are you sure?')">
      <img src="[$_IMAGES]/button_small_finish_off.gif" title="[$alt_finish_off_person]"></a>
    {else}
      <a href="index.php?page=hit&to={$char.id}">
      <img src="[$_IMAGES]/button_small_hit.gif" title="[$alt_hit_person]"></a>
    {/if}
  </div>

  {include file="template.title.[$lang].tpl" title="[$title_char_desc_current_status]"}

  <div class="page">
  <table>
  {if $you.progressBars}
    <tr>
      <td>[$char_desc_bar_damage]:</td>
      <td align="right"><img style="height:auto;max-width:200px;width: 100%;" src="image.progressbar.inc.php?width=200&height=20&proportion={$char.damage}" title="[$char_desc_bar_damage] {$char.damagePerc}"></td><td align="right">{$char.damagePerc}%</td>
    </tr>
    <tr>
      <td>[$char_desc_bar_tiredness]:</td>
      <td align="right"><img style="height:auto;max-width:200px;width: 100%;" src="image.progressbar.inc.php?width=200&height=20&proportion={$char.tiredness}" title="[$char_desc_bar_tiredness] {$char.tirednessPerc}"></td><td align="right">{$char.tirednessPerc}%</td>
    </tr>
  {else}
    <tr>
      <td>[$char_desc_bar_damage]:</td>
      <td align="right"><img style="height:auto;max-width:400px;width: 100%;" src="image.bar.inc.php?w=400&val={$char.damage}&col1=" title="[$char_desc_bar_damage]
      {$char.damagePerc}%"></td>
    </tr>
    <tr>
      <td>[$char_desc_bar_tiredness]:</td>
      <td align="right"><img style="height:auto;max-width:400px;width: 100%;" src="image.bar.inc.php?w=400&val={$char.tiredness}&col1=apple" title="[$char_desc_bar_tiredness] {$char.tirednessPerc}%"></td>
    </tr>
  {/if}
  {if $lookingAtYourself}
    {if $you.progressBars}
      <tr>
        <td>[$char_desc_bar_hunger]:</td>
        <td align="right"><img style="height:auto;max-width:200px;width: 100%;" src="image.progressbar.inc.php?width=200&height=20&proportion={$char.hunger}" title="[$char_desc_bar_hunger] {$char.hungerPerc}"></td><td align="right">{$char.hungerPerc}%</td>
      </tr>
      <tr>
        <td>[$char_desc_bar_drunkenness]:</td>
        <td align="right"><img style="height:auto;max-width:200px;width: 100%;" src="image.progressbar.inc.php?width=200&height=20&proportion={$char.drunkenness}" title="[$char_desc_bar_drunkenness] {$char.drunkennessPerc}"></td><td align="right">{$char.drunkennessPerc}%</td>
      </tr>
    {else}
      <tr>
        <td>[$char_desc_bar_hunger]:</td>
        <td align="right"><img style="height:auto;max-width:400px;width: 100%;" src="image.bar.inc.php?w=400&val={$char.hunger}&col1=blue" title="[$char_desc_bar_hunger]
        {$char.hungerPerc}%"></td>
      </tr>
      <tr>
        <td>[$char_desc_bar_drunkenness]:</td>
        <td align="right"><img style="height:auto;max-width:400px;width: 100%;" src="image.bar.inc.php?w=400&val={$char.drunkenness}&col1=gold" title="[$char_desc_bar_drunkenness] {$char.drunkennessPerc}%"></td>
      </tr>
    {/if}
    <tr>
      <td>
        [$char_desc_fullness]
      </td>
      <td align="right">
        {$char.fullness}/{$constants.STOMACH_CAPACITY} g
        <form method=post action='index.php?page=purge'>
          <input type="submit" class="button_charmenu" value="[$purge]">
        </form>
      </td>
    </tr>
  {/if}
  </table>

  {if $lookingAtYourself}
  {include file="template.title.[$lang].tpl" title="[$title_char_desc_current_skills]"}
  <div class="page skillsContainer">
    <div class="skillsColumn">
      <table style="margin: auto">
      {foreach $skills.evenLines as $evenLine}
        <tr>
          <td>
            <CANTR REPLACE NAME=skill_{$evenLine.type}></td><td>&mdash; <CANTR REPLACE NAME=skill_adjective_{$evenLine.value}>
          </td>
        </tr>
      {/foreach}
      </table>
    </div><!--
    --><div class="skillsColumn">
      <table style="margin: auto">
        {foreach $skills.oddLines as $oddLine}
          <tr>
            <td>
              <CANTR REPLACE NAME=skill_{$oddLine.type}></td><td>&mdash; <CANTR REPLACE NAME=skill_adjective_{$oddLine.value}>
            </td>
          </tr>
        {/foreach}
      </table>
    </div>
  </div>
  {/if}
{/if}

{include file="template.title.[$lang].tpl" title="[$title_char_desc_change_name]"}

<div class="page">
<table>
  <tr>
    <td>
      [$char_desc_change_1] [$char_desc_change_2]:<br />
      <form method="post" action="index.php?page=name">
      <input type="hidden" name="target_id" value="{$char.id}" />
      <input type="hidden" name="type" VALUE="1" />
      <input type="hidden" name="next" VALUE="char.events" />
      <input type="text" style="width: 100%" value="{$char.nameInForm}" name="name" />

      <small><br />[$char_desc_change_3]<br />
        <strong>&lt;CANTR CHARDESC&gt;</strong><br />
        [$char_desc_change_4]:<br /><i>[$char_desc_change_6] (&lt;CANTR CHARDESC&gt;)</i><br />
        [$char_desc_change_5]<br /><i>[$char_desc_change_6] ([$char_a_man_in_his_twenties]).</i><br />
      </small>
    </td>
  </tr>

  <tr>
    <td>
      <br />[$char_personal_desc_change]
      <textarea name="personalDesc" rows="4" style="width: 100%;">{$you.yourDescription}</textarea>
    </td>
  </tr>
  <tr>
    <td align="right">
      <input type=submit class="button_charmenu" value="[$button_char_desc_change]">
      </form>
    </td>
  </tr>

{if $lookingAtYourself}
  {if $you.isDescriptionAllowed}
    <tr>
      <td>
        <p>[$char_public_desc_change]</p>
      </td>
    </tr>
    <form method="post" action="index.php?page=publicdesc">
    <tr>
      <td>
        <input type="hidden" name="id" value="{$char.id}" />
        <input type="hidden" name="next" value="char.events" />
        <textarea name="publicDesc" rows="4" style="width: 100%">{$char.description}</textarea>
      </td>
    </tr>
    <tr>
      <td align='right'>
        <input type="submit" class="button_charmenu" value="[$button_char_desc_change]">
      </td>
    </tr>
    </form>
    <tr>
      <td>
        <p>
          [$char_public_desc_guidelines_1]
          <a href='index.php?page=publicdesc_guide&ocharid={$char.id}'>[$char_public_desc_guidelines_link]</a>
          [$char_public_desc_guidelines_2]
        </p>
      </td>
    </tr>
  {else}
    <tr>
      <td>
        <p>
          [$char_public_desc_disallowed]
        </p>
      </td>
    </tr>
  {/if}
{/if}
  <tr>
    <td align="center">
      <br /><br />
      <a href="index.php?page=char.events">
        <img src="[$_IMAGES]/button_back2.gif" alt="[$button_char_go_back]">
      </a>
    </td>
  </tr>
</table>
</div>
