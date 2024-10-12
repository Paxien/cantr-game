{include file="translation/translation.options.[$lang].tpl"}
<!-- Table with messages -->
<b>[$page_translations_2]</b><br><br>

<table><tr>
  <td><i>[$form_name]</i></td>
  <td><i>[$form_language]</i></td>
  <td><i>[$form_last_update]</i></td>
  <td><i>[$form_author]</i></td>
  <td></td>
</tr>
{foreach from=$messages item=message}
<tr><td>{$message.name}</td>
  {if $message.language==$lang2}
  <td align=center><b>{$langcode[{$message.language}]}</b></td>
  {else}
  <td align=center>{$langcode[{$message.language}]}</td>
  {/if}
  <td>{$message.updated}</td>
  <td>{$message.author}</td>
  <td><a href="index.php?page=managetranslations&id={$message.id}&language={$message.language}&action=translmess&previousaction={$action}&showall={$showall}&{$lang_url}">{$bracket1}[$small_button_edit_translate]{$bracket2}</a>
</tr>
{/foreach}
</table>
{include file="translation/translation.texts.[$lang].tpl"}
{if $showall == "yes"}
<br><br>
<center><a href="index.php?page=managetranslations&showall={$showall}&start={$prev}&{$lang_url}">Previous</A> | <a href="index.php?page=managetranslations&showall={$showall}&start={$next}&$lang_url\">Next</a></center>
{/if}
<br><br>
{include file="backlogout.[$lang].tpl"}