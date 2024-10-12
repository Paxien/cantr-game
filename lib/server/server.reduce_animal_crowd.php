<?php

$page = "server.reduce_animal_crowd";
include "server.header.inc.php";

$db = Db::get();

$stm = $db->query("SELECT id, name, max_in_location FROM animal_types at
  WHERE NOT EXISTS (SELECT * FROM animal_domesticated_types adt WHERE adt.of_animal_type = at.id)");

$animalTypes = $stm->fetchAll();
foreach ($animalTypes as $animalType) {
  $stm = $db->prepare("SELECT COUNT(*) FROM animals WHERE type = :type AND number > :maxInLocation");
  $stm->bindInt("type", $animalType->id);
  $stm->bindInt("maxInLocation", $animalType->max_in_location);
  $overcrowdedPacks = $stm->executeScalar();

  $packsToReduce = ceil($overcrowdedPacks * 0.05);

  if ($packsToReduce > 0) {
    echo "$animalType->name reducing $packsToReduce of $overcrowdedPacks\n";

    $stm = $db->prepare("UPDATE animals SET number = number - 1 WHERE type = :type AND number > :maxInLocation ORDER BY RAND() LIMIT :packsToReduce");
    $stm->bindInt("type", $animalType->id);
    $stm->bindInt("maxInLocation", $animalType->max_in_location);
    $stm->bindInt("packsToReduce", $packsToReduce);
    $stm->execute();
  }
}
