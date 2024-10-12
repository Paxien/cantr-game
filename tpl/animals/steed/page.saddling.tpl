{include file="template.title.[$lang].tpl" title="[$title_animal_saddling]"}

<div class="page">
  [$text_animal_saddling]

  <div class="centered">
    <form method="post" action="index.php?page=animal_saddling&data=yes">
      <input type="hidden" name="animal_id" value="{$animalId}">
      <a href="index.php?page=char.objects"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_character]" /></a>
      <input type="image" src="[$_IMAGES]/button_forward2.gif" title="[$alt_animal_saddling]">
    </form>
  </div>
</div>
