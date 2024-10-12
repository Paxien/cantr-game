{foreach from=$vehicles key=vehicleGroupName item=vehicleGroup}
  {if count($vehicleGroup) > 0}
  {capture name="groupName"}<CANTR REPLACE NAME=title_{$vehicleGroupName}>{/capture}
  {include file="template.title.[$lang].tpl" title="`$smarty.capture.groupName`"}

    <div class="page">
    <table>

    {foreach from=$vehicleGroup item=vehicle}
       <tr>
        <td width=30 valign="top">
          <form method="post" action="index.php?page=pointat">
            <input type="hidden" name="to_building" value="{$vehicle->id}">
            <input type="image" src="[$_IMAGES]/button_small_pointat.png" title="[$alt_pointat_vehicle]">
          </form>
        </td>
        <td width=30 valign="top">
          <form method="post" action="index.php?page=move">
            <input type="hidden" name="target" value="{$vehicle->id}">
            <input type="image" src="[$_IMAGES]/button_small_enter.gif" title="[$alt_enter_vehicle]">
          </form>
        </td>
        <td width="30" valign="top">
          <form method="post" action="index.php?page=pull_out">
            <input type="hidden" name="from" value="{$vehicle->id}">
            <input type="image" src="[$_IMAGES]/button_small_pull_out.gif" title="[$alt_pull_out]">
          </form>
        </td>
        {if $vehicleGroupName == "animals"}
          {if $vehicle->can_unsaddle}
          <td width="30" valign="top">
            <form method="post" action="index.php?page=animal_unsaddling">
              <input type="hidden" name="vehicle" value="{$vehicle->id}">
              <input type="image" src="[$_IMAGES]/button_small_horse.gif" title="[$alt_unsaddle_steed]">
            </form>
          </td>
          {else}
          <td width="30" valign="top">
            <form method="post" action="index.php?page=steed_adopt">
              <input type="hidden" name="vehicle" value="{$vehicle->id}">
              <input type="image" src="[$_IMAGES]/button_small_adopt_animal.gif" title="[$alt_adopt_steed]">
            </form>
          </td>
          {/if}
        {else}
        <td width="30" valign="top">
          {if $vehicle->isDisassemblable}
            <form method="post" action="index.php?page=disassemble_vehicle">
              <input type="hidden" name="vehicle" value="{$vehicle->id}">
              <input type="image" src="[$_IMAGES]/button_small_dest.gif" title="[$alt_disassemble_vehicle]">
            </form>
          {/if}
        </td>
        {/if}
        <td>
          <CANTR LOCNAME ID={$vehicle->id}> ({if !empty($vehicle->det)}{$vehicle->det} {/if}<CANTR REPLACE NAME=item_{$vehicle->typename}_b>)
          {$vehicle->sails}
          {foreach from=$vehicle->signs item=sign}
            <p class="sign">[ {$sign} ]</p>
          {/foreach}
        </td>
       </tr>
    {/foreach}

    </table>
    </div>
  {/if}
{/foreach}

{if $buildings}
  {if $charlocationtype == 1}
    {include file="template.title.[$lang].tpl" title="[$title_buildings]"}
  {else}
    {include file="template.title.[$lang].tpl" title="[$title_other_rooms]"}
  {/if}

  <div class="page">
  <table>

  {foreach from=$buildings item=building}
    <tr>
      <td width="30" valign="top">
        <form method="post" action="index.php?page=pointat">
          <input type="hidden" name="to_building" value="{$building->id}">
          <input type="image" src="[$_IMAGES]/button_small_pointat.png" title="[$alt_pointat_building]">
        </form>
      </td>

      <td width="30" valign="top">
        <form method="post" action="index.php?page=move">
          <input type="hidden" name="target" value="{$building->id}">
          <input type="image" src="[$_IMAGES]/button_small_enter.gif" title="[$alt_enter_building_or_room]">
        </form>
      </td>

      <td width="30" valign="top">
        <form method="post" action="index.php?page=knock">
          <input type="hidden" name="building" value="{$building->id}">
          <input type="image" src="[$_IMAGES]/button_small_knock.gif" title="[$alt_knock_door]">
        </form>
      </td>

      <td width="30" valign="top">
        <form method="post" action="index.php?page=pull_out">
          <input type="hidden" name="from" value="{$building->id}">
          <input type="hidden" name="return" value="char.buildings">
          <input type="image" src="[$_IMAGES]/button_small_pull_out.gif" title="[$alt_pull_out]">
        </form>
      </td>
      
      <td width="30" valign="top">
      {if $building->isDestroyable}
        <form method="post" action="index.php?page=destroy_building">
          <input type="hidden" name="building" value="{$building->id}">
          <input type="image" src="[$_IMAGES]/button_small_hit.gif" title="[$alt_destroy_building]">
        </form>
      {/if}
      </td>
      
      <td>
      {$building->ncount}&nbsp;&nbsp;<CANTR LOCNAME ID={$building->id}> ({if !empty($building->det)}{$building->det} {/if}<CANTR REPLACE NAME=item_{$building->typename}_b>)
        {foreach from=$building->signs item=sign}
          <p class="sign">[ {$sign} ]</p>
        {/foreach}
        {if $building->haswindow}<p class="sign">{if $building->hasopenwindow}[$page_window_open]{else}[$page_window_closed]{/if}</p>{/if}
      </td>
    </tr>
  {/foreach}

  </table>
  </div>
{/if}
