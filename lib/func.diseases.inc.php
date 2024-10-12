<?php

function disease_split_specifics($specifics_string) {

  $split_1 = preg_split("/;/", $specifics_string);
  for ($i = 0; $i < count($split_1); $i++) {

    $split_2 = preg_split("/:/", $split_1[$i]);

    $specifics["$split_2[0]"] = $split_2[1];
  }

  return $specifics;
}

function disease_join_specifics($specifics) {

  $specifics_string = "";

  while (list ($key, $val) = each ($specifics)) {

    if ($specifics_string != "") { $specifics_string .= ";"; }

    $specifics_string .= "$key:$val";
  }

  return $specifics_string;
}

function disease_eating_raw($character, $rawtype, $weight, Db $db) {

  $character = intval($character);
  $stm = $db->prepare("SELECT disease,specifics FROM diseases WHERE person = :charId");
  $stm->bindInt("charId", $character);
  $stm->execute();
  foreach ($stm->fetchAll() as $disease_info) {
    $specifics = disease_split_specifics($disease_info->specifics);

    switch ($disease_info->disease) {

    case 1:
      // ******** DISEASE NUMBER 1 ********

      // This disease reduces when eating herbal mixture A
      // code removed because it was never used
      break;
    }
  }
}
