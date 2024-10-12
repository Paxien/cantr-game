{*personal data*}

{include file="template.title.[$lang].tpl" title="[$title_settings_personal]"}

<div class="page">
  <form method="post" action="index.php?page=settings&category=profile">
    <input type="hidden" name="data" value="personal">
    <table>
      <tr>
        <td width="200" align="right">[$form_id]:</td>
        <td>
          {$personal.id}
        </td>
      </tr>
      <tr>
        <td width="200" align="right">[$form_username]:</td>
        <td>
          <input type="text" name="username" value="{$personal.username|escape}">
        </td>
      </tr>
      <tr>
        <td width="200" align="right">[$form_firstname]:</td>
        <td>
          <input type="text" name="firstname" value="{$personal.firstname|escape}">
        </td>
      </tr>

      <tr>
        <td align="right">[$form_lastname]:</td>
        <td>
          <input type="text" name="lastname" value="{$personal.lastname|escape}">
        </td>
      </tr>

      <tr>
        <td align="right">[$form_language]:</td>
        <td>
          <select name="language">
            {foreach from=$languages_list key=lang_id item=lang_name}
              <option value="{$lang_id}"{if $lang_id==$personal.language_id} selected{/if}>{$lang_name}</option>
            {/foreach}
          </select>
        </td>
      </tr>
      <tr>
        <td align="right">[$form_country]:</td>
        <td>
          <input type="text" name="country" value="{$personal.country|escape}">
        </td>
      </tr>

      <tr>
        <td align="right">[$organisation_irc_nick]:</td>
        <td><input type="text" name="ircnick" value="{$personal.irc_nick|escape}"></td>
      </tr>

      <tr>
        <td align="right">[$organisation_forum_nick]:</td>
        <td><input type="text" name="forumnick" value="{$personal.forum_nick|escape}"></td>
      </tr>
      <tr>
        <td></td>
        <td>
          <input type="submit" value="[$settings_profile_change_personal]" class="button_charmenu">
        </td>
      </tr>
    </table>
  </form>
</div>

{* new email*}

{include file="template.title.[$lang].tpl" title="[$title_settings_account]"}

<div class="page">
  <form method="post" action="index.php?page=settings&category=profile">
    <input type="hidden" name="data" value="newemail">
    <table>
      <tr>
        <td width="200" align="right">[$form_email]:</td>
        <td>{$email}</td>
      </tr>
      <tr>
        <td align="right">[$form_new_email]:</td>
        <td>
          <input type="text" name="new_email">
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <input type="submit" value="[$settings_profile_change_email]" class="button_charmenu">
        </td>
      </tr>
    </table>
  </form>

  {* new email - validate *}

  {if $tovalidate}
    <br>
    <form method="post" action="index.php?page=settings&category=profile">
      <input type="hidden" name="data" value="validateemail">
      <table>
        <tr>
          <td width="200" align="right">[$form_new_email]:</td>
          <td>{$newemail}</td>
        </tr>
        <tr>
          <td align="right">[$form_validation_code]:</td>
          <td>
            <input size="4" type="text" name="validation_code">
          </td>
        </tr>
        <tr>
          <td colspan="2">
            [$settings_profile_email_text_1]
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            <input type="submit" value="[$settings_profile_validate_email]" class="button_charmenu">
          </td>
        </tr>
      </table>
    </form>
  {/if}

  {* password change *}
  <br>

  <form method="post" action="index.php?page=settings&category=profile">
    <input type="hidden" name="data" value="password">
    <table>
      <tr>
        <td width="200" align="right">[$form_old_password]:</td>
        <td><input type="password" name="old_password"></td>
      </tr>

      <tr>
        <td align="right">[$form_new_password]:</td>
        <td>
          <input type="password" name="new_password">
        </td>
      </tr>

      <tr>
        <td align="right">[$form_retype_new_password]:</td>
        <td>
          <input type="password" name="retype_new_password">
        </td>
      </tr>
      <tr>
        <td colspan="2">
          [$settings_profile_password_text_1]
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <input type="submit" value="[$settings_profile_change_password]" class="button_charmenu">
        </td>
      </tr>
    </table>
  </form>
