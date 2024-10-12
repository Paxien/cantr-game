{include file="template.title.[$lang].tpl" title="[$title_change_object_description]"}

<div class="page">
<form method="post" action="index.php?page=keytag" onsubmit="return document.getElementById('descArea').value.length <= {$DESC_MAX_LEN}">
  <table>
    <tr>
      <td>
        [$you_change_object_desc] <CANTR OBJNAME ID={$object_id}>{if $OLDNAME}<span class="txt-label"> &mdash; {$OLDNAME}</span>{/if}
      </td>
    </tr>
    <tr>
      <td>
        [$object_desc_enter_desc]
      </td>
    </tr>
    <tr>
      <td>
        <textarea onkeyup="numChars()" id="descArea" name="description" cols="80" rows="4"></textarea><br>
        <span id="charsLeft">{$DESC_MAX_LEN}</span> [$desc_chars_left]
        <a href="index.php?page=objdesc_guide">&#91;[$link_objdesc_guide]&#93;</a>
        <input type="hidden" name="object_id" value="{$object_id}">
        <input type="hidden" name="data" value="yes">
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
  var charLimit = {$DESC_MAX_LEN};
  var charsLeftNode = document.getElementById('charsLeft');
  var descAreaNode = document.getElementById('descArea');
  {literal}
  function numChars() {
    charsLeftNode.textContent = charLimit - descAreaNode.value.length;
  }
  {/literal}
</script>
