{include file="translation/translation.options.[$lang].tpl"}

{if $texts|@count == 0}
<br><br><b>[$page_translations_1]</b> <a href="index.php?page=managetranslations&action=add1&{$lang_url}">{$bracket1}[$small_button_add]{$bracket2}</a><br><br>
[$no_search_results]<br>
{else}
{include file="translation/translation.texts.[$lang].tpl"}
{/if}
{include file="backlogout.[$lang].tpl"}