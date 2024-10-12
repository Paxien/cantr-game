{include file="template.title.[$lang].tpl" title="[$title_contact]"}

<form method="post" action="index.php?page=contact">
  <div class="page">
  <table>
    <tr>
      <td width="100%" colspan="2">[$contact_page_instructions]
      </td>
    </tr>
    {if $displayerror}
      <tr>
        <td width="100%" align="center" colspan="2" style="height:30px;background:#ff0000">{$error_message}</td>
      </tr>
    {/if}
    {if $loggedin}
      <tr>
        <td align="right" width="200">[$contact_from]:</td>
        <td align="left">
          <label name="mail_from2"><strong>{$player_details}</strong> </label>
        </td>
      </tr>
    {else}
      <tr>
        <td align="right" width="200">[$contact_email_address]:</td>
        <td align="left">
          <input type="text" maxlength="200" name="mail_from" value="{$mail_from}"/>
        </td>
      </tr>
      <tr>
        <td align="right" width="200">[$contact_confirm_email]:</td>
        <td align="left">
          <input type="text" maxlength="200" name="mail_confirm" value="{$mail_confirm}"/>
        </td>
      </tr>
    {/if}

    <tr>
      <td align="right">[$contact_to]:</td>
      <td align="left">
        <select name="email_to" width="100%">
          {foreach from=$departments item=dept}
            <option value="{$dept->id}"{$dept->selected}>{$dept->name}</option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr>
      <td align="right">[$contact_subject]:</td>
      <td align="left">
        <input type="text" maxlength="200" name="mail_subject" value="{$mail_subject}"/>
      </td>
    </tr>
    <tr>
      <td colspan="2">[$form_enter_message]:</td>
    </tr>
    <tr>
      <td colspan="2">
        <textarea name="message" rows="15" cols="80" wrap="virtual" style="width: 100%;">{$message}</textarea>
      </td>
    </tr>
    {if !$loggedin}
      <tr>
        <td colspan="2"><div class="g-recaptcha" data-sitekey="{$recaptcha_key}"></div></td>
      </tr>
    {/if}
    {if $mailSent}
      <tr>
        <td width="100%" align="center" colspan="2" style="height:30px;background:#00aa00">[$contact_email_sent]</td>
      </tr>
    {/if}
    <tr>
      <td colspan="2" style="text-align:center;">
        <a href="index.php?page={$return_page}"><img src="[$_IMAGES]/button_back2.gif" title="[$contact_button_cancel]"></a>
        <input type="image" src="[$_IMAGES]/button_forward2.gif" name="mail_send" title="[$contact_button_send]">
        <input type="hidden" name="data" value="yes">
      </td>
    </tr>
  </table>
  </div>
</form>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
