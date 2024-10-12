{include file="template.title.[$lang].tpl" title="Limitations overview"}

<div class="page">
  {foreach from=$limitations item=limCat}
    {foreach from=$limCat item=lims key=limName}
    <br>
    {$limName}:
    <br>
    <table border style="width:700px">
      <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Count</th>
        <th>Days left</th>
        <th>Lim. removal day</th>
      </tr>
      {foreach from=$lims item=lim}
        <tr>
          <td>{$lim.id}</td>
          <td>{$lim.name}</td>
          <td>{$lim.count}</td>
          <td>{$lim.time.day}</td>
          <td>{$lim.lift.day}</td>
        </tr>
      {/foreach}
    </table>
    {/foreach}
  {/foreach}
</div>

<div class="page">
  Locked players:
  <table border>
    <tr>
      <th>id</th>
      <th>name</th>
      <th>lastdate</th>
      <th>alive chars</th>
    </tr>
    {foreach from=$lockedPlrs item=plr}
      <tr>
        <td>{$plr->id}</td>
        <td>{$plr->name}</td>
        <td>{$plr->lastdate}</td>
        <td>{$plr->aliveChars}</td>
      </tr>
    {/foreach}
  </table>
</div>

<div class="centered">
  <a href="index.php?page=player"><img src="[$_IMAGES]/button_back2.gif" title="[$back_to_player]"></a>
</div>