</div>

{* on leave *}

{include file="template.title.[$lang].tpl" title="[$title_settings_onleave]"}

<div class="page">
  <form method="post" action="index.php?page=settings&category=profile">
    <input type="hidden" name="data" value="onleave">
    <table>
      <tr>
        <td width="200" align="right">[$form_status]:</td>
        <td>
          <select name="onleave">
            <option value="0"{if !$onleave_days} selected{/if}>[$status_active]</option>
            <option value="1"{if $onleave_days} selected{/if}>[$status_onleave]</option>
          </select>
        </td>
      </tr>
      <tr>
        <td align="right">[$settings_profile_onleave_text_1]</td>
        <td>
          <input type="text" name="days" size="10" value="{$onleave_days}">
        </td>
      </tr>
      <tr>
        <td colspan="2">
          [$settings_profile_onleave_text_2]
        </td>
      </tr>
      <tr>
        <td></td>
        <td><input type="submit" value="[$settings_profile_change_onleave]" class="button_charmenu"></td>
      </tr>
    </table>
  </form>
</div>

{include file="template.title.[$lang].tpl" title="[$title_settings_unsubscribe]"}

<div class="centered">
  <a href="index.php?page=unsubscribe"><img src="[$_IMAGES]/button_unsubscribe.gif" title="[$title_settings_unsubscribe]"/></a>
</div>

{include file="template.title.[$lang].tpl" title="[$title_settings_unsub_lock]"}

<div class="page">
  <form method="post" action="index.php?page=settings&category=profile">
    <input type="hidden" name="data" value="unsub_countdown">
    <table>
      <tr>
        <td colspan="2">
          [$form_unsub_countdown]
        </td>
      </tr>
      <tr>
        <td width="200" align="right">[$form_enable_countdown]:</td>
        <td>
          <select name="unsub_countdown">
            <option value="0"{if !$unsub_countdown} selected{/if}>[$unsub_countdown_disabled]</option>
            <option value="1"{if $unsub_countdown} selected{/if}>[$unsub_countdown_enabled]</option>
          </select>
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <button class="button_charmenu">[$settings_profile_change_unsub]</button>
        </td>
      </tr>
    </table>
  </form>
</div>

{include file="template.title.[$lang].tpl" title="[$title_other_options]"}

<div class="page">
  <form method="post" action="index.php?page=settings&category=profile">
    <input type="hidden" name="data" value="autosend_reports">
    <table>
      <tr>
        <td width="200" align="right">[$form_autosend_reports]:</td>
        <td>
          <select name="autosend_reports">
            <option value="0"{if !$autosend_reports} selected{/if}>[$unsub_countdown_disabled]</option>
            <option value="1"{if $autosend_reports} selected{/if}>[$unsub_countdown_enabled]</option>
          </select>
        </td>
      </tr>
      <tr>
        <td></td>
        <td><input type="submit" value="[$settings_profile_change_other_options]" class="button_charmenu"></td>
      </tr>
    </table>
  </form>
</div>

{if $assignments}
  {include file="template.title.[$lang].tpl" title="[$title_settings_assignments]"}
  <div class="page" style="font-size:15pt;">
    {foreach $assignments as $assignment}
    <CANTR REPLACE NAME=settings_profile_of_council STATUS={$assignment.status_name}{if $assignment.status_id == 4}({$assignment.special}){/if} COUNCIL={$assignment.council}><br>
      {/foreach}
  </div>
{/if}

{if $privileges || $ce_privileges}
  {include file="template.title.[$lang].tpl" title="[$title_settings_privileges]"}
  <div class="page" style="font-size:12pt;">
    <ul>
      {foreach from=$privileges item=privilege}
        <li>{$privilege}</li>
      {/foreach}
      {foreach from=$ce_privileges item=privilege}
        <li>[cantr explorer] {$privilege}</li>
      {/foreach}
    </ul>
  </div>
{/if}

<div class="centered">
  <a href="index.php?page=player" class="button_charmenu">[$back_to_player]</a>
</div>
