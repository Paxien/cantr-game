
{function name=buttonWithIcon iconClass=null caption=null link=null newTab=false}
  <div class="navbarItem">
    <a class="navbarItemLink" href="{$link}" {if $newTab}target="_blank"{/if}>
      <i class="navbarItemIcon {$iconClass}"></i><br>
      <span class="navbarItemText">{$caption}</span>
    </a>
  </div>
{/function}

<div class="page">
  <div id="navBar"></div>
</div>

{if isset($unsubDate)}
  <div class="page">
    <div class="errorMessage">
      <CANTR REPLACE NAME=unsub_counter_message DAY={$unsubDate->day} HOUR={$unsubDate->hour} MINUTE={$unsubDate->minute}>
        <a href="index.php?page=cancel_unsub_counter">[$remove_unsub_counter]</a>.
    </div>
  </div>
{/if}

{if $survs}{$survs}{/if}

{if $messages || $imessages }
  {include file="template.title.[$lang].tpl" title="[$title_messages]"}
{/if}


{if $messages}
  <!-- public messages -->
  <div class="page">
    {foreach $messages as $message}
      <div style="margin:30px 0">
        <div>
          <hr>
          {$message->author} ({$message->date})
        </div>
        <div class="messageContent">{$message->content}</div>
        <div style="text-align:right;margin-top:10px">
          <form method="post" action="index.php?page=seen_message">
            <input type="hidden" name="message" value="{$message->id}"/>
            <input type="submit" value="[$button_remove_message]" class="button_charmenu"/>
          </form>
        </div>
      </div>
    {/foreach}
  </div>
{/if}


{if $imessages}
  <!-- Individual messages -->
  <div class="page">
    {foreach $imessages as $imessage}
      <div style="margin:30px 0">
        <hr>
        {$imessage->content}

        <div style="text-align:right;margin-top:10px">
          {if $imessage->reply}
            <form method="post" action="index.php?page=sendpm">
              <input type="hidden" name="player_id" value="{$imessage->from}"/>
              <input type="submit" value="[$button_reply]" class="button_charmenu"/>
            </form>
          {/if}
          <form method="post" action="index.php?page=remove_message">
            <input type="hidden" name="message_id" value="{$imessage->id}"/>
            <input type="hidden" name="player" value="{$playerID}"/>
            <input type="submit" value="[$button_remove_message]" class="button_charmenu"/>
          </form>
        </div>
      </div>
    {/foreach}
  </div>
{/if}

{if !$isEmailValid}
  {include file="template.title.[$lang].tpl" title="[$title_email_address_problem]"}
  <div class="page">
    <CANTR REPLACE NAME=info_email_address_problem SEND_CONFIRMATION={"index.php?page=send_email_confirmation"|urlencode}>
  </div>
{/if}

{include file="template.title.[$lang].tpl" title="[$title_characters_list]"}

<div class="page">
  {$charactersList}
  {$introCharactersList}
  {if $canCreateCharacters}
  <table>
    <tr valign="center">
      <td style="height:30px;vertical-align:middle;">
        <div id="newCharacter"></div>
      </td>
    </tr>
  </table>
  {/if}
</div>

{if isset($curiosity)}
{include file="template.title.[$lang].tpl" title="[$title_curiosity_did_you_know]"}
<div class="page">
  <p class="centered">
    <CANTR REPLACE NAME={$curiosity}>
  </p>
{/if}

{if $votingLinks}
  {include file="template.title.[$lang].tpl" title="[$title_vote]"}
  <div  class="page">
  <table>
    <tr>
      <td style="vertical-align:top;text-align:center;width:50%;">
        {math equation="ceil(x/2)" x=$votingLinks|@count assign="half"}
        {section name=linkNo loop=$votingLinks}
        {if $smarty.section.linkNo.iteration-1 == $half}
      </td>
      <td style="vertical-align:top;text-align:center;width:50%;">
        {/if}
        {$votingLinks[linkNo]}<br/>
        {/section}
      </td>
    </tr>
  </table>
  </div>
{/if}

{include file="template.title.[$lang].tpl" title="[$title_support_cantr]"}

