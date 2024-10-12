<script type="text/javascript">
var charId = {$charId};
var reqConfirm = {$requireConfirmation};
var seenPreview = false;

{literal}
function popup(mylink, windowname)
{
  if (!window.focus) return true;
  var href;
  if (typeof(mylink) == 'string')
    href=mylink;
  else
    href=mylink.href;
  window.open (href, windowname, 'width=400,height=500,scrollbars=yes,resizeable=yes');
  return false;
}

$(function() {
  $("#confirmation").click(function() {
    var noteEmpty = ($('#notetext').val().length == 0) && ($('#notename').val().length == 0);
    if (reqConfirm && !seenPreview && !noteEmpty) {
      return confirm(tagText("confirm_note_no_preview"));
    }
    return true;
  });
});

function showPreview() {
  asyncRequest({
    dataType: "text",
    data: {
      character: charId,
      page: "preview_note",
      notename: $('#notename').val(),
      notetext: $('#notetext').val(),
      diff: $('#useDiff').prop("checked"),
    },
    success: function (ret) {
      ret = $.parseJSON(ret);
      $('#preview-tab').html(ret.look).show();
      $('#preview-code, #preview-diff').hide();
      if (ret.diff != null) {
        $('#preview-diff').html(ret.diff).show();
      } else {
        $('#preview-code').html(ret.code).show();
      }
      seenPreview = true;
    },
  });
}
</script>
{/literal}

<link rel="stylesheet" href="css/editnote.css" type="text/css">
<div class="page">
  <div id="preview-tab"></div>
  <div class="page" id="preview-diff"></div>
  <textarea rows="8" cols="80" id="preview-code" disabled="disabled"></textarea>
</div>

{include file="template.title.[$lang].tpl" title="[$title_write_a_note]"}

<form name="noteform" method="post" action="index.php?page=writenote">
<div class="page">
    <table>
      <tr>
        <td colspan="2" style="text-align: right;">
          <input type="button" value="[$note_preview]" onclick="showPreview()" class="button_charmenu" />
          <label style="cursor:pointer"><input type="checkbox" id="useDiff" />diff</label>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          [$note_validation_info]
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <p>
          {if $isinobject }
            [$note_in_object_description]        
          {else}
            [$note_description]
          {/if}
          </p>  
        </td>
      </tr>
      <tr>
        <td>
          [$note_title]:<br>
        </td>
        <td>
          <input type="text" name="notename" id="notename" value="{$notename}" style="width:80%">
          <a href="/pictures/index.php" onClick="return popup(this, 'notes')">
            <img src="[$_IMAGES]/button_img_up.gif" border="0" title="[$upload_picture]" style="vertical-align: middle;" />
          </a>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <textarea id="notetext" name="notetext" rows="20" style="width:100%" wrap="virtual">{$notetext}</textarea>
          <input type="hidden" name="data" value="yes">
          <input type="hidden" name="object_id" value="{$object_id}">
        </td>
      </tr>
      {if $needsettings }
      <tr>
        <td>
          [$note_setting]:
        </td>
        <td>
          <label><input type="radio" name="setting" value="1" checked>[$note_editable]</label><br>
          <label><input type="radio" name="setting" value="2">[$note_not_editable]</label>
        </td>
      </tr>
      {/if}
      <tr>
        <td colspan="2" align="center">
          <a href="index.php?page={$backLink}">
            <img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"></a>
          <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$note_store]" id="confirmation" />
        </td>
      </tr>
  </table>
</div>
</form>
