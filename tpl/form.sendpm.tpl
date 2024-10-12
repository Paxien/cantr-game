{include file="template.title.[$lang].tpl" title="[$title_send_pm]"}

<div class="page">
  <form method="post" action="index.php?page=sendpm" class="centered">
    <input type="hidden" name="player_id" value="{$receiverId}">
    <div>[$form_send_to] <b>{$receiverFullName}</b> <span style="float:right">[$info_no_html_allowed]</span></div>
    <div>
      <textarea cols="85" rows="20" name="message_text" placeholder="[$form_enter_message]"></textarea>
    </div>
    <div>
      <input type="submit" value="[$form_pm_send]" class="button_charmenu">
    </div>
  </form>
</div>
<div class="centered">
  {if $staffmember}
    <a href="index.php?page=infoplayer&player_id={$receiverId}">[$back_to_player_info]</a>
  {else}
    <a href="index.php?page=player">[$back_to_player]</a>
  {/if}
</div>
