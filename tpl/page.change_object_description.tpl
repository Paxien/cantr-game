{include file="template.title.[$lang].tpl" title="[$title_change_object_description]"}

<div class="page">
  <form method="post" action="index.php?page=changeobjdesc" onsubmit="return document.getElementById('descArea').value.length <= {$DESC_MAX_LEN}">
  <table>
    <tr>
      <td>
        [$object_desc_enter_desc]
      </td>
    </tr>
    <tr>
      <td>
        <textarea id="descArea" name="description" cols="80" rows="4"></textarea><br>
        <span id="charsLeft">{$DESC_MAX_LEN}</span> [$desc_chars_left]
        <a href="index.php?page=objdesc_guide">&#91;[$link_objdesc_guide]&#93;</a>
        <input type="hidden" name="object_id" value="{$tool_id}">
        <input type="hidden" name="data" value="yes"><br><br>
      </td>
    </tr>
    <tr>
      <td>
        [$object_desc_select_object]
        <ul class="plain" style="margin: 10px">
        {foreach from=$objects key=isInv item=objGroup}
          {if $objGroup|@count > 0}
            <li>{if $isInv}[$page_store_2]{else}[$page_store_4]{/if}</li>
            {foreach from=$objGroup item=object}
              <li><label style="cursor:pointer"><input type="radio" name="target_id" value="{$object.id}"><CANTR OBJNAME ID={$object.id}>{if $object.oldDesc} <span class="txt-label">&mdash; {$object.oldDesc}{/if}</label></li>
            {/foreach}
          {/if}
        {/foreach}
        </ul>
      </td>
    </tr>
    <tr>
      <td align="center">
        <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_inventory]"/></a>
        <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$button_store_keytag]">
      </td>
    </tr>
  </table>
</form>
</div>

<script type="text/javascript">
  var CHAR_LIMIT = {$DESC_MAX_LEN};
  {literal}
  $(function() {
    $("#descArea").keyup(function() {
      $("#charsLeft").text(CHAR_LIMIT - $("#descArea").val().length);
    });
    $("input[name='target_id']").first().click();
  });
  {/literal}
</script>
