<form id="formTranslate" method=post action="index.php?page=managetranslations&showall={$showall}&searchfor={$searchfor}&action={if $action=='translmess'}storemess{else}store{/if}&previousaction={$previousaction}&tagsearch={$tagsearch}&{$lang_url}">
  <table>
    <tr>
      <td>{if $action=="translmess"}[$form_id]{else}[$form_name]{/if}:</td><td><input type=text size=60 name={$nameType} value="{$text->{$nameType}}"></td>
    </tr>
    
    <tr>
      <td>[$form_language]:</td>
      <td><select name=language>                     
      {for $count_lang=1 to $langcode|@count}
        <option value={$count_lang}{if $action != "add1" && ($language == $count_lang || $showall == "no" && $lang1 == $count_lang) || $action == "add1" && 1 == $count_lang} selected{/if}>{$langcode.$count_lang}</option>
      {/for}
        </select>
        </td>
    </tr>
    {if $action!="add1"}
    <tr valign=top>
      <td>[$form_original_contents]:</td>
      <td><textarea cols=70 rows=11 name=original_content style="background-color:#fffffe" readonly>{$original_text->content}</textarea></td>
    </tr>
    {/if}
    <tr valign=top>
      <td>[$form_contents]:</td>
      <td><textarea cols=70 rows=11 name=content>{$text->content}</textarea></td>
    </tr>
    {if $action!="translmess"}
    <tr>
      <td>[$form_grammar]:</td>
      <td><input type=text size=60 name=grammar value="{$text->grammar}"></td>
    </tr>
    {/if}
    {if $isSample}
    <tr>
      <td>[$sample_output]: <br><i>[$save_and_refresh]</i></td>
      <td>{$sample}</td>
    </tr>
    {/if}
  </table>
  <center><br><input type=submit value="[$button_store]"></center>
  <input type=hidden name=type value="{$type}">
</form>
{include file="translation/translation.back.[$lang].tpl"}