<div class="page">
  [$page_player_3]
  <div class="centered">
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
      <input type="hidden" name="cmd" value="_s-xclick"/>
      <input type="hidden" name="hosted_button_id" value="FBP3HZHZ4W6DC"/>
      <input type="hidden" name="business" value="5V4EANUWU6XFG"/>
      <input type="hidden" name="item_name" value="[$paypal_desc]"/>
      <input type="hidden" name="no_note" value="1"/>
      <input type="hidden" name="currency_code" value="USD"/>
      <input type="hidden" name="tax" value="0"/>
      <input type="hidden" name="lc" value="{$lc}"/>
      <input type="image" src="graphics/cantr/pictures/paypal_don_button.png"
             name="submit" title="[$paypal_button_title]"/>
    </form>
  </div>
</div>

{include file="template.title.[$lang].tpl" title="[$title_discuss_cantr]"}

<div class="page">
  [$page_player_1]<br><br>
  [$page_player_5]<br><br>
  <div class="centered">
    <a href="https://forms.gle/FQRqW9o74YFWYtaT6" target="_blank">[$text_recruitment]</a>
  </div>
</div>

<div class="page">
  <div class="centered">
    {foreach $turnreps as $report}
      <form method="post" action="index.php?page=requestreport">
        <input type="hidden" name="turnnumber" value="{$report}"/>
        <input type="submit" value="[$button_turnreport] {$report}" class="button_charmenu"/>
      </form>
    {/foreach}
  </div>
</div>

{include file="template.title.[$lang].tpl" title="[$title_game_admin_menu]"}

