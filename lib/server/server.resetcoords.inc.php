<?php

$page = "server.resetcoords";
include "server.header.inc.php";

echo "\nChecking coordinates:\n";

$db = Db::get();
$stm = $db->query("SELECT id,name,region FROM locations WHERE (type=2 OR type=4) AND x IS NULL");

foreach ($stm->fetchAll() as $loc_info) {
  $stm = $db->prepare("SELECT name,x,y FROM locations WHERE id = :locationId");
  $stm->bindInt("locationId", $loc_info->region);
  $stm->execute();
  $parent_loc_info = $stm->fetchObject();

  if (isset($parent_loc_info->x)) {
    $stm = $db->prepare("UPDATE locations SET x = :x, y = :y WHERE id = :id");
    $stm->bindInt("x", $parent_loc_info->x);
    $stm->bindInt("y", $parent_loc_info->y);
    $stm->bindInt("id", $loc_info->id);
    $stm->execute();
  }
}

include "server/server.footer.inc.php";
