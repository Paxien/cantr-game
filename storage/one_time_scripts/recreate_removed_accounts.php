<?php

error_reporting(E_ALL);
$page = "recreate_removed_accounts";
include("../../lib/stddef.inc.php");

$dbh = new database(); // old db driver, the script would need to be migrated
$dbh->open();


$ref = do_query("SELECT pcs.id FROM `pcstatistics` pcs WHERE action = 'punsubscribed'
  AND NOT EXISTS (SELECT * FROM players p WHERE p.id = pcs.id) GROUP BY pcs.id ORDER BY pcs.id");

$missingPlayerIds = fetch_scalars($ref);

foreach ($missingPlayerIds as $playerId) {
  echo "$playerId ";
  $language = do_scalar("SELECT language FROM chars WHERE player = $playerId
    GROUP BY language ORDER BY COUNT(*) DESC");
  if (!$language) {
    echo "unknown language ";
    $language = 1;
  }

  echo "$language, active ";
  $registerDay = do_scalar("SELECT MIN(register) FROM chars WHERE player = $playerId");
  if (!$registerDay) {
    // check register day of the account created right after that
    $registerDay = do_scalar("SELECT register FROM players WHERE id > $playerId ORDER BY id LIMIT 1");
    echo "~~";
  }
  echo $registerDay . "-";

  $removalDate = do_scalar("SELECT turn FROM pcstatistics WHERE action = 'punsubscribed' AND id = $playerId");
  echo "$removalDate ";

  $ip = do_scalar("SELECT ip FROM ips WHERE player = $playerId ORDER BY lasttime DESC LIMIT 1");
  echo "last login: $ip ";
  echo "\n";

  $ip = mysql_real_escape_string($ip);

  do_query("INSERT INTO `players`
  (`id`, `username`, `firstname`, `lastname`, `email`, `nick`,
  `language`, `password`, `register`,
  `lastdate`, `lasttime`, `lastlogin`,
  `status`)
  VALUES
  ($playerId, NULL, 'recovered account', 'removed after unsub', 'unknown@unknown.com', 'unknown',
  $language,
  'unknown',
  $registerDay,
  $removalDate,
  0,
  '$ip',
  " . PlayerConstants::REMOVED . ")");
}