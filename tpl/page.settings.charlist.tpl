{include file="template.title.[$lang].tpl" title="[$title_settings_charlist]"}

<div class="page">
  <table>
    <tr>
      <td width="300">[$settings_charlist_name]</td>
      <td width="50">[$settings_charlist_spawn]</td>
      <td width="50">[$settings_charlist_death]</td>
      <td width="100">[$settings_charlist_language]</td>
      <td width="200"><td>
    <tr>
    {foreach from=$chars item=char}
    <tr {if $char.death_day} style="color:#777777;"{/if}>
      <td>{$char.name} </td>
      <td>{$char.spawn_day}</td>
      <td>{$char.death_day}</td>
      <td>{$char.language}</td>
      <td>{if $char.blocked_days} <CANTR REPLACE NAME=settings_charlist_blocked_slot DAYS={$char.blocked_days}>{/if}</td>
    </tr>
    {/foreach}
  </table>
  
  <div class="centered">
    <a href="index.php?page=player" class="button_charmenu">[$back_to_player]</a>
  </div>
</div>