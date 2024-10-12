{include file="template.title.[$lang].tpl" title="Travels timeline"}

<div class="page" style="width: 80%">
  <p>Please enter comma-separated list of player ids.</p>
  <form action="index.php?page=travels_timeline" method="post">
    <input type="text" value="{$rawPlayersList}" name="player_ids"
           style="width: 100%; box-sizing: border-box"
    /><input type="submit" value="Show!">
  </form>

  {if $displayData}
    <p>
      Displaying {$tableSize} rows.<br>
      Concerned characters:
    </p>
    <table border>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Status</th>
        <th>Player name</th>
        <th>Player Id</th>
      </tr>
      {foreach $concernedCharacters as $charInfo}
        <tr>
          <td>
            {$charInfo.id}
          </td>
          <td>
            {$charInfo.name}
          </td>
          <td>
            {$charInfo.status}
          </td>
          <td>
            {$charInfo.playerName}
          </td>
          <td>
            {$charInfo.playerId}
          </td>
        </tr>
      {/foreach}
    </table>
    <div id="mytable"></div>
  {/if}

  <div class="centered">
    <a href="index.php?page=player" class="button_charmenu">[$back_to_player]</a>
  </div>
</div>

<script type="text/javascript">

  var travelData = {$tableData};
  $(function() {
    $('#mytable').tablesorter({
      theme: 'blue',
      widgets: ['zebra'],
      widgetOptions: {
        // build_type   : 'array', // can sometimes be detected if undefined
        build_source: travelData,
        build_headers: {
          rows: 1,  // Number of header rows
          classes: [], // Header classes to apply to cells
          text: [], // Header cell text
        },
        build_footers: {
          rows: 0,   // Number of header rows from the csv
        }
      }
    });
  });
</script>

<link rel="stylesheet" href="css/theme.blue.css">
<script src="js/libs/jquery.tablesorter.js"></script>
<!-- build table widget -->
<script type="text/javascript" src="js/libs/widget-build-table.js"></script>
