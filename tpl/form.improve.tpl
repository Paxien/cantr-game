{include file="template.title.[$lang].tpl" title="[$title_improve]"}

<div class="page">
  <form method=post action="index.php?page=improve">
  <input type="hidden" name="data" value="yes">
  <input type="hidden" name="connection" value="{$connection}">
  <table>
    <tr>
      <td style="vertical-align:text-top;">
        [$road_possible_actions]<br>
        {foreach $options as $optName => $opt}
          <label style="cursor:pointer;">
            <input class="impAction" type="radio" name="actionType" value="{$optName}">{$opt.description}
          </label><br>
        {/foreach}
      </td>
      <td id="details" style="padding-left:25px;vertical-align:text-top;">
      </td>
    </tr>
  </table>

  <div class="centered">
    <a href="index.php?page=char"><img src="[$_IMAGES]/button_back2.gif" alt="[$back_to_character]"></a>
      <input type=image src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]">
  </div>
  </form>
</div>

<script type="text/javascript">
var options = {$optionsJson};
{literal}
$(function() {
  $(".impAction").click(function(evt) {
    var tg = $(evt.target);
    $("#details").html(options[tg.val()].details);
  });
  $(".impAction").first().click();
});
{/literal}
</script>
