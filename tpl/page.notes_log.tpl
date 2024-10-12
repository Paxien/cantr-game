{include file="template.title.[$lang].tpl" title="Notes log"}

<div class="page">
  <form action="index.php?page=notes_log" method="post">
    between day <input type="text" name="search_from_day" size="5" value="{$search_from_day}">
    hour <input type="text" name="search_from_hour" size="2" value="{$search_from_hour}"> and
    day <input type="text" name="search_to_day" size="5" value="{$search_to_day}">
    hour <input type="text" name="search_to_hour" size="2" value="{$search_to_hour}"> <br>
    character id: <input type="text" name="search_char_id" size="10" value="{$search_char_id}"><br>
    object id: <input type="text" name="search_object_id" size="10" value="{$search_object_id}"> <br>
    note id (from obj_notes): <input type="text" name="search_note_id" size="10" value="{$search_note_id}"> <br>
    note title ('%' for wildcard): <input type="text" name="search_note_title" size="30" value="{$search_note_title}"> <br>
    Page: <input type="text" name="ptab" size="4" value="{$ptab}"> [range 1 to {$nCount}]<br>
    <input type="submit" value="SEARCH" style="margin-right:30px">
    {if $ptab > 1}
      <button name="ptab" type="submit" value="{math equation="a - 1"  a=$ptab}">< prev</button>
    {/if}
    {if $ptab < $nCount}
      <button name="ptab" type="submit" value="{math equation="a + 1"  a=$ptab}">next ></button>
    {/if}
  </form>
  <br><br>
  <table border align="center" class="altern">
    <tr>
      <td>Note name*</td><td>Object id</td><td>Note id</td><td>Character</td><td>action</td><td>date</td><td>[old title] versions</td>
    </tr>
    {foreach from=$notes_list item=note}
    <tr>
      <td>{$note->note_name}</td><td>{$note->object_id}</td>
      <td><a href="index.php?page=pdreadnote&note_id={$note->note_id}">{$note->note_id}</a></td>
      <td>{$note->char_name} ({$note->char_id}) of <a href="index.php?page=infoplayer&player_id={$note->player_id}">{$note->player_id}</a></td>
      <td>{$note->action}</td>
      <td>{$note->date}</td>
      <td>
        {if $note->prev_contents}
          {if $note->note_name != $note->prev_title && $note->prev_title}
            ['{$note->prev_title|truncate:15}']
          {/if}
          <a href="index.php?page=pdreadnote&notes_log_id={$note->id}">prev</a>
          {if $note->next_ver_id}
            <a href="index.php?page=pdreadnote&notes_log_id={$note->next_ver_id}">curr</a>
          {else}
            <a href="index.php?page=pdreadnote&note_id={$note->note_id}">curr</a>
          {/if}
        {/if}
      </td>
    </tr>
    {/foreach}
  </table>
  * lack of note name = note probably doesn't exist <br>
  <br>
  <br>
  <div class="centered">
    <a href="index.php?page=player"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_player]"></a>
  </div>
</div>