{include file="template.title.[$lang].tpl" title="[$info_animals_title]"}

<div class="page">
  <form action="index.php?page=animal_butcher" method="post">
  <input type="hidden" name="object_id" value="{$object_id}" />
  <input type="hidden" name="data" value="yes" />
    <table>
      <tr>
        <td style="width:300px;vertical-align:top;">
          <span class="listCaption">[$header_animals]:</span>
          <ul class="plain animalList">
          {foreach from=$animals item=animal key=animal_id}
            <li><label><input type="radio" name="animal_id" value="{$animal_id}" />{$animal}</label></li>
          {/foreach}
          </ul>
        </td>
        <td style="vertical-align:top;">
          <div id="detailsPanel" style="display:none;">
            <div id="continueButton" class="centered" style="display:none;"><input type="submit" class="button_charmenu" value="[$form_continue]" /></div>
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <div style="text-align:center"><a href="index.php?page=char.objects"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]" /></a></div>
        </td>
      </tr>
    </table>
  </form>
</div>

<script type="text/javascript">
var text = {$butcher_text};
</script>
<script type="text/javascript" src="[$JS_VERSION]/js/page.animals.butcher.js"></script>