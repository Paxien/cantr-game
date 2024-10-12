<?php

$db = Db::get();
  $receiver_types = "520, 521";
  $transmitter_types = "522, 535, 536, 537, 567, 571";
  $repeater_types = "660, 661, 662, 663";
  $radiotypes = $receiver_types . ", " . $transmitter_types . ", " . $repeater_types;

  //Check if there are locations with NULL coordinates, excluding ones with region 0 since that means traveling

  $stm = $db->query("
    SELECT kid.id, kid.name, mom.x, mom.y, kid.area
    FROM locations kid, locations mom
    WHERE kid.region=mom.id AND (kid.x is null OR kid.y IS NULL) AND kid.region>0");
  $num_rows = $stm->rowCount();
  if ($num_rows == 0) {
    echo "There are no locations with NULL coordinates.<br />";
  } else {
    //List ones that were found
    echo "Found $num_rows locations with NULL coordinates.<br />";
    echo "<table border='1'>\n<tr><th>ID</th><th>Name</th><th>Parent X</th><th>Parent Y</th><th>Area (location type)</th></tr>\n";
    while (list ($id, $name, $x, $y, $area) = $stm->fetch(PDO::FETCH_NUM)) {
      echo "<tr><td>$id</td><td>$name</td><td>$x</td><td>$y</td><td>$area</td></tr>\n";
    }
    echo "</table><br />";
    //Fix ones that were found
    echo "Copying coordinates from the parent location unless they were also NULL.<br />";
    echo "If there were parent locations with NULL coordinates (excluding locations 22 and 23), you should run the script again.";
    $stm = $db->query("
      UPDATE locations kid, locations mom SET kid.x=mom.x, kid.y=mom.y
      WHERE kid.region=mom.id AND (kid.x IS NULL OR kid.y IS NULL) AND mom.x IS NOT NULL AND mom.y IS NOT NULL AND kid.region>0");

    $rows_affected = $stm->rowCount();
    echo "Fixed $rows_affected locations.<br />";
  }
  //Check and fix if there are radio objects with no specifics (frequency) since if the field is blank, it would make the next stage fail
  $stm = $db->query("
    SELECT `id` FROM `objects`
    WHERE `type` IN ($radiotypes) AND (`specifics` IS NULL OR `specifics` LIKE '')"); // TODO injected value

$num_rows = $stm->rowCount();
if ($num_rows == 0) {
  echo "There are no radios with a missing frequency.<br />";
} else {
    echo "Found $num_rows radios with a missing frequency. Trying to find frequency...<br />";
    foreach ($stm->fetchAll() as $obj) {
      $stm = $db->prepare("SELECT frequency FROM radios WHERE item = :objectId AND frequency IS NOT NULL AND frequency NOT LIKE '' LIMIT 1");
      $stm->bindInt("objectId", $obj->id);
      $stm->execute();
      if ($stm->rowCount() > 0) {
        $obj2 = $stm->fetchObject();
        echo "Found frequency for $obj->id. ";
        $stm = $db->prepare("UPDATE objects SET specifics = :specifics WHERE id = :objectId LIMIT 1");
        $stm->bindStr("specifics", (string)$obj2->frequency);
        $stm->bindInt("objectId", $obj->id);
        $stm->execute();
        if ($stm->rowCount() > 0) echo "Updated frequency to $obj2->frequency.<br />";
        else echo "Frequency update failed<br />";//most likely the frequency isn't a number in this case
      } else {
        echo "Couldn't find frequency for $obj->id. ";
        $stm = $db->prepare("UPDATE objects SET specifics='100' WHERE id= :objectId LIMIT 1");
        $stm->bindInt("objectId", $obj->id);
        $stm->execute();
        if ($stm->rowCount() > 0) echo "Reset frequency to 100.<br />";
        else echo "Attempt to reset frequency failed<br />";
      }
    }
  }
  //Check if there are radios that exist as objects but not in the radios table.
  $stm = $db->query("
    SELECT o.`id`, o.`type`, o.`specifics`, o.`location`
    FROM `objects` o LEFT JOIN `radios` r ON o.`id`=r.`item`
    WHERE o.`type` IN ($radiotypes) AND o.location > 0 AND r.`item` IS NULL");
  $num_rows = $stm->rowCount();
  if ($num_rows==0) echo "All radios in the objects table are also listed in the radios table.<br />";
  else {
    echo "Found $num_rows radios that exist only in the objects table. Adding the missing ones...<br />";
    echo "<table border='1'>\n<tr><th>ID</th><th>Type</th><th>Frequency</th><th>Location</th></tr>\n";
    while (list ($id, $type, $freq, $loc) = $stm->fetch(PDO::FETCH_NUM)) {
      echo "<tr><td>$id</td><td>$type</td><td>$freq</td><td>$loc</td></tr>\n";
      $updateStm = $db->prepare("INSERT INTO radios (item, type, frequency, location, x, y) VALUES (:item, :type, :frequency, :locationId, 0, 0)");
      $updateStm->bindInt("item", $id);
      $updateStm->bindInt("type", $type);
      $updateStm->bindInt("frequency", $freq);
      $updateStm->bindInt("locationId", $loc);
      $updateStm->execute();
      //This doesn't take the repeater value or the coordinates into account since they'll be updated in the next stages anyway
    }
    echo "</table><br />";
  }
  //Check if there are entries where the coordinates are out of sync.
  $stm = $db->query("
    SELECT r.item, r.location, r.x, r.y, l.x, l.y
    FROM radios r JOIN locations l ON r.location=l.id
    WHERE r.x!=l.x OR r.y!=l.y OR r.x IS NULL OR r.y IS NULL");
  //I had to include the IS NULL stuff separately because it cannot compare NULL and a number even though if you do a query for the
  //opposite (=) then it does recognize that NULL does not equal what ever the number happens to be
  $num_rows = $stm->rowCount();
  if ($num_rows==0) {
    echo "No radio coordinates are out of sync.<br />";
  } else {
    echo "There are $num_rows radios with coordinates out of sync. Adjusting...<br />";
    echo "<table border='1'>\n<tr><th>ID</th><th>Location</th><th>Old X</th><th>Old Y</th><th>New X</th><th>New Y</th></tr>\n";
    while (list ($id, $loc, $rx, $ry, $lx, $ly) = $stm->fetch(PDO::FETCH_NUM)) {
      echo "<tr><td>$id</td><td>$loc</td><td>$rx</td><td>$ry</td><td>$lx</td><td>$ly</td></tr>\n";
      $updateStm = $db->prepare("UPDATE radios SET x = :x, y = :y WHERE item = :item");
      $updateStm->bindInt("x", $lx);
      $updateStm->bindInt("y", $ly);
      $updateStm->bindInt("item", $id);
      $updateStm->execute();
    }
    echo "</table><br />";
  }
  //Check if there are receivers marked as transmitters or repeaters, fix if yes.
  $stm = $db->query("UPDATE radios SET repeater=0 WHERE repeater>0 AND `type` IN ($receiver_types)");
  $rows_affected = $stm->rowCount();
  if ($rows_affected==-1) echo "Receiver update query failed<br />";
  else if ($rows_affected==0) echo "There are no receivers marked as transmitters or repeaters.<br />";
  else echo "There were $rows_affected receivers marked as transmitters or repeaters.<br />";

  //Check if there are repeaters marked as receivers or transmitters, fix if yes.
  $stm = $db->query("UPDATE radios SET repeater=1 WHERE repeater IN (0,2) AND `type` IN ($repeater_types)");
  $rows_affected = $stm->rowCount();
  if ($rows_affected==-1) echo "Repeater update query failed<br />";
  else if ($rows_affected==0) echo "There are no repeaters marked as receivers or transmitters.<br />";
  else echo "There were $rows_affected repeaters marked as receivers or transmitters.<br />";

  //Check if there are transmitters marked as receivers or repeaters, fix if yes.
  $stm = $db->query("UPDATE radios SET repeater=2 WHERE repeater <2 AND `type` IN ($transmitter_types)");
  $rows_affected = $stm->rowCount();
  if ($rows_affected==-1) echo "Transmitter update query failed<br />";
  else if ($rows_affected==0) echo "There are no transmitters marked as receivers or repeaters.<br />";
  else echo "There were $rows_affected transmitters marked as receivers or repeaters.<br />";

  //Check if there are any with mismatched frequencies. Fix if there are.
  $stm = $db->query("
    SELECT r.`item`, r.`location`, o.`specifics`, r.`frequency`
    FROM `radios` r JOIN `objects` o on r.`item`=o.`id`
    WHERE r.`frequency`!=o.`specifics`");
  $rows_affected = $stm->rowCount();
  if ($num_rows==0) echo "No radio frequencies are out of sync.<br />";
  else {
    echo "There are $num_rows radios with frequencies out of sync. Adjusting...<br />";
    echo "<table border='1'>\n<tr><th>ID</th><th>Location</th><th>New frequency</th><th>Old Frequency</th></tr>\n";
    while (list ($id, $loc, $o_freq, $r_freq) = $stm->fetch(PDO::FETCH_NUM)) {
      echo "<tr><td>$id</td><td>$loc</td><td>$o_freq</td><td>$r_freq</td></tr>\n";
      $updateStm = $db->prepare("UPDATE radios SET frequency = :frequency WHERE item = :item");
      $updateStm->bindInt("frequency", $o_freq);
      $updateStm->bindInt("item", $id);
      $updateStm->execute();
    }
    echo "</table><br />";
  }
  echo "Done.<br />";
