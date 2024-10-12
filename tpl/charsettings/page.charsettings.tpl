{include file="charsettings/page.charsettings.menu.[$lang].tpl"}

<div class="page">
<form id='mainform' action="index.php?page=char.settings" method="post">
  <input type="hidden" name="data" value="yes"/>
  <input type="hidden" name="selectedtab" value="{$selectedtab}"/>

  {if $selectedtab == 0}
    {include file="charsettings/page.charsettings.activity.[$lang].tpl"}
  {elseif $selectedtab == 1}
    {include file="charsettings/page.charsettings.filters.[$lang].tpl"}
  {elseif $selectedtab == 2}
    {include file="charsettings/page.charsettings.other.[$lang].tpl"}
  {elseif $selectedtab == 3}
    {include file="charsettings/page.opt_out_from_spawning.[$lang].tpl"}
  {/if}

  <div class="centered">
    <a href="index.php?page=char.events&object={$object}">
      <img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"></a>
    <input TYPE=image src="[$_IMAGES]/button_forward2.gif" title="[$button_continue]">
  </div>
</form>
</div>