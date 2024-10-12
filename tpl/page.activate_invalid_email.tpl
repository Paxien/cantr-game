{include file="template.title.[$lang].tpl" title="[$title_activate_invalid_email]"}

<div class="page">
  {if $success}
    [$email_confirmation_accepted]
  {else}
    [$email_confirmation_failed]
  {/if}
</div>

<div class="centered">
  <a href="index.php?page=player"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_player]"></a>
</div>