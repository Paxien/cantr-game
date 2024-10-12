<?php

$max_turn = HTTPContext::getInteger('max_turn');
if (!$max_turn) $max_turn = GameDate::NOW()->getDay();

$min_turn = HTTPContext::getInteger('min_turn');
if (!$min_turn) $min_turn = $max_turn-100; // default is last 100 days

$stats = array();
for ($i=$min_turn; $i<=$max_turn; $i++) {
  $stats[$i] = array(
    'papplied' => 0,
    'psubscribed' => 0,
    'prefused' => 0,
    'punsubscribed' => 0,
    'premoved' => 0,
    'pwarned' => 0,
    'pidledout' => 0,
    'totalp' => 0,
    'csubscribed' => 0,
    'cdied' => 0,
    'totalc' => 0
  );
}

function countForAction($action, $from, $to, &$stats, Db $db) {
  $stm = $db->prepare("SELECT turn AS day, COUNT(action) AS count FROM pcstatistics
    WHERE action = :action AND turn BETWEEN :from AND :to GROUP BY turn");
  $stm->bindStr("action", $action);
  $stm->bindInt("from", $from);
  $stm->bindInt("to", $to);
  $stm->execute();
  foreach ($stm->fetchAll() as $row) {
    $stats[$row->day][$action] = $row->count;
  }
}

function valueForAction($action, $from, $to, &$stats, Db $db) {
  $stm = $db->prepare("SELECT turn AS day, id AS count FROM pcstatistics
    WHERE action = :action AND turn BETWEEN :from AND :to GROUP BY turn");
  $stm->bindStr("action", $action);
  $stm->bindInt("from", $from);
  $stm->bindInt("to", $to);
  $stm->execute();
  foreach ($stm->fetchAll() as $row) {
    $stats[$row->day][$action] = $row->count;
  }
}


$db = Db::get();
// *** APPLIED PLAYERS

countForAction("papplied", $min_turn, $max_turn, $stats, $db);
countForAction("psubscribed", $min_turn, $max_turn, $stats, $db);
countForAction("prefused", $min_turn, $max_turn, $stats, $db);
countForAction("punsubscribed", $min_turn, $max_turn, $stats, $db);
countForAction("premoved", $min_turn, $max_turn, $stats, $db);
countForAction("pwarned", $min_turn, $max_turn, $stats, $db);
countForAction("pidledout", $min_turn, $max_turn, $stats, $db);
countForAction("csubscribed", $min_turn, $max_turn, $stats, $db);
countForAction("cdied", $min_turn, $max_turn, $stats, $db);
countForAction("csubscribed", $min_turn, $max_turn, $stats, $db);

// *** TOTAL NUMBER OF PLAYERS
valueForAction("totalp", $min_turn, $max_turn, $stats, $db);
valueForAction("totalc", $min_turn, $max_turn, $stats, $db);

// *** HEADLINE
echo "</table>";
echo '<form action="index.php?page=statistics&type=pcstatistics" method="post">From: <input type="text" name="min_turn">To: <input type="text" name="max_turn"><input type="submit" value="OK"></form>';
echo "<table border>";
echo "<tr>";
echo "<th>DAY</th>";
foreach($stats[$min_turn] as $header => $value) {
  echo "<th><span style=\"font-variant:small-caps; font-weight:bold\">" . $header . "</span></th>";
}
echo "</tr>";
		
        
// *** CONTENT
foreach ($stats as $day => $dayStat) {
  echo "<tr>";
  echo "<td>$day</td>";
  foreach ($dayStat as $key => $dayValue) {
    echo "<td>$dayValue</td>";
  }
  echo "</tr>";
}
