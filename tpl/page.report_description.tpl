{include file="template.title.[$lang].tpl" title="[$report_abuse]"}

<div style="width:700px;margin:auto;">
  <form method="post" action="index.php?page=reportdescription&desc_id={$desc_id}&data=yes">
    <input type="hidden" name="return" value="{$return}" />
    [$report_abuse_text]<br>
    <textarea rows="3" cols="84" disabled>{$reported_text}</textarea> <br><br>
    [$report_abuse_details]<br>
    <textarea rows="3" cols="84" name="reason"></textarea><br>
    
    <div style="margin-top:10px;text-align:center;">
      <a href="index.php?page={$return}"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]" /></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$contact_button_cancel]" />
    </div>
  </form>
</div>