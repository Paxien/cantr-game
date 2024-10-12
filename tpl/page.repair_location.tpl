{include file="template.title.[$lang].tpl" title="[$title_repair_location]"}

<div class="page">
  <p>
    <CANTR REPLACE NAME=repair_location_text NAME={urlencode($name)}>
  </p>
  <form method="post" action="index.php?page=repair_location&data=yes">
    <table class="table-bordered">
      <tr>
        <td>[$repair_location_raw]</td>
        <td>[$repair_location_singular_cost]</td>
        <td>[$repair_location_recursively_cost]</td>
        <td>[$repair_location_raws_on_ground]</td>
      </tr>
        {assign var="anyRawMissingForRepairWithSublocations" value=false}
        {foreach $rawsWithSublocations as $rawName => $amount}
          <tr>
            <td>{$rawName}</td>
            <td {if $availableOnGround.$rawName < $raws.$rawName}style="color:red"{/if}>{$raws.$rawName|default:0}</td>
            <td {if $availableOnGround.$rawName < $amount}style="color:red"
              {assign var="anyRawMissingForRepairWithSublocations" value=true}
              {/if}>{$amount}</td>
            <td>{$availableOnGround.$rawName|default:0}</td>
          </tr>
        {/foreach}
      <tr>
        <td style="border: 0"></td>
        <td style="border: 0; width:33%">
          <input type="submit" name="repair" class="button_charmenu" style="font-size: 15pt"
                 value="[$location_repair_button] {if $leastAbundandRawPercent < 100}({$leastAbundandRawPercent}%){/if}">
        </td>
        <td colspan="2" style="border: 0; width:67%">
          {if $anyRawMissingForRepairWithSublocations}
            [$cannot_repair_recursively_raws_missing]
          {else}
          <label style="cursor: pointer">
            <input type="checkbox" name="recursively"/>[$repair_location_recursively]
          </label>
          {/if}
        </td>
      </tr>
    </table>
  </form>
  <div style="margin-top:30px">
    <a class="ghostButton" style="padding:3px"
       href="index.php?page=char.description">[$plain_button_back]</a>
  </div>
</div>
</div>
