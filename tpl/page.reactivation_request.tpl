{include file="template.title.[$lang].tpl" title="[$title_reactivation]"}

<div class="page">
{if $alreadyAwaitingReactivation}
  <p>
    <CANTR REPLACE NAME=awaiting_reactivation_text EMAIL={$email|urlencode} >
  </p>
{else}
  <p>
    {if $playerStatus == PlayerConstants::UNSUBSCRIBED}
    <CANTR REPLACE NAME=reactivation_text_unsubscribed FIRSTNAME={$firstName|urlencode}>
    {else}
    <CANTR REPLACE NAME=reactivation_text_idled_out FIRSTNAME={$firstName|urlencode}>
    {/if}
  </p>
  <div class="ctaContainer"><a class="ctaLink" href="index.php?page=reactivation&data=yes"><div class="cta">[$reactivation_request_submit]</div></a></div>
{/if}
  <div class="centered"><a class="button_charmenu" href="index.php?page=logout">[$back_to_main]</a></div>
</div>
