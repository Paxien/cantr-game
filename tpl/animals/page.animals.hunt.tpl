{include file="template.title.[$lang].tpl" title="$locname: [$title_description]"}

<div class="page" style="text-align:center;" id="navigationSubpanel">
  <a href="index.php?page=char.description" class="button_charmenu">[$page_location_subcategory_description]</a>
  <a href="index.php?page=animals&subpage=hunt" class="button_charmenuactive">[$page_location_subcategory_hunting]</a>
  <a href="index.php?page=animals&subpage=domestication" class="button_charmenu">[$page_location_subcategory_domestication]</a>
</div>

<div class="page huntingContainer">
    <form method="post" action="index.php?page=hitanimal">
      <div class="huntingAnimals">
      <span class="listCaption">[$header_animals]:</span>
        <ul class="plain animalList">
        {foreach from=$animals_data item=animal}
          <li>
            <a href="index.php?page=pointat&data=yes&to_animal={$animal.id}"><img src="[$_IMAGES]/button_tiny_point.png" style="margin-bottom: 3px;" align="absmiddle" title="[$alt_point_animal]" /></a>
            {if $animal.can_be_hunted}
              <label><input type="checkbox" {if !$animal.is_domesticated}class="wildPacks"{/if} name="pack[]" value="{$animal.id}" />{$animal.number} {$animal.name}</label>
            {else}
              {$animal.number} {$animal.name}
            {/if}
          </li>
        {/foreach}
        {if $packs_to_hunt > 1}
        <li>
          <label><input type="checkbox" class="selectHunt" value="true" /> [$form_all]</label>
        </li>
        <li>
          <label><input type="checkbox" class="selectHunt" value="false" /> [$form_none]</label>
        </li>
        {/if}
        </ul>
      </div><!--
      --><div class="huntingWeapon">
        [$form_weapon]: <select name="tool">
        {foreach from=$weapons item=weapon}
        <option value="{$weapon.id}">{$weapon.name}</option>
        {/foreach}
        </select>
        <br><br>
        [$form_force]: <br>
        <ul class="plain">
        {section name=flist start=0 loop=11 step=1}
          <label>
            <input type="radio" name="force" value="{$smarty.section.flist.index}"
            {if $smarty.section.flist.last} checked{/if} /> {math equation="x*10" x=$smarty.section.flist.index } %
          </label> <br>
        {/section}
        </ul>
        {if $packs_to_hunt > 0}
          <div style="text-align:center;margin:20px;"><input style="margin:auto;" type="image" src="[$_IMAGES]/button_small_hit.gif" title="[$alt_hit_animal]"></div>
        {/if}
      </div>
    </form>
  </div>

<script type="text/javascript" src="[$JS_VERSION]/js/page.animals.hunt.js"></script>
