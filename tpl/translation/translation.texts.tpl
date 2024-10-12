<br><br><b>[$page_translations_1]</b> <a href="index.php?page=managetranslations&action=add1&{$lang_url}">{$bracket1}[$small_button_add]{$bracket2}</a><br><br>
<table>
<tr>
  <td><i>[$form_name]</i></td>
  <td><i>[$form_language]</i></td>
  <td><i>[$form_last_update]</i></td>
  <td><i>[$form_author]</i></td>
  <td></td>
</tr>
{foreach $texts as $text}
<tr><td>{$text.name}</td>
  {if $text.language==$lang2}
  <td><b>{$langcode[{$text.language}]}</b></td>
  {else}
  <td>{$langcode[{$text.language}]}</td>
  {/if}
  <td>{$text.updated}</td>
  <td>{$text.author}</td>
  <td><a href="index.php?page=managetranslations&name={$text.id}&language={$text.language}&action=translate&previousaction={$action}&searchfor={$searchfor}&showall={$showall}&tagsearch={$tagsearch}&{$lang_url}">{$bracket1}[$small_button_edit_translate]{$bracket2}</a></td>
</tr>
{/foreach}
</table>