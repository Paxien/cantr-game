{include file="template.title.[$lang].tpl" title="[$title_objects]"}

<div class="page-left">
<table>
  {foreach from=$objects item=obj}
    <tr>
      <td id="object_{$obj.id}" class="obj_object" data-amount="{$obj.amount}" data-unit-weight="{$obj.unitWeight}"
          data-is-quantity="{$obj.isQuantity}">
        <table>
          <tr>
            <td class="action-buttons action-buttons-{$obj.buttons|count}">
            {foreach $obj.buttons as $it}
                <form method="post" action="index.php?page={$it.page}">
                  {foreach from=$it.inputs item=hidval key=hkey}
                    <input type="hidden" name="{$hkey}" value="{$hidval}"/>
                  {/foreach}
                  <input class="action_{$it.page|regex_replace:"/&.*/":""}" type="image" src="[$_IMAGES]/button_small_{$it.img}.gif"
                         title="<CANTR REPLACE NAME={$it.img_title}>"/>
                </form>
            {/foreach}
            </td>
            <td class="obj_name">
              {$obj.name}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  {/foreach}
</table>
</div>

{if $isJsInterface}
  <script type="text/javascript">
    var isInventory = false;
  </script>
  <script type="text/javascript" src="[$JS_VERSION]/js/func.objects_inventory.js"></script>
  <script type="text/javascript" src="[$JS_VERSION]/js/page.objects.js"></script>
  <script type="text/javascript" src="[$JS_VERSION]/js/page.objects_inventory.shorten_desc.js"></script>
{/if}
