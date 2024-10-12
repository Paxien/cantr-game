{include file="template.title.[$lang].tpl" title="$note_title"} 


<center>
<table width="700">
  <tr>
    <td width="700" bgcolor="#006600">
      <pre>{$contents}</pre>
    </td>
  </tr>
</table>
<br />
<br />
<table width="700">
  <tr>
  {if $previousNote}
    <td style="text-align:left;width:50%">
      <a href="index.php?page=readnote&object_id={$previousNote.id}"><img src="[$_IMAGES]/button_small_arrow_left.gif" alt="[$alt_book_prev_page]"></a>
      <span class="note-title">{$previousNote.title}</span>
    </td>
  {/if}
  {if $nextNote}
    <td style="text-align:right;width:50%">
      <span class="note-title">{$nextNote.title}</span>
      <a href="index.php?page=readnote&object_id={$nextNote.id}"><img src="[$_IMAGES]/button_small_arrow_right.gif" alt="[$alt_book_next_page]"></a>
    </td>
  {/if}
  </tr>
</table>
{if !$hideBackButton}
<table width="700">
  <tr>
    <td align="center">
        <form method="post" action="index.php?page={$back_link}">
          <input type="image" src="[$_IMAGES]/button_back2.gif" title="[$back_to_previous]">
        </form>
    </td>
  {if $Seals}
    <td width="400" align="center">
      {foreach from=$Seals item=SealName}
        <img src="[$_IMAGES]/sealwax.png" align="absmiddle" title="[$seal_description]" /><span style="color: #ff0;"> &nbsp; {$SealName}</span>
        <br />
      {/foreach}
    </td>
  {/if}
  </tr>
</table>
{/if}

</center>
