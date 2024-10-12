<form id="formDownload" method=post action="index.php?page=managetranslations&action=downloadfile">
  <table>
    <tr>
      <td>[$form_translation_source]</td>
      <td><select name="lang2">
      {for $count_lang=1 to $langcode|@count}
        <option value={$count_lang} {if $count_lang == $lang2}selected{/if}>{$langcode.$count_lang}</option>
      {/for}
      </select></td>
    </tr>
    <tr>
      <td>[$form_translation_target]</td>
      <td><select name="lang1">
        {for $count_lang=1 to $langcode|@count}
          <option value={$count_lang} {if $count_lang == $lang1}selected{/if}>{$langcode.$count_lang}</option>
        {/for}
        </select>
      </td>
    </tr>
  </table>
  <input type="radio" name="download_option" value="untranslated_tags" id="download_option1">
  <label for="download_option1">[$download_untranslated_tags]</label><br>
  <input type="radio" name="download_option" value="untranslated_content" id="download_option2">
  <label for="download_option2">[$download_untranslated_content]</label><br>
  <input type="radio" name="download_option" value="memory" id="download_option3">
  <label for="download_option3">[$download_memory]</label><br>
  <input type="radio" name="download_option" value="memory_tags" id="download_option4">
  <label for="download_option4">[$download_memory_tags]</label><br>
  <input type="radio" name="download_option" value="all_tags" id="download_option5">
  <label for="download_option5">[$download_all_tags]</label><br>
  <center><input type=submit value="[$button_download]"></center>
</form>
<iframe id="downloadFrame" src="" style="display:none; visibility:hidden;"></iframe>
{include file="translation/translation.back.[$lang].tpl"}