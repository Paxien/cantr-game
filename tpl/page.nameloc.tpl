{include file="template.title.[$lang].tpl" title="[$title_change_location_name]"}

<div class='page'>
  <table>
    <tr>
      <td>
        <form method=post action="index.php?page=name">
          <CANTR REPLACE NAME=page_change_location_1 NAME={$oldname_enc}><BR>
            <input type="text" size="40" name="name" value="{$oldname}">
            <input type="hidden" name="target_id" value="{$id}">
            <input type=submit name="submit" class="button_charmenu" style="margin-left:10px" value="[$button_store]">
            <input type="hidden" name="next" value=char>
            <input type="hidden" name="type" value=2>
        </form>
      </td>
    </tr>
    {if $signs_present}
      <tr>
        <td>
          [$page_change_signs_1]
        </td>
      </tr>
      {foreach $signs as $sign}
        <tr>
          <td>
            <form method=post action="index.php?page=name">
              <input type="hidden" name="order" value={$sign->signorder}>
              <input type="hidden" name="next" value="char">
              <input type="hidden" name="type" value="2">
              <input type=submit name="submit" value="[$button_store]">
              {$sign->signorder}) <p class="sign_inline">[ {$sign->name} ]</p>
            </form>
          </td>
        </tr>
      {/foreach}
      {if !$signwriting_ok}
        <tr>
          <td><br>[$page_signs_have_no_correct_tools]</td>
        </tr>
      {else}
        <tr>
          <td><br>[$page_signs_have_correct_tools]</td>
        </tr>
        {if !$signs_alterable}
          <tr>
            <td><br>[$page_signs_not_alterable_here]</td>
          </tr>
        {/if}
      {/if}

      {if $sign_project}
        {include file="template.title.[$lang].tpl" title="[$page_signs_existing_project_exists]"}
        <tr>
          <td><a href="index.php?page=infoproject&project={$sign_project->getId()}">{$sign_project->getName()}</a></td>
        </tr>
      {/if}
    {/if}

  </table>
</div>

{if $signs_present && !$sign_project && $signwriting_ok && $signs_alterable}

{include file="template.title.[$lang].tpl" title="[$page_signs_setup_sign_project]"}

<div class='page'>
  <table>
    <tr>
      <td colspan="2">
        [$page_signs_project_instructions]
      </td>
    </tr>
    <tr>
      <td>
        <form method=post action="index.php?page=nameloc&id={$id}">
          <input type="hidden" name="project_data" value="1">
          <label>
            <input type="radio" name="type" value="1">
            [$page_signs_change_sign]
          </label>
      </td>
      <td>#<select name="change_sign">
          {foreach $signs as $sign}
            <option value="{$sign->signorder}">{$sign->signorder}</option>
          {/foreach}
        </select> [$page_signs_new_text]<input type="text" size="40" name="changetext">
      </td>
    </tr>
    {if $signs|count > 1}
      <tr>
        <td>
          <label>
            <input type="radio" name="type" value="2">
            [$page_signs_remove_sign]
          </label>
        </td>
        <td>
          #<select name="remove_sign">
            {foreach $signs as $sign}
              <option value="{$sign->signorder}">{$sign->signorder}</option>
            {/foreach}
          </select>
        </td>
      </tr>
      <tr>
        <td>
          <label>
            <input type="radio" name="type" value="3">
            [$page_signs_move_sign]
          </label>
        </td>
        <td>#<select name="move_sign">
            {foreach $signs as $sign}
              <option value="{$sign->signorder}">{$sign->signorder}</option>
            {/foreach}
          </select> [$page_signs_to_position] <select name="new_sign_position">
            {foreach $signs as $sign}
              <option value="{$sign->signorder}">{$sign->signorder}</option>
            {/foreach}
          </select>
        </td>
      </tr>
    {/if}
    <tr>
      <td>
        <label>
          <input type="radio" name="type" value="4">
          [$page_signs_add_new]
        </label>
      </td>
      <td>[$page_signs_new_text] <input type="text" size="40" name="newsigntext"> [$page_signs_in_position] <select
            name="insert_sign_position">
          {foreach $signs as $sign}
            <option value="{$sign->signorder}">{$sign->signorder}</option>
          {/foreach}
          <option value="{$signs|count + 1}">{$signs|count + 1}</option>
        </select>

      </td>
    </tr>
    <tr>
      <td align="right">
        <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]"></form>
      </td>
    </tr>
  </table>
  {/if}
  <div class="centered">
    <a href="index.php?page={$lastpage}"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"></a>
  </div>
</div>