<div class="page accessListContainer"><!--
  --><div class="accessListColumn">
    <span class="subcategory">[$subcategory_general]:</span><br/>
    <a href="index.php?page=departments">[$link_departments]</a><br/>
    <a href="index.php?page=publicstatistics">[$link_statistics_public]</a><br/>
    <a href="index.php?page=financessummary">[$link_finances_summary]</a><br/>

        {if $accesslist[1] || $accesslist[13] || $accesslist[23] || $accesslist[19] || $accesslist[21] || $accesslist[31] || $accesslist[49]}
          <span class="subcategory">[$subcategory_administrative]:</span>
          <br/>
          {if $accesslist[1]}
            <a href="index.php?page=lockgame" onclick="return confirm('You are sure?');">[$link_gamelock]</a>
            <br/>
          {/if}
          {if $accesslist[13]}
            <a href="index.php?page=messageallplayers">[$link_message]</a>
            <br/>
          {/if}
          {if $accesslist[23]}
            <a href="index.php?page=mailall">[$link_mailall]</a>
            <br/>
          {/if}
          {if $accesslist[19]}
            <a href="index.php?page=statistics">[$link_statistics]</a>
            <br/>
          {/if}
          {if $accesslist[21]}
            <a href="index.php?page=adminmap">[$link_admin_map]</a>
            <br/>
          {/if}
          {if $accesslist[31]}
            <a href="index.php?page=surveylist">[$link_manage_surveys]</a>
            <br/>
          {/if}
          {if $accesslist[49]}
            <a href="index.php?page=urldecoder">[$link_decode_url]</a>
            <br/>
          {/if}
          {if $accesslist[50]}
            <a href="index.php?page=manual_events">[$link_manual_events]</a>
            <br/>
          {/if}
        {/if}

        {if $accesslist[3] || $accesslist[33] || $accesslist[17] || $accesslist[34] || $accesslist[22]}
          <span class="subcategory">[$subcategory_players_department]:</span>
          <br/>
          {if $accesslist[3]}
            <a href="index.php?page=listplayers">[$link_players_database]</a>
            <br/>
            <a href="index.php?page=radioreport" onclick="return confirm('You are sure?');">[$link_radioreport]</a>
            <br/>
          {/if}
          {if $accesslist[33]}
            <a href="index.php?page=ce">Cantr Explorer - experimental, under construction</a>
            <br/>
          {/if}
          {if $accesslist[17]}
            <a href="index.php?page=pendingplayers">[$link_pendingplayers] ({$newplayerscount} [$pending])</a>
            <br/>
          {/if}
          {if $accesslist[34]}
            <a href="index.php?page=researchplayers">[$link_research_players]</a>
            <br/>
          {/if}
          {if $accesslist[22]}
            <a href="index.php?page=notes_log">[$link_notes_log]</a>
            <br/>
            <a href="index.php?page=pdimagereview">[$link_review_images]</a>
            <br/>
          {/if}
          {if $accesslist[51]}
            <a href="index.php?page=travels_timeline">[$link_travels_timeline]</a>
            <br/>
          {/if}
          {if $accesslist[52]}
            <a href="index.php?page=indirectobjecttransfers">Indirect object transfers</a>
          {/if}
        {/if}
  </div><div class="accessListColumn">
        {if $accesslist[29] || $accesslist[8] || $accesslist[11] || $accesslist[10] || $accesslist[5] || $accesslist[25] || $accesslist[7] || $accesslist[11] || $accesslist[16] || $accesslist[47]}
          <span class="subcategory">[$subcategory_resources_department]:</span>
          <br/>
          {if $accesslist[29]}
            <a href="index.php?page=manageclothescategories">[$link_clothes_categories]</a>
            <br/>
          {/if}
          {if $accesslist[8]}
            <a href="index.php?page=managemachines">[$link_machine_types]</a>
            <br/>
          {/if}
          {if $accesslist[11]}
            <a href="index.php?page=manageobjects">[$link_object_types]</a>
            <br/>
          {/if}
          {if $accesslist[10]}
            <a href="index.php?page=managerawtypes">[$link_database_raw_types]</a>
            <br/>
          {/if}
          {if $accesslist[5]}
            <a href="index.php?page=listraws">[$link_database_raw_positions]</a>
            <br/>
          {/if}
          {if $accesslist[25]}
            <a href="index.php?page=manage_animals">[$link_animal_types]</a>
            <br/>
          {/if}
          {if $accesslist[7]}
            <a href="index.php?page=listanimals">[$link_animal_placement]</a>
            <br/>
          {/if}
          {if $accesslist[11]}
            <a href="index.php?page=managevehicles">[$link_vehicle_connection_types]</a>
            <br/>
          {/if}
          {if $accesslist[16]}
            <a href="index.php?page=listlocations">[$link_locations]</a>
            <br/>
          {/if}
          {if $accesslist[47]}
            <a href="index.php?page=manage_events">Manage event-types groups</a>
            <br/>
          {/if}
        {/if}

        {if ($accesslist[43] && $_ENV == 'test') || ($accesslist[44] && $_ENV == 'www') || $accesslist[32] || $accesslist[36]}
          <span class="subcategory">[$subcategory_programming_department]:</span>
          <br/>
          {if $accesslist[43] and $_ENV == 'test'}
            <a href="index.php?page=utiltester">Run util.tester (test environment)</a>
            <br/>
          {/if}
          {if $accesslist[44] and $_ENV == 'www'}
            <a href="index.php?page=utiltester">Run util.tester (live environment)</a>
            <br/>
          {/if}
        {/if}

        {if $accesslist[26] }
          <span class="subcategory">[$subcategory_languages_department]:</span>
          <br/>
          {if $accesslist[26]}
            <a href="index.php?page=managetranslations&action=select">[$link_translations]</a>
            <br/>
            <a href="index.php?page=tplman">[$link_manage_templates]</a>
            <br/>
          {/if}
        {/if}

        {if $accesslist[24] || ($accesslist[45] && $_ENV == 'test') || ($accesslist[46] && $_ENV == 'www')}
          <span class="subcategory">[$subcategory_public_relations]:</span>
          <br/>
          {if $accesslist[24]}
            <a href="index.php?page=manageadvert">[$link_manage_advert]</a>
            <br/>
          {/if}
          {if $accesslist[45] and $_ENV == 'test'}
            <a href="index.php?page=votinglinks">Manage voting links (test)</a>
            <br/>
          {/if}
          {if $accesslist[46] and $_ENV == 'www'}
            <a href="index.php?page=votinglinks">Manage voting links (live)</a>
            <br/>
          {/if}
        {/if}
  </div>
</div>

<script type="text/javascript" src="[$JS_VERSION]/js/libs/socket.io.1.7.2.min.js"></script>
<script type="text/javascript" src="[$JS_VERSION]/js/page.player.js"></script>
