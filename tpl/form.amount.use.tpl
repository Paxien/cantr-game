{include file="template.title.[$lang].tpl" title="[$title_select_amount_project]"}

{if $next == "form"}

  <script type="text/javascript">
    var resources = new Array();
    var resNeeded = new Array();
    var output = {$prod};
    var days = {$days};
    var dailyProd = (output/days);
    var k=0;
    {foreach from=$resources key=resName item=resAmount}
      resNeeded[k++] = "{$resName}";
      resources["{$resName}"] = {$resAmount[0]/$days};
    {/foreach}
    
  {literal}
  
    function recount(filled, amt){

      var seldays = 0;
      if (filled == "soutput"){
        seldays = amt/dailyProd;
      }
      else if (filled == "sdays"){
        seldays = amt;
      }
      else {
        seldays = amt/resources[filled];
      }
      
      if (filled != "soutput"){
        var out = Math.floor(dailyProd*seldays);
        if (filled != "sdays"){
          while ( Math.floor( ((out+1)/dailyProd)*resources[filled] ) <= amt &&
          (Math.ceil(((out+1)/dailyProd)*8)/8) == ((Math.ceil(seldays*8))/8) ) {
            out++;
            seldays = out/dailyProd;
          }
        }
        document.getElementById('output').value = out;
      }
      if (filled != "sdays") document.getElementById('days').value = (Math.ceil(seldays*8))/8;
      
      for (var i=0;i<k;i++){
        if (filled != resNeeded[i])
          document.getElementById(resNeeded[i]).value = Math.floor(resources[resNeeded[i]]*seldays);
      }
    }
  </script>
{/literal}
{/if}

 <form method="post" action="index.php?page=use&{$linksParams}">
   <div class="page">
  <table>
    <tr>
      <td colspan="2">
        <input type="hidden" name="data" value="yes">
        <CANTR REPLACE NAME=page_select_amount_project DAYS={$days} GRAMS={$prod} TYPE={$output_name}>
      </td>
    </tr>

    <tr>
      <td style="text-align:right;">
        [$page_project_resource_name]
      </td>
      <td style="padding-left:10px;">
      {if $next == "form"}
        [$page_project_amount]
      {/if}
      </td>
    </tr>
    
  {foreach from=$resources key=resName item=resAmount}
    <tr>
      <td style="text-align:right;" width="350">
        {$resAmount[0]} [$grams_of] <CANTR REPLACE NAME=raw_{$resName}>
      </td>
        <td width="350" style="padding-left:10px;">
          {if $next == "form"}
          <input type="text" name="amount_{$resName}" id="{$resName}" onkeyup="recount('{$resName}', this.value)"> ({$resAmount[1]|default:0})
          {/if}
        </td>
    </tr>
  {/foreach}
  {if $next == "form"}
    <tr>
      <td style="width:350px;text-align:right;">
        <br>
        [$form_amount_make]:
      </td>
      <td style="width:350px; padding-left:10px;">
        <br>
        <input type="text" name="amount" size="30" value="{$amount}" id="output" onkeyup="recount('soutput', this.value)">
      </td>
    </tr>
    <tr>
      <td style="width:350px;text-align:right;">
        [$form_days_of_work]:
      </td>
      <td style="width:350px; padding-left:10px;">
        <input type="text" name="days" size="10" value="{$amount}" id="days" onkeyup="recount('sdays', this.value)">
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <CANTR REPLACE NAME=form_amount_limit GRAMS={math equation="80*$prod"}>
      </td>
    </tr>
  {else}
    <tr><td colspan="2"><CANTR REPLACE NAME=form_amount_fixed_output GRAMS={$prod}></td></tr>
  {/if}
  <tr>
    <td colspan="2">
    {if $show_allocation}
    <table style="width:100%">
      <tr>
        <td colspan="3">
          [$form_resource_allocation_choice]
          <br>
        </td>
      </tr>
      <tr>
        <td style="width:33%" align="left">
          <label>
            <input type="radio" name="resource_allocation" value="none"> [$form_resource_allocation_none]
          </label>
        </td>
        <td style="width:33%" align="left">
          <label>
            <input type="radio" name="resource_allocation" value="full" checked> [$form_resource_allocation_full]
          </label>
        </td>
        <td align="left">
          <label>
            <input type="radio" name="resource_allocation" value="regardless"> [$form_resource_allocation_regardless]
          </label>
        </td>
      </tr>
    </table>
    {else}
    <input type="hidden" name="resource_allocation" value="none">
    {/if}
    </td>
  </tr>
  </table>

  <div class="centered">
    <a href="{if $back_to_objects}index.php?page=char.objects{else}index.php?page=use&object_id={$object}{/if}"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"></a>
    <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
  </div>
 </div>
</form>  