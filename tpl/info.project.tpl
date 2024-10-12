<center><table>
{if $chars}
  <tr valign=top><td width=200>[$form_participants]:</td><td width=500>
  {foreach from=$lineSkills item=charSkill}
    {$charSkill}<br>
  {/foreach}
  </td></tr>
{else}
  <tr valign=top><td width=200></td><td width=500></td></tr>
{/if}

{if $initiator}
  <tr><td>[$form_initiator]:</td><td>
  {if $initiatorId}
    <a href="index.php?page=characterdescription&ocharid={$initiatorId}"><CANTR CHARNAME ID={$initiatorId}></a> ({$day}-{$turn})
  {else}
    [$page_project_info_not_here] ({$day}-{$turn})
  {/if}
  </td></tr>
{/if}

<tr><td>[$form_progress]:</td><td>{$progress|number_format:1} [$word_percent]</td></tr>

{if $worktime <= 7}
  <tr><td>[$form_total_time_hours]:</td><td>{$worktime}</td></tr>
{else}
  <tr><td>[$form_total_time_days]:</td><td>{$worktime/8.0}</td></tr>
{/if}

{if $project_info->automatic==0}
  <tr><td>[$form_project_automation]:</td><td>[$form_manual_project]</td></tr>
{elseif $project_info->automatic==1}
  <tr><td>[$form_project_automation]:</td><td>[$form_unattended_project]</td></tr>
{elseif $project_info->automatic==2}
  <tr><td>[$form_project_automation]:</td><td>[$form_assisted_project]</td></tr>
{/if}

{if $project_info->result_description}
  <tr><td>[$form_project_result_description]:</td><td><div class="txt-label"><p>{$project_info->result_description|htmlspecialchars}</div></td></tr>
{/if}

{if $project_info->steps}
  <tr><td>[$form_steps_left]:</td><td>{$project_info->steps}</td></tr>
{/if}

{if $needed}
  <tr valign=top><td>[$form_materials]:</td><td>
  {foreach from=$needed item=req}
    <CANTR REPLACE NAME={$req.rawTag}> (<CANTR REPLACE NAME=page_build_rawneeded AMOUNT1={$req.done} AMOUNT2={$req.needed} AMOUNT3={$req.left}>)<br>
  {/foreach}
  </td></tr>
{/if}

{if $objects}
  <tr valign=top><td>[$form_objects]:</td><td>
  {foreach from=$objects item=object}
    {$object}<br>
  {/foreach}
  </td></tr>
{/if}

{if $objsNear}
  <tr><td> [$required_nearby]: </td><td>
  {foreach from=$objsNear item=objNear}
    {if $objNear.isNear}
      <span class="tool">&#9745;
    {else}
      <span class="tool_missing">&#9744;
    {/if}
    <CANTR OBJNAME ID={$objNear.id}></span><br>
  {/foreach}
  </td></tr>
{/if}

{if $toolsNeeded}
  <tr VALIGN=top><td>[$form_tools]:</td><td>
  {foreach from=$toolsNeeded item=tool}
    {if $tool.isPresent}
      <span class="tool">&#9745;
    {else}
      <span class="tool_missing">&#9744;
    {/if}
    {$tool.name}
    </span><br>
  {/foreach}
  </td></tr>
{/if}

{if $toolBoost}
    <tr valign="top"><td>[$form_additional_tool]:</td>
    <td><CANTR REPLACE NAME={$toolBoost.name}> ({$toolBoost.boost}%)<br>
    </td></tr>
{/if}

{if $quantity}
  <tr><td>[$form_quantity_desired]:</td><td>{$quantity}</td></tr>
{/if}
{if $agriculturalConditions}
  <tr valign="top"><td></td>
    <td>{$agriculturalConditions}</td></tr>
{/if}

{if $projectType == 17}
  <tr><td>[$project_info_result]:</td><td>
  {if $signResults.type=="change"}
      [$page_signs_change_sign] #{$signResults.number}</td></tr>
      <tr><td>[$project_info_current]:</td><td><p class="sign">[ {$signResults.name} ]</p></td><tr>
      <tr><td>[$project_info_new]:</td><td><p class="sign">[ {$signResults.newName} ]</p></td><tr>
  {elseif $signResults.type=="remove"}
      [$page_signs_remove_sign] #{$signResults.number}</td></tr>
      <tr><td>[$project_info_current]:</td><td><p class="sign">[ {$signResults.name} ]</p></td><tr>
  {elseif $signResults.type=="move"}
      [$page_signs_move_sign] #{$signResults.number} [$page_signs_to_position] {$signResults.position}</td></tr>
      <tr><td>[$project_info_current]:</td><td><p class="sign">[ {$signResults.name} ]</p></td><tr>
  {elseif $signResults.type=="add"}
      [$page_signs_add_new] [$page_signs_in_position] {$signResults.number}</td></tr>
      <tr><td>[$project_info_new]:</td><td><p class="sign">[ {$signResults.newName} ]</p></td><tr>
  {/if}
{/if}
    {if $problems}
    <tr>
      <td>[$project_info_problems]</td>
      <td>
        <ul class="inlinePlain">
            {foreach $problems as $problem}
              <li>
                <CANTR REPLACE NAME={$problem}>
              </li>
            {/foreach}
        </ul>
      </td>
    </tr>
    {/if}
    <tr>
      <td></td>
      <td>
        <table>
          <tr>
            {if $joinProject}
              <td>
                <form METHOD=POST ACTION="index.php?page=joinproject">
                  <input TYPE=hidden NAME=project VALUE={$projectId}>
                  <input TYPE=image SRC="[$_IMAGES]/button_small_join.gif" BORDER=0 title="[$page_build_participate]">
                </FORM>
              </td>
            {/if}
            <td>
              <form METHOD=POST ACTION="index.php?page=delproject">
                <input TYPE=hidden NAME=project VALUE={$projectId}>
                <input TYPE=image SRC="[$_IMAGES]/button_small_end.gif" BORDER=0 ALIGN=middle title="[$page_build_remove]">
              </form>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</center>