<?php
$page = "server.taint";
include "server.header.inc.php";

$db = Db::get();

$globalConfig = new GlobalConfig($db);
$taintEnabled = $globalConfig->isUniversalTaintEnabled();
if ($taintEnabled) {
  $deteriorationManager = new DeteriorationManager($db);
  $taintReport = $deteriorationManager->taintAllResources();

  foreach ($taintReport as $entry) {
    echo "Executing taint of $entry->rawName (id: $entry->rawId). $entry->amount grams have tainted in " .
      number_format($entry->secondsProcessing, 2) . ".\n";
  }

} else {
  echo "No taint because it is disabled in the global config\n";
}

import_lib("func.expireobject.inc.php");
expire_multiple_objects("type = 2 AND weight = 0");

include "server/server.footer.inc.php";
