<?php
include_once "stddef.inc.php";

function getuse($id, $type, Db $db)
{
  $uses = 0;
  if ($type == "raw") {
    $uses = getrawuses($id, $db);
  }
  if ($type == "object") {
    $uses = getobjectuses($id);
  }

  return $uses;
}

function getrawuses($id, Db $db)
{
  $uses[0] = 0;
  $nuse = 0;

  if (is_numeric($id)) {
    $stm = $db->prepare("SELECT * FROM rawtypes WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $id);
    $stm->execute();
    foreach ($stm->fetchAll() as $item_info) {
      if ($item_info->nutrition) {
        $nuse++;
        $uses[$nuse] = "0:nutrition";
      }
      if ($item_info->strengthening) {
        $nuse++;
        $uses[$nuse] = "0:strengthening";
      }
      if ($item_info->energy) {
        $nuse++;
        $uses[$nuse] = "0:energy";
      }
      // now check objects
      $searchstr = $item_info->name . ">";

      $stm2 = $db->prepare("SELECT * FROM objecttypes WHERE build_requirements LIKE :requirements1 OR build_requirements LIKE :requirements2");
      $stm2->bindStr("requirements1", "%:$searchstr%");
      $stm2->bindStr("requirements2", "%,$searchstr%");
      $stm2->execute();

      foreach ($stm2->fetchAll() as $object_info) {
        $nuse++;
        $uses[$nuse] = "1:" . $object_info->unique_name;
      }
      // now check machines
      $stm3 = $db->prepare("SELECT * FROM machines WHERE requirements LIKE :requirements1 OR requirements LIKE :requirements2");
      $stm3->bindStr("requirements1","%:$searchstr%");
      $stm3->bindStr("requirements2", "%,$searchstr%");
      $stm3->execute();
      foreach ($stm3->fetchAll() as $machine_info) {
        $nuse++;
        $uses[$nuse] = "2:" . $machine_info->name;
      }
    }
  }
  $uses[0] = $nuse;
  return $uses;
}

function getobjectuses($id)
{
  $uses[0] = 0;
  return $uses;
}

function reportuse($use_string)
{
  $use = "no usage";
  $uses = preg_split("/:/", $use_string);
  switch ($uses[0]) {
    case 0: // intrinsic
      $use = $uses[1] . " -  intrinsic";
      break;
    case 1: // objects
      $use = $uses[1] . " -  object build requirement";
      break;
    case 2: // machine projects
      $use = $uses[1] . " -  machine based project requirement";
      break;
  }
  return $use;
}
