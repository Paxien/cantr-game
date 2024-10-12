{include file="template.title.[$lang].tpl" title="[$title_undock]"}

<form method="post" action="index.php?page=undock">
  <div class="page">
    [$undock_description]<br>
    <ul class="plain">
      {foreach from=$dirs key=direction item=dirDesc}
        <li>
          <label style="cursor:pointer"><input type="radio" name="direction" value="{$direction}"> <CANTR REPLACE NAME={$dirDesc}></label>
        </li>
      {/foreach}
    </ul>
    <input type="submit" value="[$button_continue]">
  </div>
</form>

{literal}
<script type="text/javascript">
  $(function() {
    $('input[name="direction"]').first().click();
  });
</script>
{/literal}