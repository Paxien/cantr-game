{include file="template.title.[$lang].tpl" title="[$title_survey_management]"}
  
<center>
  <table width="1000">
    <tr>
      <td>
        <table border style="margin:auto;">
        <tr>
          <td>[$page_survey_list_number_abr]</td><td>[$page_survey_list_ID_abr]</td><td>[$page_survey_list_name_abr]</td><td>[$page_survey_list_questions_count_abr]</td><td>[$page_survey_list_answers_count_abr]</td><td>[$page_survey_list_creation_date_abr]</td><td>[$page_survey_list_enabled_abr]</td> <td>[$page_survey_list_language_abr]</td> <td>[$page_survey_list_spec_plrs_abr]</td> <td>  </td>
        </tr>
          {foreach from=$surveyList key=i item=row}
          <tr>
            <td>{$i+1}</td> <td>{$row.s_id}</td> <td>{$row.name}</td> <td>{$row.question_count}</td> <td>{$row.answer_count}</td> <td>{$row.date}</td> <td>{$row.enabled}</td> <td>{$row.language}</td> <td>{if $row.specific_players_list}1{else}0{/if}</td> <td> <a href="index.php?page=surveylist&survey_id={$row.s_id}">[$page_survey_list_results]</a> <a href="index.php?page=managesurvey&survey_id={$row.s_id}"><b>[M]</b></a></td>
          </tr>
          {/foreach}
        </table>
        <br>
        
        
        <div style="text-align:center;">
          <a href="index.php?page=createsurvey" class="button_charmenu">[$page_survey_list_create]</a><br><br>
          <a href="index.php?page=player"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_player]"></a>
        </div>
        
      </td>
    </tr>
  </table>
</center>