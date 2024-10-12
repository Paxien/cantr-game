<center>
<table>
  <tr>
    <td>
      <form method="post" action="index.php?page=managetranslations&showall=yes{if $action=='search'}&tagsearch=yes{/if}&{$lang_url}">
        <input type="submit" value="[$page_translations_3]" class="button_charmenu{if $showall == 'yes'}active{/if}"/>
      </form>
    </td>
    <td>
      <form method="post" action="index.php?page=managetranslations&showall=no&{if $action=='search'}&tagsearch=yes{/if}{$lang_url}">
        <input type="submit" value="[$page_translations_4]" class="button_charmenu{if $showall == 'no'}active{/if}"/>
      </form>
    </td>
    <td>
      <form method="post" action="index.php?page=managetranslations&showall=updated&{if $action=='search'}&tagsearch=yes{/if}{$lang_url}">
        <input type="submit" value="[$updated_english]" class="button_charmenu{if $showall == 'updated'}active{/if}"/>
      </form>
    </td>
  </tr>
</table>
<form method=post action="{$current_url}">
<table>
  <tr>
    <td>[$form_translation_source]</td>
    <td><select name="lang2">
    {for $count_lang=1 to $langcode|@count}
      <option value={$count_lang} {if $count_lang == $lang2}selected{/if}>{$langcode.$count_lang}</option>
    {/for}
    </select></td>
    <td>[$form_translation_target]</td>
    <td><select name="lang1">
      {for $count_lang=1 to $langcode|@count}
        <option value={$count_lang} {if $count_lang == $lang1}selected{/if}>{$langcode.$count_lang}</option>
      {/for}
      </select>
    </td>
    <td><input type=submit value="[$button_submit]"/></td>
  </tr>
</table>
</form>
</center>
<form method=post action="index.php?page=managetranslations&action=search&showall={$showall}&{$lang_url}">
  <b>[$page_searchtranslations]</b>
  <table><tr>
    <td>[$form_searchtranslations]</td>
    <td><input type=text size=60 name=searchfor value="{$searchfor}" required/></td>
    <td><label>[$page_searchtagnames]<input type=checkbox name=tagsearch value=yes{if $tagsearch!="no"} checked{/if}/><label></td>
  </tr>
  <tr>
    <td></td>
    <td><input type=submit value="[$button_search]"></td>
    <td><a href="index.php?page=managetranslations&name={$text.id}&language={$text.language}&action=download&previousaction={$action}&searchfor={$searchfor}&showall={$showall}&tagsearch={$tagsearch}&{$lang_url}">[$button_download]</a></td>
  </tr>
  </table>
</form><br><br>