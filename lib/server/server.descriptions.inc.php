<?php

$page = "server.descriptions";
include "server.header.inc.php";

$db = Db::get();
$stm = $db->query("SELECT id,sex,register,description,spawning_age FROM chars ORDER BY id");
foreach ($stm->fetchAll() as $char_info) {

  if ($char_info->sex == 1) { $description = "a man"; $his = "his"; } else { $description = "a woman"; $his = "her"; }

  $age = floor((($turn->day - $char_info->register) / 20 + $char_info->spawning_age) / 10);

  switch ($age) {

  case 0 : $description .= " child"; break;
  case 1 : $description .= " in $his teens"; break;
  case 2 : $description .= " in $his twenties"; break;
  case 3 : $description .= " in $his thirties"; break;
  case 4 : $description .= " in $his fourties"; break;
  case 5 : $description .= " in $his fifties"; break;
  case 6 : $description .= " in $his sixties"; break;
  case 7 : $description .= " in $his seventies"; break;
  case 8 : $description .= " in $his eighties"; break;
  case 9 : $description .= " in $his nineties"; break;
  default:
    if ($age < 15) {
      $description .= " who is old";
    } elseif ($age < 20) {
      $description .= " who is very old";
    } elseif ($age < 25) {
      $description .= " who is extremely old";
    } elseif ($age < 30) {
      $description .= " who is ancient";
    } elseif ($age < 35) {
      $description .= " who is very ancient";
    } elseif ($age >= 35)  {
      $description .= " who is extremely ancient";
    }
  }

  if ($description != $char_info->description) {

    $stm = $db->prepare("UPDATE chars SET description = :description WHERE id = :charId");
    $stm->bindStr("description", $description);
    $stm->bindInt("charId", $char_info->id);
    $stm->execute();
  }
}

include "server/server.footer.inc.php";

?>

