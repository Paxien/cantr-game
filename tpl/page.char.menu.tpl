{include file="template.title.[$lang].tpl" title="[$title_character_menu]"}

<div id="character_menu" class="page">
  <a href="index.php?page=writenote">
    <img src="[$_IMAGES]/button_note.gif" title="[$alt_write_a_note]"></a>
  <a href="index.php?page=create_envelop">
    <img src="[$_IMAGES]/button_envelop.gif" title="[$alt_create_an_envelop]"></a>
  {if $canManufacture}
    <a href="index.php?page=build">
      <img src="[$_IMAGES]/button_build.gif" title="[$alt_build_or_manufacture]"></a>
  {/if}
  <a href="index.php?page=char.settings">
    <img src="[$_IMAGES]/button_listraws.gif" title="[$alt_charsettings]"></a>
</div>
