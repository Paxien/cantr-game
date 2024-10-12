{include file="template.title.[$lang].tpl" title="Multiaccount cookieless tracker"}

<script type="text/javascript" src="lib/vis/vis.min.js"></script>
<link href="lib/vis/vis.min.css" rel="stylesheet" type="text/css" />

<div class="page">
  {foreach $groups as $groupId => $rows}
    <table border>
      <tr><td>player</td><td>last date</td><td>count</td></tr>
    {foreach $rows as $row}
      <tr><td>{$row->player}</td><td>{$row->date}</td><td>{$row->count}</td></tr>
    {/foreach}
    </table>
    <br><br><br>
  {/foreach}
</div>

<div id="graph_visualization" style="width:95%;height: 1200px;border: solid #fff 1px">
</div>

<script type="text/javascript">
  // create an array with nodes

  var nodes = [
    {foreach $groups as $groupId => $rows}
      { id: "{$groupId}_group", label: "{$groupId} group", color: "#d44" },
    {/foreach}
    {foreach $nodes as $nodeId => $nothing}
      { id: "{$nodeId}", label: "{$nodeId}", color: "#8f8" },
    {/foreach}
  ];

  nodes = new vis.DataSet(nodes);

  // create an array with edges
  var edges = new vis.DataSet([
    {foreach $groups as $groupId => $rows}
      {foreach $rows as $row}
        { from: "{$row->player}", to: "{$groupId}_group", value: {$row->count} },
      {/foreach}
    {/foreach}
  ]);

  // create a network
  var container = $("#graph_visualization")[0];
  var data = {
    nodes: nodes,
    edges: edges
  };
  var options = { };
  var network = new vis.Network(container, data, options);
</script>