{if $inlocation}


  <!-- Available exits -->
  {if $doorid}
    {include file="template.title.[$lang].tpl" title="$locname: [$title_exits]"}
    <div class="page">
      <table>
        <tr>
          <td width="30">
            <form method="post" action="index.php?page=move">
              <input type="hidden" name="target" value="{$locregion}">
              <input type="image" src="[$_IMAGES]/button_small_exit.gif" border=0 title="[$alt_exit_room]">
            </form>
          </td>
          {if $loctype != 3}
            <td width="30">
              <form method="post" action="index.php?page=knock">
                <input type="hidden" name="building" value="{$locregion}">
                <input type="image" src="[$_IMAGES]/button_small_knock.gif" border=0 title="[$alt_knock_exit]">
              </form>
            </td>
          {/if}
          <td width="30" valign="top">
            <form method="post" action="index.php?page=pull_out">
              <input type="hidden" name="from" value="{$locregion}">
              <input type="hidden" name="return" value="char.description">
              <input type="image" src="[$_IMAGES]/button_small_pull.gif" title="[$alt_pull_out]">
            </form>
          </td>
          <td>
            [$page_desc_door]
            <CANTR LOCNAME ID={$doorid}> {if $iswindow}+ {if $iswindowopen}[$page_window_open]{else}[$page_window_closed]{/if}{/if}
          </td>
        </tr>
        {if $weatherData && $loctype == 2}
          <tr>
            <td colspan="3"></td>
            <td id="weatherData">
              {$weatherData}
            </td>
          </tr>
        {/if}
      </table>
    </div>
  {/if}

  {include file="template.title.[$lang].tpl" title="$locname: [$title_description]"}

  {if $loctype == 1}
    <div class="page" style="text-align:center;" id="navigationSubpanel">
      <a href="index.php?page=char.description" class="button_charmenuactive">[$page_location_subcategory_description]</a>
      <a href="index.php?page=animals&subpage=hunt" class="button_charmenu">[$page_location_subcategory_hunting]</a>
      <a href="index.php?page=animals&subpage=domestication" class="button_charmenu">[$page_location_subcategory_domestication]</a>
    </div>
  {/if}
  <div class="page">
    <table>
      {if $show_capacity}
        <tr>
          <td align="right">[$page_location_capacity_used]</td>
          <td width="250" align="center"><img src="image.progressbar.inc.php?width=200&height=20&proportion={$capacity}"></td>
          <td>{$capacity100}%</td>
        </tr>
      {/if}
      {if $show_crowding}
        <tr>
          <td align="right">[$page_location_people_capacity_used]</td>
          <td width="250" align="center"><img src="image.progressbar.inc.php?width=200&height=20&proportion={$crowding}"></td>
          <td align="left">{$crowding100}</td>
        </tr>
      {/if}

      {if $isfuel}
        <tr>
          <td align="right">[$page_fuel_level]:</td>
          <td width="250" align="center"><img src="image.progressbar.inc.php?width=200&height=20&proportion={$fuel}"></td>
          <td align="left">{$fuel100}% ({$fuelwt}g)</td>
        </tr>
      {/if}
      {if $is_custom_description_allowed}
        {if $custom_description}
          <tr>
            <td align="right" valign="top">[$page_location_building_description]:</td>
            <td colspan="2">
              {$custom_description}
            </td>
          </tr>
        {/if}
        <tr>
          <td colspan="3" align="right">
            {if $custom_description}
              <form method="post" action='index.php?page=reportdescription&desc_id={$custom_description_id}'>
                <input type="hidden" name="return" value='char.description'>
                <input type="submit" value="[!]" title="[$report_abuse]" class="button_charmenu">
              </form>
            {/if}
            <input type="button"
                   id="changeBuildingDescButton"
                   value="[$new_building_desc]" class="button_charmenu">
          </td>
        </tr>
        <div id="descChange" style="display:none;" class="page">
          <form action="index.php?page=changebuildingdesc" method="post">
            <input type="hidden" name="building_id" value="{$location}">
            <fieldset>
              <legend>[$new_building_desc]</legend>
              <textarea style="width:100%" rows="5" name="new_desc">{$custom_description_edit}</textarea>
              <input type="submit" value="[$new_building_desc_accept]" class="button_charmenu"/>
              <p style="margin:3px;font-size:7pt;">[$text_new_building_desc]</p>
            </fieldset>
          </form>
        </div>
      {/if}
    </table>
  </div>
  <div class="page">
    {if $loctype == 1}
      <p style="text-align:center;">
        [$digging_slot_summary]
      </p>
    {/if}
    {if $borderslake or $borderssea}
      <p style="text-align:center;">
        {if $borderslake}[$page_desc_borders_lake]{/if} {if $borderssea}[$page_desc_borders_sea]{/if}
      </p>
    {/if}
  </div>
  <div class="page locationDescription">
    {if $loctype == 1 || ( $loctype == 3 && $outdoor ) }
      <div class="locationDescriptionMap" {if $loctype == 3} align="center"{/if}>
        <img src="liteindex.php?page=map&character={$character}" id="mapImage">
      </div>
    {/if}
    {if $doraws}
      <div class="locationDescriptionRaws">
        <div class="locationDescriptionRawsInside">
          <ul class="plain rawsList">
            <li>
              [$form_raws]:
            </li>
            {foreach $raws as $raw}
              <li>
                <form method="post" action="index.php?page=dig">
                  <input type="hidden" name="type" value="{$raw->type}">
                  <input type="hidden" name="location" value="{$location}">
                  <label style="cursor: pointer">
                    <input type="image" src="[$_IMAGES]/button_small_{$raw->action}.gif"
                           align="absmiddle"
                           {if $raw->action == "dig"    }title="[$action_dig_1b] {$raw->name}">{/if}
                    {if $raw->action == "farm"   }title="[$action_dig_2b] {$raw->name}">{/if}
                    {if $raw->action == "collect"}title="[$action_dig_3b] {$raw->name}">{/if}
                    {if $raw->action == "pump"   }title="[$action_dig_4b] {$raw->name}">{/if}
                    {if $raw->action == "catch"  }title="[$action_dig_5b] {$raw->name}">{/if}

                    {$raw->name}
                  </label>
                </form>
              </li>
            {/foreach}
          </ul>
        </div>
      </div>
    {/if}
    <div class="locationDescriptionTerrain">
      {if $loctype == 1}
        <img src="[$_IMAGES]/terrain/{$areaname}_{$season}.jpg" title="{$areaname}" style="border:1px #003300 solid">
        <br>
      {/if}
      {if $loctype != 2}
        {$weatherData}
        {if $loctype == 1}<br>
          {$harvestEfficiency}
        {/if}
        <span id="weatherData"></span>
      {/if}
    </div>
  </div>
  {if $isboat && $loctype != 5 && $locregion > 0}
    <div class="centered" style="margin-top:30px;width:700px;">
      <form method="post" action="index.php?page=undock">
        <input type="submit" value="[$button_undock]" class="button_action">
      </form>
    </div>
  {/if}
  <div class="page">
    <table>
      {if $loctype == 5}
        <!-- Sailing ship information -->
        <tr>
          <td colspan="2" style="text-align:center;padding:10px;">
            <img src="liteindex.php?page=map&character={$character}" id="mapImage">
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <span class="sailingInfo">
            {if $docking}
              [$page_desc_docking]
              {if $canCancelDocking}
                <a class="button_charmenu" href="index.php?page=canceldocking&sailing_id={$sailingid}">
                  [$button_cancel_docking]
                </a>
              {/if}
            {elseif $SPEED}
              [$page_desc_sailing]
            {else}
              [$page_desc_floating]
            {/if}
              </span>
              {if !$docking}
                <button class="adjustSailingButton button_charmenu">[$set_ship_course_button]</button>
                <form>
                  <div style="display:none" class="adjustSailingBox">
                    <p class="currentSailingMessage"></p>
                    <label for="sailingDirection">[$form_new_direction]:</label>
                    <input id="sailingDirection" size="3" type="text"/> [$degrees]<br>
                    <label for="sailingSpeed">[$form_new_speed]:</label>
                    <input id="sailingSpeed" size="3" type="text"/> [$percent]<br>
                    <label for="sailingHours">[$sailing_stop_after]:</label>
                    <input id="sailingHours" size="3" type="text"/> [$sailing_turns]<br/>
                    <div class="centered">
                      <button class="confirmSailing button_charmenu">[$form_confirm]</button>
                    </div>
                  </div>
                </form>
              {/if}
          </td>
        </tr>
      {/if}
    </table>
  </div>
  <div class="page">
    {if $isRepairable}
      <div class="centered">
        <a href="index.php?page=repair_location" style="display: flex;justify-content: center;align-items: center"><img src="[$_IMAGES]/button_small_repair.gif"/>[$page_location_repair]</a>
      </div>
    {/if}
  </div>
  {if $visibleFromDistance|count > 0}
    {include file="template.title.[$lang].tpl" title="[$title_visible_entities]"}
    <div class="page">
      <ul class="inlinePlain">
        {foreach $visibleFromDistance as $markerData}
          <li style="padding-top: 5px">
            <div style="display: inline-block; width: 60px">
              {if $markerData.canBeDockedTo}
                <form method="post" action="index.php?page=dock">
                  <input type="hidden" name="dock" value="{$markerData.id}">
                  <input type="submit" value="[$button_dock]" class="button_action">
                </form>
              {/if}
            </div>
            {$markerData.text}
              {if in_array($markerData.id, $dockingShips)}
                  [$ship_is_docking]
              {/if}
            {$markerData.objectsOnDeck}
            {$markerData.additionalSigns}
          </li>
        {/foreach}
      </ul>
    </div>
  {/if}

