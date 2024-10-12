{include file="template.title.[$lang].tpl" title="[$title_select_note]"}
<div class="page">
  <form method="post" action="index.php?page=seal">
    [$select_note_1]<br><br>
    {foreach from=$notes key=k item=note}
      <label style="cursor:pointer;"><input type="radio" name="note_id" value="{$note->id}" {if $k == 0 }checked{/if}>{$note->title}</label><br>
    {/foreach}
    <br>
    <input type="hidden" value="{$object_id}" name="object_id">
    <div class="centered">
      <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]"></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$button_continue]">
      <input type="hidden" name="data" value="yes">
    </div>
  </form>
</div>
