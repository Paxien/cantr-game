{include file="template.title.[$lang].tpl" title="[$title_create_survey]"}

<script type="text/javascript" src="[$JS_VERSION]/js/create_survey.js"></script>

<div class="page">
        <form method="post" action="index.php?page=createsurvey" onsubmit="return isAccepted()" >
          <input type="hidden" name="data" value="yes" />
          <input type="hidden" id="n_of_q" name="n_of_q" value="0" />
          <table>
            <tr>
              <td colspan="2">
                Survey guide: <a href="http://forum.cantr.org/viewtopic.php?p=479865#p479865">http://forum.cantr.org/viewtopic.php?p=479865#p479865</a>.
              </td>
            </tr>
            <tr>
              <td style="text-align:right;">[$page_survey_list_name_abr]: </td>
              <td><input type="text" value="name" name="surv_name" size="50" /></td>
            </tr>
            <tr>
              <td style="text-align:right;">[$page_survey_results_plrs_to_be_surveyed]: </td>
              <td><input type="text" value="" name="surv_player_ids" id="surv_player_ids" size="50" />
                <input id="check_players" type="button" value="Check" />
                <span id="isAcc" name="isAcc"></span></td>
            </tr>
            <tr>
              <td style="text-align:right;">[$page_survey_results_enabled]: </td>
              <td><input type="checkbox" name="surv_enabled" checked></td>
            </tr>
            <tr>
              <td style="text-align:right;">[$form_language]: </td>
              <td>
                <select name="surv_lang">
                {foreach from=$langs item=lang key=lid}
                  <option value="{$lid}">{$lang}</option>
                {/foreach}
                </select>
              </td>
            </tr>
            <tr>
              <td style="text-align:right;">[$page_survey_manage_questions]: </td>
              <td><input type="button" value="ADD" onClick="addQuestion()" /> <input type="button" value="DELETE" onClick="delQuestion()" /></td>
            </tr>
          </table>
          <div id="div-questions">
          </div>
          <br><br>
          <div style="text-align:center;"><a href="index.php?page=surveylist"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_player]"></a> <input type="image" src="[$_IMAGES]/button_forward2.gif"></div> <br><br>
        </form>
</div>
