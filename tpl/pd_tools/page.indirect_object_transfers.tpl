{include file="template.title.[$lang].tpl" title="Indirect object transfers"}

<div class="page" style="width: 80%">
  <p>This tool displays *possible* situations when somebody tries to hide object transfer between characters of the same player.<br>
    It shows all situations when the portable object is passed through other characters or dropped and taken or stored and taken from the
    storage. It doesn't work for raws and coins.<br>
    Seeing some record on the list, however, doesn't mean somebody is cheating.<br>
    It's possible that the object was passed multiple times before reaching the second character.
    For example it can be sold, then put into warehouse and months later bought by other character of same player.<br>
    Every suspicious situation needs to be double-checked.<br>
    'Early character' means the one that is the original giver of the object,<br>
    'Later character' is the final receiver, both must be owned by the same player.<br>
    <br>
    Select date range:</p>
  <form action="index.php?page=indirectobjecttransfers" method="post">
    <label>Min later day: <input type="text" value="{$laterDayMin}" name="later_day_min"/></label><br>
    <label>Max later day: <input type="text" value="{$laterDayMax}" name="later_day_max"/></label><br>
    <input type="submit" value="Show!">
  </form>

  <p>
    Displaying {$tableSize} rows.
  </p>
  <div id="mytable"></div>

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
