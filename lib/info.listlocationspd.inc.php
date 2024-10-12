<?php

// SANITIZE INPUT
$main_id = HTTPContext::getInteger('main_id');

function output_sublocations ($main_id, Db $db) {

  $stm = $db->prepare("SELECT * FROM locations WHERE region = :region AND type>1 ORDER BY id");
  $stm->bindInt("region", $main_id);
  $stm->execute();
  echo "<ul>\n";
  foreach ($stm->fetchAll() as $subloc_info) {
    echo "<li>$subloc_info->name ($subloc_info->id) ";
    switch ($subloc_info->type) {
      case 2: echo "[building]";
              break;
      case 3: echo "[vehicle/vessel]";
              break;
      case 5: echo "[sailing vessel]";
              break;
    }
    echo "</li>\n";

    output_sublocations($subloc_info->id, $db);
  }
  echo "</ul>\n";
}

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::VIEW_LIST_OF_LOCATIONS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$db = Db::get();
	
  $stm = $db->query("SELECT * FROM locations WHERE type=1 OR region=0 ORDER BY id");

  foreach ($stm->fetchAll() as $mainloc_info) {
    
    echo "<BR>$mainloc_info->name ($mainloc_info->id):<BR>";
    output_sublocations($mainloc_info->id, $db);
  }
