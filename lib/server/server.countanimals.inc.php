<?php

$page = "server.countanimals";
include "server.header.inc.php";
$today = GameDate::NOW()->getDay();

$message = "Overview animals: $today";

$message .= " - Summary Report\n\n";

$db = Db::get();
$stm = $db->query("SELECT SUM(number) AS animals, COUNT(id) AS packs FROM animals");
$counts = $stm->fetchObject();

$message .= "=== Totals ===\n\n";
$message .= "Total number of animals: $counts->animals\n";
$message .= "Total number of animal packs: $counts->packs\n";

// added die off stats and estimated growth
$message .=" TOTAL POPULATION ESTIMATES \n Animal;Total N;\n ";
$stm = $db->query("SELECT * FROM animal_types");
foreach ($stm->fetchAll() as $type_info) {
  $message.= " " . $type_info->name . " : ";  
  $stm = $db->prepare("SELECT COUNT(*) as acount, SUM(number) as asum FROM animals WHERE type = :id");
  $stm->bindInt("id", $type_info->id);
  $stm->execute();
  
  $info_tot = $stm->fetchObject();
  $message.= "$info_tot->asum ($info_tot->acount)\n";
}

$title = "Summary animal count $today";

$mailService = new MailService("Cantr Resources Department", $GLOBALS['emailResources']);
$mailService->sendPlaintext($GLOBALS['emailResources'], $title, $message);

include "server/server.footer.inc.php";
