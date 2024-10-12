{include file="template.title.[$lang].tpl" title="$locname: [$title_description]"}

<div class="page" style="text-align:center;" id="navigationSubpanel">
  <a href="index.php?page=char.description" class="button_charmenu">[$page_location_subcategory_description]</a>
  <a href="index.php?page=animals&subpage=hunt" class="button_charmenu">[$page_location_subcategory_hunting]</a>
  <a href="index.php?page=animals&subpage=domestication" class="button_charmenuactive">[$page_location_subcategory_domestication]</a>
</div>



<form action="index.php?page=animals" method="post">
  <input type="hidden" name="data" value="yes" />
  <input type="hidden" name="action_type" value="" />
  <div class="page domesticationContainer">
    <div class="domesticationAnimals">
      <span class="listCaption">[$header_animals]:</span>
      <ul class="plain animalList">
        {foreach from=$animals key=pack_id item=animal}
          <li><label><input type="radio" name="pack_id" value="{$pack_id}" />{$animal.number} {$animal.name} {if $animal.domesticated}({$animal.fullness}){/if}</label></li>
        {/foreach}
      </ul>
    </div><!--
    --><div class="domesticationPanel">
      <ul class="plain" id="selectPanel">
        <li id="taming">[$domestication_action_taming]</li>
        <li id="milking">[$domestication_action_milking]</li>
        <li id="shearing">[$domestication_action_shearing]</li>
        <li id="collecting">[$domestication_action_collecting]</li>
        <li id="healing">[$domestication_action_healing]</li>
        <li id="separating">[$domestication_action_separating]</li>
      </ul>
      <div id="detailsPanel" style="display:none;">
      <div id="continueButton" class="centered" style="display:none;"><input type="submit" class="button_charmenu" value="[$form_continue]" /></div>
      </div>
    </div>
  </div>
</form>

<script type="text/javascript">
var animals = {$animal_actions};
</script>
<script type="text/javascript" src="[$JS_VERSION]/js/page.animals.domestication.js"></script>
