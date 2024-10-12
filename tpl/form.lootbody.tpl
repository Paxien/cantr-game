{include file="template.title.[$lang].tpl" title="[$title_lootbody_cause]"}

<div class="page">
    {if     $bodyage == 1}[$body_older_than_year]
    {elseif $bodyage == 2}[$body_older_than_half_year]
    {elseif $bodyage == 3}[$body_younger_than_half_year]
    {/if}

    {if     $cause == 0}[$body_cause_unknown]
    {elseif $cause == 1}[$body_cause_hit]
    {elseif $cause == 4}[$body_cause_animal_attack]
    {elseif $cause == 6}[$body_cause_starvation]
{else}[$body_cause_heart_attack]
{/if}
<p name="description" rows="4" style="width: 100%;">"<i>{$bodyDescription}<i>"</p>
</div>

{include file="template.title.[$lang].tpl" title="[$title_lootbody_possessions]"}

<div class="page">
<table>
{foreach from=$clothes item=citem}
  <TR>
    <TD style="padding:5px">
      <FORM METHOD="POST" ACTION="index.php?page=loot">
        <INPUT TYPE=image SRC="[$_IMAGES]/button_small_take.gif" title="Take this possession" style="vertical-align:middle">&nbsp;{$citem->name} <span class="txt-label"> - {$citem->description}</span>
        <INPUT TYPE="hidden" NAME="id" VALUE="{$id}">
        <INPUT TYPE="hidden" NAME="clothid" VALUE="{$citem->id}">
      </FORM>
    </TD>
  </TR>
{foreachelse}
  <tr>
    <td>
      [$body_no_clothes]
    </td>
  </tr>
{/foreach}
</table>
</div>

{include file="template.title.[$lang].tpl" title="[$title_char_desc_change_name]"}

<form method="post" action="index.php?page=name">
<div class="page">
<table>
  <tr>
    <td>
      [$char_desc_change_1] [$char_desc_change_2]:<br />

      <input type="hidden" name="target_id" value="{$id}" />
      <input type="hidden" name="type" VALUE="1" />
      <input type="hidden" name="next" VALUE="char.events" />
      <input type="text" style="width: 90%" value="{$bodyName}" name="name" />

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
      <textarea name="personalDesc" rows="4" style="width: 100%;">{$bodyPersonalDescription}</textarea>
    </td>
  </tr>
  <tr>
    <td align="right">
      <input type=submit class="button_charmenu" value="[$button_char_desc_change]">

    </td>
  </tr>
</table>
</div>
</form>