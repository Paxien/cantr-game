{include file="template.title.[$lang].tpl" title="[$title_survey_results]"}

<style type="text/css" id="survey_filter"></style>
<div class="page">
  <table>
    <tr>
      <td>
        <form method="post" action="index.php?page=surveylist&survey_id={$survey_id}" >
          <p>[$page_survey_results_res_since] <input type="text" name="from_date" value={$from_date}> [$page_survey_results_res_until] <input type="text" name="to_date" value="{$to_date}"><input type="submit" value="ok" class="button_charmenu"> (i.e."now","-30 days","2012-03-02")</p>
        </form>
        <span style="font-size:14pt;">"{$result.name}", {$result.count} [$page_survey_results_answers], [$page_survey_results_created]: {$result.creationDate}, [$page_survey_results_enabled]: {$result.enabled}</span>
        <br>
        {* SPECIFIC PLAYERS TO BE SURVEYED LIST *}
        {if count($result.id_array) > 0} [$page_survey_results_plrs_to_be_surveyed]:
        {foreach from=$result.id_array item=player_id} {$player_id}{/foreach}{/if}
        {* SPECIFIC PLAYERS TO BE SURVEYED LIST END *}
        
        <script type="text/javascript" src="[$JS_VERSION]/js/survey_results_filter.js"></script>
        
        <div style="float:right;margin-right:10px;">
        <form>
          <select id="lang_sel" size="5" onclick="getLang()">
            <option value="0">All</option>
            {foreach from=$result.languages key=lang_id item=abr}
            <option value="{$lang_id}">{$abr}</option>
            {/foreach}
          </select>
        </form>
        </div>
        
        <br>
        {foreach from=$result.questions key=qid item=question}
          <div style="border:2px solid #008800;">
            <span style="font-size:13pt;">[$page_survey_results_question]: {$question.q_text}</span><br>
            
            <table border>
            <tr>
              <td>[$page_survey_results_answers_abr]</td> <td>[$page_survey_results_perc_answers_abr]</td> <td>[$page_survey_results_answer_text]</td>
            </tr>
            {section name=x loop=$question max=$question.n}
            <tr>
              <td>{$question[x].count}</td> <td>{$question[x].percent}</td> <td>{$question[x].a_text}</td>
            </tr>
            {/section}
            </table>
            
            {if count($result.answers.$qid) > 0}
            <span style="font-size:15pt;">[$page_survey_results_text_answers] </span>
            <ol>
              {foreach from=$result.answers.$qid item=text_answer}
              {if !empty($text_answer.content)}
              {assign var=l_id value=$text_answer.language}
              <li class="ans_lang_{$text_answer.language}"> [{$result.languages.$l_id}] {$text_answer.content|escape}</li>
              {/if}
              {/foreach}
            </ol>
            {/if}
          </div>
        {/foreach}
        <br>
        {foreach from=$result.players item=plr_info}
          {assign var="l_id" value=$plr_info.s_lang }
          <div class="ans_lang_{$plr_info.s_lang}" style="border:2px solid #008800;">[{$result.languages.$l_id}] - {$plr_info.date}<br>
          {foreach $plr_info.q_a as  $qid => $answers}
          <hr style="width:60%; color:#00aa00;" />
            <i><CANTR REPLACE NAME=survey_q_{$qid}></i>
            <ul>
            {foreach $answers as $answer}
              <li>{$answer}</li>
            {/foreach}
            </ul>
          {/foreach}
          </div>
        {/foreach}
        <br>
        <center><a href="{$result.backLink}" style="font-size:18px;" class="button_charmenu">[$page_survey_results_hide_result]</a></center>
      </td>
    </tr>
  </table>
</div>