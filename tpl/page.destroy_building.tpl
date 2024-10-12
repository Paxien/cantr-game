{include file="template.title.[$lang].tpl" title="[$title_building_destruction]"}

<div class="page">
  <div>
    [$page_going_to_destroy_building] <CANTR LOCNAME ID={$buildingId}> (<CANTR LOCDESC ID={$buildingId}>).<br>
    [$page_building_destruction_text]<br>
    {if $requiredTools}
      <CANTR REPLACE NAME=project_destruction_required_tools TOOLS={$requiredTools}>
    {/if}
  </div>
  <div class="centered">
    <form method="post" action="index.php?page=destroy_building">
      <input type="hidden" name="data" value="yes" />
      <input type="hidden" name="building" value="{$buildingId}" />
      <a href="index.php?page=char.buildings"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"/></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$button_continue]">
    </form>
  </div>
</div>
