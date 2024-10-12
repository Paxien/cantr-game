{include file="template.title.[$lang].tpl" title="[$title_manage_survey]"}
<script type="text/javascript" src="[$JS_VERSION]/js/create_survey.js"></script>

<div class="page">
  <form method="post" action="index.php?page=managesurvey&survey_id={$data.s_id}" onsubmit="return isAccepted()">
    <table>
      <tr>
        <td style="text-align:right;">ID: </td><td>{$data.s_id}</td>
      </tr>
      <tr>
        <td style="text-align:right;">[$page_survey_list_name_abr]: </td><td> <CANTR REPLACE NAME=survey_s_{$data.s_id}></td>
      </tr>
      <tr>
        <td style="text-align:right;">[$page_survey_results_created]: </td><td>{$data.date} </td>
      </tr>
      <tr>
        <td><input type="hidden" name="changed" value="yes" /></td>
        <td><label><input type="checkbox" name="enabled" value="1" {if $data.enabled}checked{/if} /> [$page_survey_results_enabled]</label></td>
      </tr>
      <tr>
        <td style="text-align:right;">[$form_language]: </td>
        <td><select name="s_language">
            {foreach from=$lang_list item=lang key=kid}
              <option value="{$kid}" {if $data.s_language == $kid} selected{/if}>{$lang}</option>
            {/foreach}
          </select>
        </td>
      </tr>
      <tr>
        <td style="text-align:right;">[$page_survey_results_plrs_to_be_surveyed]: </td><td><input type="text" value="{$data.player_ids}" name="surv_player_ids" id="surv_player_ids" size="50" /><input type="button" value="Check" onClick="checkPlayerIds()" /> <span id="isAcc" name="isAcc"></span></td>
      </tr>
      <tr>
        <td colspan="2" style="text-align:center;">
        <a href="index.php?page=surveylist"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_player]"></a>
        <input type="image" src="[$_IMAGES]/button_forward2.gif"></td>
      </tr>
    </table>
  </form>
</div>