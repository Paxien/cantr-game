<div class="page">
  <p>[$charsettings_description_1]</p>
  <label style="cursor:pointer" onMouseDown="return false;"><input type="checkbox" id="select_all_activity"/>[$select_all]</label>

  <table>
    <tr style="vertical-align: top">
      <td>
        {foreach from=$eventTypeGroups item=item key=key}
        {if ( ($key + 1) % $_COLUMN_SIZE ) == 0 }
      </td>
      <td>
        {/if}
        <label style="cursor:pointer"><input type='checkbox' class="selectActivity" name='{$item->type}'
                                             {if $item->selected}checked{/if}>{$item->description}</label><br>
        {/foreach}
      </td>
    </tr>
  </table>
</div>


  <script type="text/javascript">
    {literal}
    $(function() {
      $("#select_all_activity").change(function() {
        $(".selectActivity").prop("checked", $(this).is(":checked"));
      });
    });
    {/literal}
  </script>