{else}
  <!-- Description for travellers -->
  {include file="template.title.[$lang].tpl" title="[$title_description]"}
  <div class="page" id="weatherData">
    {$weatherData}
  </div>
  <div class="centered" style="width:700px">
    <img src="liteindex.php?page=map&character={$character}">
  </div>
  {if $visibleFromDistance|count > 0}
    {include file="template.title.[$lang].tpl" title="[$title_visible_entities]"}
    <div class="page">
      <ul class="inlinePlain">
        {foreach $visibleFromDistance as $markerData}
          <li style="padding-top: 5px">
            <div style="display: inline-block; width: 60px">
              {if $markerData.canBeDockedTo}
                <form method="post" action="index.php?page=dock">
                  <input type="hidden" name="dock" value="{$markerData.id}">
                  <input type="submit" value="[$button_dock]" class="button_action">
                </form>
              {/if}
            </div>
            {$markerData.text}
            {$markerData.objectsOnDeck}
            {$markerData.additionalSigns}
          </li>
        {/foreach}
      </ul>
    </div>
  {/if}
{/if}

{if $roads}
  <!-- Available exits -->
  {include file="template.title.[$lang].tpl" title="$locname: [$title_exits]"}
  <div class="page">
    <table id="exitRoutes">
      {foreach from=$roads item=road}
        <tr>
          <td width="30">
            <form method="post" action="index.php?page=pointat">
              <input type="hidden" name="to_road" value="{$road->id}">
              <input type="image" src="[$_IMAGES]/button_small_pointat.png" title="[$alt_pointat_road]">
            </form>
          </td>
          <td width="30">
            <form method="post" action="index.php?page=travel">
              <input type="hidden" name="connection" value="{$road->id}">
              <input type="image" src="[$_IMAGES]/button_small_follow.gif" title="[$alt_follow_exit]">
            </form>
          </td>
          <td width="30">
            {if $road->canImprove}
              <form method="post" action="index.php?page=improve">
                <input type="hidden" name="location" value="{$location}">
                <input type="hidden" name="connection" value="{$road->id}">
                <input type="image" src="[$_IMAGES]/button_small_improve.gif" title="[$alt_improve_exit]">
              </form>
            {/if}
          </td>
          <td {if !$road->accepted}style="color:#888888;"{/if}>
            {$road->type} [$to]
            <CANTR LOCNAME ID={$road->endloc}>
              ([$form_direction]: {$road->direction})
          </td>
        </tr>
      {/foreach}
    </table>
  </div>
{/if}
<script type="text/javascript" src="[$JS_VERSION]/js/page.description.js"></script>
