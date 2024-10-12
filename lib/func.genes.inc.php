<?php

function read_gen($character, $gen)
{
  $db = Db::get();
  $stm = $db->prepare("SELECT value FROM genes WHERE person = :charId AND type = :gene LIMIT 1");
  $stm->bindInt("charId", $character);
  $stm->bindInt("gene", $gen);
  $gen_value = $stm->executeScalar();
  if ($gen_value != null) {
    return $gen_value;
  }
  // Gen is not in the database, so create randomly
  $value = generate_random_gen($gen);

  $stm = $db->prepare("INSERT INTO genes (person,type,value) VALUES (:charId, :type, :value)");
  $stm->bindInt("charId", $character);
  $stm->bindInt("type", $gen);
  $stm->bindInt("value", $value);
  $stm->execute();

  return $value;
}

function get_skill_adjective($value)
{
  if ($value < 2800) {

    $skill = "awkwardly";
  } elseif ($value < 4600) {

    $skill = "novicely";
  } elseif ($value < 6400) {

    $skill = "efficiently";
  } elseif ($value < 8200) {

    $skill = "skillfully";
  } else {

    $skill = "expertly";
  }

  return $skill;
}

function get_strength_adjective($value)
{

  if ($value < 2800) {

    $skill = "much_weaker";
  } elseif ($value < 4600) {

    $skill = "weaker";
  } elseif ($value < 6400) {

    $skill = "average";
  } elseif ($value < 8200) {

    $skill = "stronger";
  } else {

    $skill = "much_stronger";
  }

  return $skill;
}


function read_state($character, $type)
{
  $db = Db::get();
  $stm = $db->prepare("SELECT value FROM states WHERE person = :charId AND type = :type LIMIT 1");
  $stm->bindInt("charId", $character);
  $stm->bindInt("type", $type);
  $state_value = $stm->executeScalar();
  if ($state_value != null) {
    return $state_value;
  }

  $value = read_gen($character, $type);
  $stm = $db->prepare("INSERT INTO states (person, type, value) VALUES (:charId, :type, :value)");
  $stm->bindInt("charId", $character);
  $stm->bindInt("type", $type);
  $stm->bindInt("value", $value);
  $stm->execute();
  return $value;
}

function alter_state($character, $type, $change)
{

  $oldvalue = read_state($character, $type);

  $newvalue = min(10000, max(0, $oldvalue + $change));
  $db = Db::get();
  $stm = $db->prepare("UPDATE states SET value = :value WHERE person = :charId AND type = :type LIMIT 1");
  $stm->bindInt("value", $newvalue);
  $stm->bindInt("charId", $character);
  $stm->bindInt("type", $type);
  $stm->execute();
}

function set_state($character, $type, $value)
{
  $newvalue = min(10000, max(0, $value));
  $db = Db::get();
  $stm = $db->prepare("UPDATE states SET value = :value WHERE person = :charId AND type = :type LIMIT 1");
  $stm->bindInt("value", $newvalue);
  $stm->bindInt("charId", $character);
  $stm->bindInt("type", $type);
  $stm->execute();
}

function generate_random_gen($gen)
{
  srand((float)microtime() * 1000000);

  $db = Db::get();
  $stm = $db->prepare("SELECT rand_minimum, rand_maximum FROM state_types WHERE id = :gene LIMIT 1");
  $stm->bindInt("gene", $gen);
  $stm->execute();
  $state_type_info = $stm->fetchObject();
  if ($state_type_info->rand_maximum > $state_type_info->rand_minimum) {
    $value = rand() % ($state_type_info->rand_maximum - $state_type_info->rand_minimum) + $state_type_info->rand_minimum;
  } else {
    $value = $state_type_info->rand_minimum;
  }

  return $value;
}
