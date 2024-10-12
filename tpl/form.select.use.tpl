{include file="template.title.[$lang].tpl" title="[$title_machine_use]"}

<form method="post" action="index.php?page=use&object={$object}">

  <div class="page">
    [$form_machine_use]
    <ul class="inlinePlain">
    {foreach name=outer from=$projects item=projGroup key=outputRawName}
      <li style="font-weight:bold;margin-top:7px;"><CANTR REPLACE NAME=raw_{$outputRawName}>:</li>
      <ul class="plain">
      {foreach name=inner from=$projGroup item=project}
        <li>
          <label style="cursor:pointer;"><input type="radio" name="choice" value="{$project.id}"
                {if $smarty.foreach.outer.first && $smarty.foreach.inner.first} checked{/if}/>
            {$project.name}
          {if $project.raws}
            <span class="project_resources_list"> ([$machine_project_requires] {strip}
            {foreach name=raws_list from=$project.raws key=rawName item=hasInInventory}
              <span {if !$hasInInventory}class="raw_missing"{/if}>{$rawName}</span>{if !$smarty.foreach.raws_list.last}, {/if}
            {/foreach}
            ){/strip}</span>
          {/if}
          </label>
        </li>
      {/foreach}
      </ul>
    {/foreach}
    </ul>
    <div class="centered">
      <a href="index.php?page=char.objects"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]"></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$form_machine_use_go]">
    </div>
  </div>
</form>
