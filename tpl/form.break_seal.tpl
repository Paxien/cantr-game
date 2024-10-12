{include file="template.title.[$lang].tpl" title="[$title_emptyenvelope]"}

<div class="page">
  [$form_emptyenvelope]
  <ul class="plain">
    {foreach from=$seals item=seal}
    <li><img src="[$_IMAGES]/sealwax.png" align="absmiddle" title="[$seal_description]" /> <span style="color:yellow;">{$seal}</span>
    {/foreach}
  </ul>
</div>
<div class="centered">
  <a href="index.php?page=char.inventory"><input type="image" src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]"></a>
  <form method="post" action="index.php?page=break_seal&object_id={$object_id}">
    <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_emptyenvelope_cont]">
    <input type="hidden" name="data" value="yes">
  </form>
</div>
