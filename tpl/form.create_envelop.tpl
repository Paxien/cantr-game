{include file="template.title.[$lang].tpl" title="[$title_create_envelop]"}

<form method="POST" action="index.php?page=create_envelop">
  <div class="page">
    [$form_title_envelop]: <input name="title" style="width:70%">
    <input type="hidden" name="data" value="yes">

    <div class="centered">
      <a href="index.php?page=char.inventory"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]"></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$button_create]">
    </div>
  </div>
</form>
