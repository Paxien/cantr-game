{include file="template.title.[$lang].tpl" title="<CANTR REPLACE NAME=title_player_info>"}

<script type="text/javascript" src="[$JS_VERSION]/js/info.infoplayer.js"></script>

<center>
  <div id="player-account">
    <table width="1000">
      <tbody>

      <tr>
        <td width="175">Player ID:</td>
        <td colspan="3">{$aPageData.player->id}</td>
      </tr>

      <tr>
        <td> Name:</td>
        <td>
          <strong>{$aPageData.player->firstname} {$aPageData.player->lastname}</strong>
          {if $aPageData.status.is_active}
            <form method="post" action="index.php?page=sendpm">
              <input type="hidden" name="player_id" value="{$aPageData.player->id}"/>
              <input type="submit" value="Send PM"/>
            </form>
          {/if}
        </td>
        <td>
          Status:
          {if $aPageData.status.description == "Locked"}<span color="yellow">{/if}
    <b>{$aPageData.status.description}</b>
    {if $aPageData.status.description == "Locked"}</span>{/if}
        </td>
        <td align="right">
          {if $aPageData.status.action != ""}
            <form method="post" action="index.php?page=infoplayer">
              {if $aPageData.status.action == "Lock" OR $aPageData.status.action == "Unlock"}
                <input type="hidden" name="switch_locking_status" value="yes"/>
              {elseif $aPageData.status.action == "Revive"}
                <input type="hidden" name="revive" value="yes">
              {/if}
              <input type="hidden" name="player_id" value="{$aPageData.player->id}"/>
              <input type="submit" value="{$aPageData.status.action}"/>
            </form>
          {/if}
        </td>
      </tr>

      <tr>
      <td width="150">Username:</td>
      <td>{$aPageData.player->username}</td>
      <td width="145" align="left" rowspan="2">
          Use of custom descriptions:
          {if $aPageData.status.status != "Disallowed"}<span color="yellow">{/if}
      <b>{$aPageData.custom_descriptions.status}</b>
      {if $aPageData.status.description == "Locked"}</span>{/if}
      </tr>

      <tr>
        <td width="150">Forum Nick:</td>
        <td>{$aPageData.player->forumnick}</td>
        </td>
        <td rowspan="2">
          <form method="post" action="index.php?page=infoplayer">
            <input type="hidden" name="switch_limitations" value="yes"/>
            <input type="hidden" name="player_id" value="{$aPageData.player->id}"/>
            <input type="submit" value="{$aPageData.custom_descriptions.button}"/>
          </form>
        </td>
      </tr>
      <tr>
        <td width="150">IRC Nick:</td>
        <td>{$aPageData.player->nick}</td>
      </tr>

      <tr>
        <td>Email:</td>
        <td>
          {if $aPageData.status.is_active}
            <a href="mailto:{$aPageData.player->email}">{$aPageData.player->email}</a>
          {else}
            {$aPageData.player->email}
            <b>Do not contact - not registered anymore!</b>
          {/if}
        </td>
        {if $aPageData.admin->allowed_to_alter_email}
          <td>
            <form method=post action="index.php?page=infoplayer">
              <input type="hidden" name="change_email" value="yes"/>
              <input type="hidden" name="player_id" value="{$aPageData.player->id}"/>
              <input type="text" name="new_email" value="{$aPageData.player->email}"/>
          </td>
          <td>
            <input type="submit" value="change"/>
            </form>
          </td>
        {else}
          <td colspan="2">&nbsp;</td>
        {/if}
      </tr>

      <tr>
        <td>Credits:</td>
        <td>
          {$aPageData.player->credits}
          <form method="post" action="index.php?page=infoplayer">
            <input type="text" size="10" name="alter_credits"/>
            <input type="hidden" name="player_id" value="{$aPageData.player->id}"/>
            <input type="submit" value="add"/>
          </form>
        </td>
        <td>Creating new chars:</td>
        <td>
          <a href="index.php?page=lock_newchars&player_id={$player_id}">
            [{($aPageData.newCharsLocked) ? "unlock" : "lock"}]</a>
        </td>
      </tr>

      <td>Using radio:</td>
      <td>
        <a href="index.php?page=infoplayer&player_id={$player_id}&lock_radio={($aPageData.radioLocked) ? "no" : "yes"}">
          {($aPageData.radioLocked) ? "[unlock]" : "[lock]"}
        </a>
      </td>

      <tr>
        <td>Chars on INTRO server:</td>
        {if $aPageData.introServerAvailable}
        <td>
          {foreach $aPageData.mentors as $mentor}
            {$mentor->name|escape} [{$mentor->language}]
            <br>
            {foreachelse}
            * no mentor chars *
            <br>
          {/foreach}
        </td>
      <tr>
        <td>Add mentor char on INTRO:</td>
        <td>
          <form method="post" action="index.php?page=infoplayer">
            <select name="new_tutor_language">
              {foreach $langcode as $num => $name}
                <option value="{$num}">{$name}</option>
              {/foreach}
            </select>
            <select name="new_tutor_sex">
              <option value="1">male</option>
              <option value="2">female</option>
            </select>
            <input type="hidden" name="player_id" value="{$player_id}">
            <input type="submit" value="create">
          </form>
        </td>
        {else}
        <td>unavailable</td>
        {/if}
      </tr>
      <tr>
        <td colspan="4">Notes:</td>
      </tr>
      <tr>
        <form method="post" action="index.php?page=infoplayer">
          <td colspan="3">
            <input type="hidden" name="change_notes" value="yes"/>
            <input type="hidden" name="player_id" value="{$aPageData.player->id}"/>
            <input type="text" size="100" name="player_notes" value="{$aPageData.player->notes}"/>
          </td>
          <td>
            <input type="submit" value="Update"/>
          </td>
        </form>
      </tr>

      {if $aPageData.admin->allowed_to_change_privs}
        <tr>
          <td valign="top">Assignments:</td>
          <td colspan="3">
            {foreach $aPageData.assignments as $assignment}
              {$assignment->statusText} of {$assignment->council}
              <a href="index.php?page=manage_privs&action=remove&pid={$aPageData.player->id}&cid={$assignment->councilid}">[remove]</a>
              <br/>
            {/foreach}
            <a href="index.php?page=manage_privs&action=add&pid={$aPageData.player->id}">[add]</a>
          </td>
        </tr>
        <tr>
          <td valign="top">Terms of Use:</td>
          <td colspan="3">
            {$aPageData.player->terms_of_use}
            <form method="post" action="index.php?page=infoplayer&player_id={$aPageData.player->id}">
              <input type="hidden" name="tou_change" value="1"/>
              <select name="tou">
                {foreach $aPageData.tou_versions as $tou}
                  <option value="{$tou}">{$tou}</option>
                {/foreach}
              </select>
              <input type="submit" value="Change"/>
            </form>
          </td>
        </tr>
        <tr>
          <td valign="top">Privileges:</td>
          <td colspan="3">
            {foreach $aPageData.access as $access}
              {$access} <br/>
            {/foreach}
            {foreach $aPageData.ceAccess as $ceAccess}
              [CE] {$ceAccess} <br/>
            {/foreach}
            <a href="index.php?page=manage_privs&action=editpr&pid={$aPageData.player->id}">[edit]</a>
          </td>
        </tr>
      {/if}

      <tr>
        <td>Country:</td>
        <td>{$aPageData.player->country}</td>
        <td>Language:</td>
        <td>{$aPageData.player->languageName}</td>
      </tr>

      <tr>
        <td>Year of birth:</td>
        <td>{$aPageData.player->age}</td>
        <td>Date of registration:</td>
        <td>{$aPageData.player->register}</td>
      </tr>

      {if $aPageData.player->refplayer != 0}
        <tr>
          <td>Referring player:</td>
          <td colspan="3"><a
                href="index.php?page=infoplayer&player_id={$aPageData.player->refplayer}">{$aPageData.player->refplayer}</a>
          </td>
        </tr>
      {/if}

      <tr>
        <td>Player(s) referred:</td>
        <td colspan="3">
          {section name=referrer loop=$aPageData.referred_players}
            <a href="index.php?page=infoplayer&player_id={$aPageData.referred_players[referrer]}">{$aPageData.referred_players[referrer]}</a>
            &nbsp;
          {/section}
        </td>
      </tr>

      <tr>
        <td>Referrer URL:</td>
        <td colspan="3">{$aPageData.player->referrer}</td>
      </tr>

      <tr>
        <td valign="top">Other accounts:</td>
        <td colspan="3">
          {foreach $aPageData.same_player_info as $same_player_info}
            <a href="index.php?page=infoplayer&player_id={$same_player_info->p2}">{$same_player_info->p2}</a>
            (identified {$same_player_info->date} by
            <a href="index.php?page=infoplayer&player_id={$same_player_info->admin_id}">
              {$same_player_info->admin_fn} {$same_player_info->admin_ln}</a>
            )
            <form method="post" action="index.php?page=infoplayer&player_id={$aPageData.player->id}">
              <input type="hidden" name="remove_other_account" value="{$same_player_info->p2}"/>
              <input type="submit" value="Remove"/>
            </form>
            <br/>
          {/foreach}
          <form method="post" action="index.php?page=infoplayer&player_id={$aPageData.player->id}">
            <input type="text" size="10" name="other_account"/>
            <input type="submit" value="Add">
          </form>
        </td>
      </tr>

      <tr>
        <td>Last login:</td>
        <td colspan="3">{$aPageData.player->lastdate}-{$aPageData.player->lasttime}</td>
      </tr>

      <tr>
        <td>Last login info:</td>
        <td colspan="3">{$aPageData.player->lastlogin} <a
              href="index.php?page=infoplayer&player_id={$aPageData.player->id}&listips=yes">[IP
            history]</a></td>
      </tr>

      <tr>
        <td>Recent activity:</td>
        <td colspan="3">{$aPageData.player->recent_activity_display}</td>
      </tr>

      {if $aPageData.ip_info != null}
        <tr>
          <td valign="top">IP History:</td>
          <td colspan="3">
            <table style="width:100%">
              <tbody>
              <tr>
                <th>IP</th>
                <th>Whois</th>
                <th>Client-IP</th>
                <th>Whois</th>
                <th>Times</th>
                <th>Last time</th>
              </tr>
              {foreach $aPageData.ip_info as $ip_info}
                <tr class="{cycle values="odd,even"}-row">
                  <td>{$ip_info->ip}</td>
                  <td>{$ip_info->remhost_ip}</td>
                  <td>{$ip_info->client_ip}</td>
                  <td>{$ip_info->remhost_client_ip}</td>
                  <td>{$ip_info->times}</td>
                  <td>{$ip_info->lasttime}</td>
                </tr>
              {/foreach}
              </tbody>
            </table>
          </td>
        </tr>
      {/if} {* $aPageData.ip_info != null *}

      <tr>
        <td valign="top">IP watch to email:</td>
        <td colspan="3">
          {section name=email loop=$aPageData.ip_watch}
            {$aPageData.ip_watch[email]}
            <a href="index.php?page=infoplayer&player_id={$aPageData.player->id}&removewatch={$aPageData.ip_watch[email]}">[remove]</a>
            <br/>
          {/section}
          <form method="post" action="index.php?page=infoplayer&player_id={$aPageData.player->id}">
            <input type="text" size="10" name="setwatch"/>
            <input type="submit" value="Add">
          </form>
        </td>
      </tr>
      </tbody>
    </table>
      <table width="1000">
      <tr>
        <td colspan="4">Characters:</td>
      </tr>

      <tr>
        <th width="280" colspan="2">Name</th>
        <th width="300">Location</th>
        <th width="100" align="right">Lck</th>
        <th width="30">Ln</th>
        <th style="min-width:80px">Last access</th>
        <th style="min-width:100px">View</th>
      </tr>
      {foreach $aPageData.characters as $char}
        <TR VALIGN=top>
          <TD>
            {if $char->isDead}
              <FONT COLOR="#777777">{$char->name} ({$char->id})</FONT>
            {else}
              {$char->name} ({$char->id})
            {/if}
          </TD>

          <TD width=30 align=right>
            {if $char->isAlive}
              <A HREF="index.php?page=kill&chartokill={$char->id}&player_id={$player_id}"
                 onclick="return confirm('Are you sure you want to KILL that character?');">[kill]</A>
              {if $char->slotBlocked > 0}
                <FONT COLOR="#FF0000">b{$char->slotBlocked}</FONT>
              {/if}
            {/if}
          </TD>
          <td>{$char->locFullName}</td>
          <td align="right">
            {if !$char->lockDays}
              <A HREF="" onclick="lock_prompt({$char->id}); return false; ">[lock]</A>
            {else}
              {$char->lockDays}d <a
                href="index.php?page=lock_char&action=unlock&charid={$char->id}">[unlock]</a>
            {/if}
          </td>
          <TD>{$char->langShortName}</TD>
          <td width=80> {$char->lastDate}-{$char->lastTime}</td>
          <td>
            {if $aPageData.canControlCharacters}
              <A HREF="index.php?page=char&character={$char->id}">[v]</A>
            {/if}
            <A HREF="index.php?page=infoplayer&player_id={$player_id}&listknownas={$char->id}">[ka]</A>
            <A HREF="index.php?page=infoplayer&player_id={$player_id}&listknows={$char->id}">[kn]</A>
            <a href="index.php?page=infoplayer&player_id={$player_id}&alterdesc={$char->id}">[d]</a>
          </TD>
        </TR>
        {if $char->knownAs}
          <TR>
            <TD COLSPAN=5>
              <BR>This character is known as:<BR>
              {foreach $char->knownAs as $charnamingInfo}
                &nbsp;&nbsp;&nbsp;
                <i>{$charnamingInfo->name}</i>
                (by {$charnamingInfo->cname} of
                <A
                    HREF="index.php?page=infoplayer&player_id={$charnamingInfo->pid}">{$charnamingInfo->pfname}
                  {$charnamingInfo->plname}</A>
                )
                <BR>
              {/foreach}
              <BR></TD>
          </TR>
        {/if}

        {if $char->knows}
          <TR>
            <TD COLSPAN=5>
              <BR>This character knows:<BR>
              {foreach $char->knows as $charKnown}
                &nbsp;&nbsp;&nbsp;
                <i>{$charKnown->name}</i>
                (which is {$charKnown->cname} of
                <A HREF="index.php?page=infoplayer&player_id={$charKnown->pid}">{$charKnown->pfname} {$charKnown->plname}</A>
                )
                <BR>
              {/foreach}
              <BR></TD>
          </TR>
        {/if}
        {if $char->id == $aPageData.characterToAlterDesc}
          <tr>
            <td colspan=6>
              <p>Current public description:</p>
              <form method=post action="index.php?page=publicdesc">
                <input type=hidden name=id value="{$char->id}">
                <input type=hidden name=type value=1>
                <input type=hidden name=next
                     value=infoplayer&player_id={$aPageData.player->id}&alterdesc={$char->id} >
                <textarea name='publicDesc' rows='4' style='width: 100%'>{$char->customDesc}</textarea>
            </td>
          </tr>
          <tr>
            <td colspan=6 align="right">
              <input type=submit class="button_charmenu" value="[$button_char_desc_change]">
              </form></td>
          </tr>
        {/if}

        {if $char->travels}
          <TR>
            <TD colSpan=6 style="padding-left: 30px">
              {assign var="lastloc" value=-1}
              {foreach $char->travels as $travel}
                {if $travel->location != $lastloc}
                  {assign var=lastloc value=$travel->location}
                  {$travel->locationname}
                {/if}
                <FONT COLOR="#999999">
                  {if $travel->arrival} arrived {else} departed {/if}
                  at {$travel->day}-{$travel->hour}
                  {if $travel->vehiclename != "travelling"}
                    <small> in {$travel->vehiclename}</small>{/if}
                  , </FONT>
              {/foreach}
            <TD>
          </TR>
        {/if}
      {/foreach}
    </table>

    <table style="width:1000px">
      <TR>
        <TD align=right colSpan=9>
          <A HREF="index.php?page=infoplayer&player_id={$player_id}{($aPageData.travels) ? "" : "&travels=1"}">
            [{($aPageData.travels) ? "Hide" : "Show"} travel history]</A>
          <A HREF="index.php?page=doubleaccts&ids={$player_id}">[Cooperation report]</A></TD>
      </TR>
      <TR>
        <TD align=left colSpan=9>
          <FORM method="post" action="index.php?page=infoplayer&player_id={$player_id}">
            Transfer character
            <select name="transferchar">
              <option value="0" selected>--- none selected ---</option>
              {foreach $aPageData.characters as $char}
                <option value="{$char->id}">{$char->id} {$char->name}</option>
              {/foreach}
            </select> to account no.: <INPUT size="7" name="transferdest">
            <input type="submit" value="Transfer now"></FORM>
        </TD>
      </TR>
    </TABLE>
  </div>
</center>

<div class="centered">
  <A HREF="index.php?page=pendingplayers">Manage database of pending player</A><BR>
  <A HREF="index.php?page=listplayers">Go back to player search</A>
</div>

<div style="position: fixed; bottom: 10px; left: 10px;">
  <a href="index.php?page=player"><img src="{$aPageData.imagepath}/button_back2.gif" title="Back"></a>
</div>
