{include file="template.title.[$lang].tpl" title=$survey.name}

<div class="page">
  <form method="POST" action="{$survey.form_action}">
    <input type="hidden" name="survey_sent" value="{$survey.s_id}"> {* used to know if the survey was submitted *}
    {section name=id loop=$survey.questions}
      <div style="border:1px solid #008800;padding:3px;">
        {$survey.questions[id].text}<br>
        {section name=aid loop=$survey.questions[id] max=$survey.questions[id].n}
          {if $survey.questions[id][aid].type == $survey.ANSWER_RADIO}
            <label style="cursor:pointer;"><input type="radio" name="q_{$survey.questions[id].q_id}[]"
                                                  value="{$survey.questions[id][aid].a_id}"> {$survey.questions[id][aid].text}</label>
          {elseif $survey.questions[id][aid].type == $survey.ANSWER_RADIO_TEXT}
            <label style="cursor:pointer;"><input type="radio" name="q_{$survey.questions[id].q_id}[]"
                                                  value="{$survey.questions[id][aid].a_id}"> {$survey.questions[id][aid].text} </label>
            <input type="text" name="text_{$survey.questions[id][aid].a_id}">
          {elseif $survey.questions[id][aid].type == $survey.ANSWER_TEXT}
            <input type="hidden" name="q_{$survey.questions[id].q_id}[]" value="{$survey.questions[id][aid].a_id}">
            <input type="text" name="text_{$survey.questions[id][aid].a_id}">
          {elseif $survey.questions[id][aid].type == $survey.ANSWER_CHECKBOX}
            <label style="cursor:pointer;"><input type="checkbox" name="q_{$survey.questions[id].q_id}[]"
                                                  value="{$survey.questions[id][aid].a_id}"> {$survey.questions[id][aid].text}</label>
          {elseif $survey.questions[id][aid].type == $survey.ANSWER_CHECKBOX_TEXT}
            <label style="cursor:pointer;"><input type="checkbox" name="q_{$survey.questions[id].q_id}[]"
                                                  value="{$survey.questions[id][aid].a_id}"> {$survey.questions[id][aid].text} </label>
            <input type="text" name="text_{$survey.questions[id][aid].a_id}">
          {/if}
          <br>
        {/section}
      </div>
    {/section}
    <br>
    <div class="centered">
      {if $show_back}
        <a href="index.php?page={$back_destination|default:'player'}"><img SRC="[$_IMAGES]/button_back2.gif" title="[$back_to_player]"></a>
      {/if}
      <input type="image" src="[$_IMAGES]/button_forward2.gif">
    </div>
  </form>
</div>
