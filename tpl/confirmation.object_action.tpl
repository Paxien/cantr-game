{include file="template.title.[$lang].tpl" title="<CANTR REPLACE NAME=$title>"}

<div class="page">
  <p class="centered"><b><CANTR REPLACE NAME={$text}></b></p>

  <div class="centered">
    <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"></a>
    <a href="index.php?page={$action}&object_id={$object_id}&data=yes"><img src="[$_IMAGES]/button_forward2.gif" title="[$alt_continue]" /></a>
  </div>
</div>
