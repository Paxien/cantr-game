{include file="template.title.[$lang].tpl" title="[$title_events]"}

<style type="text/css">
  {foreach $eventExistingGroups as $v}
  div.eventsgroup_{$v} {
    display: block;
  }

  {/foreach}
  div.eventsgroup_nogruped {
    display: block;
  }
</style>
<script type="text/javascript" src="[$JS_VERSION]/js/libs/socket.io.1.7.2.min.js"></script>
<script type="text/javascript" src="[$JS_VERSION]/js/events.js"></script>
<script type="text/javascript" src="[$JS_VERSION]/js/events_extended_box.js"></script>
<script type="text/javascript">

  var charId = "{$charIdMd5}";
  var filters = [
    {foreach from=$filters key=k item=v}
    [{$v->data}],
    {/foreach}
    null
  ];

  var initialWhisperingBookmarks = {$whisperingBookmarks};
</script>


{if $too_drunk == 1}
  <div class="page">
    [$disclaimer_too_drunk]
  </div>
{/if}

<div class="page" id="whisperingBookmarks" title="[$tooltip_events_whisper_bookmarks]">
  [$page_events_speak_to]
</div>
<div class="page messageFormContainer">
  <form name="messageform" method="post" action="index.php?page=talk">
    <div class="eventsMessageColumn" id="messageFieldNode">
      <textarea name="message" id="messageField" rows="3"></textarea>
      <input type="hidden" name="data" value="yes">
      <input type="hidden" name="to" value="0" id="talk_to">
    </div>
    <div class="eventsSubmitMessageColumn" id="submitTalkNode">
      <input type="submit" id="submitTalk" VALUE="[$button_talk_to_all]" class="button_charmenu">
    </div>
  </form>
</div>

{if $filters|@count > 1 }
  <div class="page" style="text-align:right">
    {foreach from=$filters key=k item=v}
      <button type="button" id="filter_{$k}" style="width:100px; margin-left:4px; margin-top:2px"
              class="button_charmenu{if $k == $filters|@count - 1 }active{/if}" onMouseDown="return selectFilter({$k});">
        {$v->name}</button>
    {/foreach}
  </div>
{/if}

<div class="page">
  <div id="eventsList" class="eventsList">
    {foreach $events as $event}
      {$event}
    {/foreach}
  </div>
  <div class="eventsListButtons">
    <form method=post action="index.php?page=char.events">
      <input type="hidden" name="none" value="yes">
      <input type="submit" value="[$button_hide_all]" class="button_charmenu">
    </form>
    <form method=post action="index.php?page=char.events">
      <input type="hidden" name="all" value="yes">
      <input type="submit" value="[$button_show_all]" class="button_charmenu">
    </form>
  </div>
</div>

<script type="text/javascript">
  {if $filter|@count > 1 && $selectedfilter != ''}
  selectFilter({$selectedfilter});
  {/if}
  startEventsListener({$character}, {$maxid}, {$l});
</script>
