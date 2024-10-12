<?php

$page = "server.disease";
include "server.header.inc.php";
include_once "func.genes.inc.php";

srand ((float) microtime() * 1000000);

function infect_disease_2(Db $db) {
  $stm = $db->query("SELECT chars.id AS cid,COUNT(*) AS n FROM chars,objects WHERE chars.location!=0
    AND chars.status = 1 AND objects.location=chars.location AND objects.type=7 GROUP BY chars.id");
  foreach ($stm->fetchAll() as $char_info) {
    if (rand(0,100) < $char_info->n) {

      $stm = $db->prepare("SELECT COUNT(*) FROM diseases WHERE person = :charId AND disease=2");
      $stm->bindInt("charId", $char_info->cid);
      $cnt = $stm->executeScalar();
      if ($cnt == 0) {
        $stm = $db->prepare("INSERT INTO diseases (person, disease, date, specifics)
          VALUES (:charId, 2, :day, '6')");
        $stm->bindInt("charId", $char_info->cid);
        $stm->bindInt("day", GameDate::NOW()->getDay());
        $stm->execute();
      }
    }
  }
}

$frequency_table[1] = 0;
$frequency_table[2] = 0;

$db = Db::get();
$gameDate = GameDate::NOW();

$stm = $db->query("SELECT person,disease,date,specifics FROM diseases");
foreach ($stm->fetchAll() as $disease_info) {

  echo "Processing: char $disease_info->person, disease $disease_info->disease with specifics $disease_info->specifics -- ";

  $frequency_table[$disease_info->disease]++;

  switch ($disease_info->disease) {

  case 1:
    // ******** DISEASE NUMBER 1 ********

    // This disease causes sneezing. The sneezing causes infection to others in the area.
    // The disease also increase tiredness.

    $chance_of_sneezing = 0; //35;
    $chance_of_infection = 0; // 35; // Only when sneezing
    $chance_of_increase = 0; //70;
    $size_of_increase = 500;

    $stm = $db->prepare("SELECT location FROM chars WHERE id = :charId LIMIT 1");
    $stm->bindInt("charId", $disease_info->person);
    $charLocation = $stm->executeScalar();

    $char_loc = new char_location($disease_info->person);

    $chars_near = $char_loc->chars_near(_PEOPLE_NEAR);

    $dice = rand() % 100;

    if ($dice < $chance_of_sneezing) {

      // SNEEZE

      echo " SNEEZES";

      Event::create(92, "ACTOR=$disease_info->person")
        ->nearCharacter($disease_info->person)->andAdjacentLocations()->except($disease_info->person)->show();

      Event::create(93, "")->forCharacter($disease_info->person)->show();

      $dice = rand() % 100;

      if ($dice < $chance_of_infection) {

	echo " INFECTS";

	$select = rand() % count($chars_near);

	$stm = $db->prepare("SELECT count(*) FROM diseases WHERE person = :charId AND disease=1 LIMIT 1");
	$stm->bindInt("charId", $chars_near[$select]);
	$count = $stm->executeScalar();

	if (!$count) {

	  $stm = $db->prepare("INSERT INTO diseases (person,disease,date,infector,specifics)
      VALUES (:charId,1, :day, :infectorId,'')");
	  $stm->bindInt("charId", $chars_near[$select]);
	  $stm->bindInt("day", $gameDate->getDay());
	  $stm->bindInt("infectorId", $disease_info->person);
	  $stm->execute();
	}
      }
    }

    $dice = rand() % 100;

    if ($dice < $chance_of_increase) {

      echo " LOOSES ENERGY";

      alter_state($disease_info->person, _GSS_TIREDNESS, $size_of_increase);
    }
    break;

  case 2:
    // ******** DISEASE NUMBER 2 ********

    // This disease causes one to need much more food - processing thus
    // mostly in server.food.inc.php. The disease decays over naturally over
    // time, but slower for older people.

    // The rate is based on the following idea:
    // When the multiplier for the food consumption gets below 1, the disease
    // ends.
    // The starting multiplier will be 6.
    // For a 20 year old character, the disease ends after about 5 days.
    // For an 80 year old character or older , the disease ends after about 41 days.

    // Determine age
    $stm = $db->prepare("SELECT register,spawning_age FROM chars WHERE id= :charId LIMIT 1");
    $stm->bindInt("charId", $disease_info->person);
    $stm->execute();
    $char_info = $stm->fetchObject();
    $age = (($gameDate->getDay() - $char_info->register)/20.0 ) + $char_info->spawning_age;

    // Determine new multiplier for food consumption
    $rate = min(0.957,$age * .00433333 + .61);
    $specifics = min(6,$disease_info->specifics * $rate);

    $stm = $db->prepare("UPDATE diseases SET specifics=:specifics WHERE person= :charId AND disease = :disease LIMIT 1");
    $stm->bindStr("specifics", (string)$specifics);
    $stm->bindInt("charId", $disease_info->person);
    $stm->bindInt("disease", $disease_info->disease);
    $stm->execute();
    // Sometimes create an events
    if ($specifics > 2 && rand(0,100) < 30) {
      Event::create(250, "")->forCharacter($disease_info->person)->show();
    }
  }

  echo "\n<br>";
}

$stm = $db->prepare("DELETE FROM diseases WHERE date < :day AND disease=1");
$stm->bindInt("day", $gameDate->getDay() - 9);
$stm->execute();
$db->query("DELETE FROM diseases WHERE specifics<1 AND disease=2");

infect_disease_2($db);

include "server/server.footer.inc.php";
