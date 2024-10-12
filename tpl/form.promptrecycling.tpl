{include file="template.title.[$lang].tpl" title="[$title_confirm_disassemble]"}

<div class="page">
  <form method="post" action="index.php?page=recycling">
    <input type="hidden" name="object_id" value="{$object_id}">
    <p class="centered">
      [$desc_recycling_1] <b><CANTR OBJNAME ID={$object_id}></b><br>
      [$desc_recycling_2]
    </p>
    <div class="centered">
      <a href="index.php?page=char.objects"><img src="[$_IMAGES]/button_back2.gif" alt="[$back_to_character]"></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_pick_the_lock]">
    </div>
  </form>
</div>
