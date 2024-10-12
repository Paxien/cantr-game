<?php

$db = Db::get();
$stm = $db->prepare("SELECT count(name) AS cnt FROM animal_types");
$animaltypes = $stm->executeScalar();

$stm = $db->prepare("SELECT count(name) AS cnt FROM texts WHERE name LIKE 'animal_%_s' AND language=1");
$animaltexts = $stm->executeScalar();

echo "There are currently $animaltypes animal_types and $animaltexts descriptions to go with them. <br><br>";

$teller = 0;
$stm = $db->query("SELECT name FROM texts WHERE name LIKE 'animal_%_s' AND language=1");
$animals = [];
foreach ($stm->fetchScalars() as $text) {
  $animals[] = $text;
}

$maxteller = count($animals);
$stm = $db->query("SELECT name FROM animal_types");
$date = date("Y-m-d");
foreach ($stm->fetchScalars() as $animalType) {

  $found = false;
  for ($teller = 0; $teller < $maxteller; $teller++) {
    $animalType = strtolower($animalType);

    $name = "animal_" . str_replace(" ", "_", $animalType) . "_s";
    $name2 = "animal_" . str_replace(" ", "_", $animalType) . "_p";
    $name3 = "animal_" . str_replace(" ", "_", $animalType) . "_o";
    if ($animals[$teller] == $name) {
      $found = true;
    }
  }

  if ($found !== true) {
    if ($animalType != "") {
      $newcontent = $animalType;
      $stm = $db->prepare("INSERT INTO texts (name, type, language, content, updated, translator) VALUES (:name, '1','1',:content, :date,'Animal magician')");
      $stm->bindStr("name", $name);
      $stm->bindStr("content", $newcontent);
      $stm->bindStr("date", $date);
      $stm->execute();
      echo "$animalType wasn't found and was added. ";
      echo "<br>";
      $endchar = substr($animalType, strlen($animalType) - 1, 1);

      $newcontent = $animalType . "s";
      if ($endchar == 's') {
        $newcontent = $animalType . "es";
      }
      $stm = $db->prepare("INSERT INTO texts (name, type, language, content, updated, translator) VALUES (:name, '1', '1', :content, :date, 'Animal magician')");
      $stm->bindStr("name", $name2);
      $stm->bindStr("content", $newcontent);
      $stm->bindStr("date", $date);
      $stm->execute();

      echo "plural version added as $newcontent. ";
      echo "<br>";

      $startchar = substr($animalType, 0, 1);
      $newcontent = "a " . $animalType;
      if (($startchar == 'a') or ($startchar == 'e') or ($startchar == 'i') or ($startchar == 'o') or ($startchar == 'u')) {
        $newcontent = "an " . $animalType;
      }
      $stm = $db->prepare("INSERT INTO texts (name, type, language, content, updated, translator) VALUES (:name, '1', '1', :content, :date, 'Animal magician')");
      $stm->bindStr("name", $name2);
      $stm->bindStr("content", $newcontent);
      $stm->bindStr("date", $date);
      $stm->execute();

      echo "version with indefinite article added as $newcontent. ";
      echo "<br>";
    }
  }
}
