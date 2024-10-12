<?php

$page = "server.food";
include_once "server.header.inc.php";
include_once "func.diseases.inc.php";
include_once "func.genes.inc.php";

/* ************ FOOD CONSUMPTION ****************** */

function eatResults($amount, $rawstrength, $type)
{
  if ($type=="strength") {
    $factor=_EAT_PERCENT_BACK_HEALING;
    $percdir=1;
  }

  if ($type=="energy") {
    $factor=_EAT_PERCENT_BACK_TIREDNESS;
    $percdir=-1; //tiredness goes down
  }

  $perc_up = $amount * $rawstrength / 100 * $factor;
  $perc_up = floor ($perc_up) * $percdir;

	return $perc_up;
}

$consumeStats = new Statistic("consumed", Db::get());
$consumeStomachStats = new Statistic("consumed_stomach", Db::get());

print "Food consumption: ";

$charLocks = Limitations::getAllLims(Limitations::TYPE_LOCK_CHAR);
$charLocks = Pipe::from($charLocks)->map(function($v) {
	return $v->id;
})->toArray();


$db = Db::get();

$stm = $db->prepare("SELECT id FROM players WHERE status = :locked");
$stm->bindInt("locked", PlayerConstants::LOCKED);
$stm->execute();
$playerLocks = $stm->fetchScalars();


$stm = $db->prepare("SELECT id, disease, specifics, player
  FROM chars LEFT JOIN diseases ON chars.id = diseases.person WHERE status = :active");
$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->execute();
foreach ($stm->fetchAll() as $char_info) {
		// locked characters are excluded from the eating system
		if (in_array($char_info->player, $playerLocks) || in_array($char_info->id, $charLocks)) {
			echo "Omitting locked character $char_info->id of $char_info->player<br>\n";
			continue;
		}

		//delete food that has been instantly digested
		$stm = $db->prepare("DELETE FROM stomach WHERE person = :charId AND food = :food");
		$stm->bindInt("charId", $char_info->id);
		$stm->bindInt("food", 294);
		$stm->execute();

    $food = 0;

    if ($char_info->disease == 2) {
      $eatperday = _EATPERDAY * $char_info->specifics;
    } else {
      $eatperday = _EATPERDAY;
    }

    $stm = $db->prepare(
      "SELECT s.uid, s.weight, rt.nutrition, rt.strengthening, rt.energy, rt.name
      FROM stomach s, rawtypes rt
      WHERE s.person = :charId AND s.food = rt.id AND rt.nutrition > 0
      ORDER BY s.eaten_date"
    );
    $stm->bindInt("charId", $char_info->id);
    $stm->execute();
    foreach ($stm->fetchAll() as $object_info) {

      $needed = $eatperday - $food;

      $contribution = ($object_info->nutrition / 100) * $object_info->weight;

      if ($food < $eatperday) {

        if ($contribution == $needed) {
          $consumeStomachStats->update($object_info->name, 0, $object_info->weight);
          $food = $eatperday;
          $stm = $db->prepare("DELETE FROM stomach WHERE uid = :uid");
          $stm->bindInt("uid", $object_info->uid);
          $stm->execute();
					echo "Character $char_info->id digested $object_info->weight g of $object_info->name.\n";
					if ($object_info->strengthening){
						$perc_up = eatResults($object_info->weight, $object_info->strengthening, "strength");
						alter_state($char_info->id, _GSS_HEALTH, $perc_up);
					}
					if ($object_info->energy) {
						$perc_up = eatResults($object_info->weight, $object_info->energy, "energy");
						alter_state($char_info->id, _GSS_ENERGY, $perc_up);
					}
        } elseif ($contribution < $needed) {
          $consumeStomachStats->update($object_info->name, 0, $object_info->weight);
          $food += $contribution;
          $stm = $db->prepare("DELETE FROM stomach WHERE uid = :uid");
          $stm->bindInt("uid", $object_info->uid);
          $stm->execute();
					echo "Character $char_info->id digested $object_info->weight g of $object_info->name.\n";
					if ($object_info->strengthening) {
						$perc_up = eatResults($object_info->weight, $object_info->strengthening, "strength");
						alter_state($char_info->id, _GSS_HEALTH, $perc_up);
					}
					if ($object_info->energy) {
						$perc_up=eatResults($object_info->weight, $object_info->energy, "energy");
						alter_state($char_info->id, _GSS_ENERGY, $perc_up);
					}
        } elseif ($contribution > $needed) {
          $food = $eatperday;
          $weight = floor(100 * ($needed / $object_info->nutrition));
          $consumeStomachStats->update($object_info->name, 0, $weight);

          if ($weight>=1) {
            $stm = $db->prepare("UPDATE stomach SET weight = weight - :weight WHERE uid = :uid");
            $stm->bindInt("weight", $weight);
            $stm->bindInt("uid", $object_info->uid);
            $stm->execute();
          }
					echo "Character $char_info->id digested $weight g of $object_info->name.\n";
          if ($object_info->strengthening) {
						$perc_up = eatResults($weight, $object_info->strengthening, "strength");
						alter_state($char_info->id, _GSS_HEALTH, $perc_up);
					}
					if ($object_info->energy) {
						$perc_up = eatResults($weight, $object_info->energy, "energy");
						alter_state($char_info->id, _GSS_ENERGY, $perc_up);
					}

        }
      }
    }

    //Apparently if the necessary daily amount has been eaten manually, it leaves a 0.something hunger that it tries to satisfy
		//and for some reason it would get a wrong eating message
    if ($eatperday-$food>=1)
    {
      $stm = $db->prepare(
        "SELECT o.id, o.type, o.typeid, o.weight, rt.nutrition, rt.strengthening>0 AS healing, rt.energy>0 as energy, rt.name
	FROM objects o, rawtypes rt
	WHERE o.person = :charId AND o.type=2 AND o.typeid=rt.id AND rt.nutrition>0
	ORDER BY 6, 7, 5"
	);
      $stm->bindInt("charId", $char_info->id);
      $stm->execute();

      foreach ($stm->fetchAll() as $object_info) {
	$needed = $eatperday - $food;
	// print "Needs: $needed ";

	$foodname = str_replace (" ", "_", $object_info->name);
	$contribution = ($object_info->nutrition / 100) * $object_info->weight;

        if ($food < $eatperday) {
    if ($contribution == $needed) {
      $consumeStats->update($object_info->name, 0, $object_info->weight);
      $food = $eatperday;
      import_lib("func.expireobject.inc.php");
      expire_object($object_info->id);
			echo "Character $char_info->id ate $object_info->weight g of $object_info->name.\n";
			if ($object_info->healing) {
				$perc_up = eatResults($object_info->weight, $object_info->healing, "strength");
				alter_state($char_info->id, _GSS_HEALTH, $perc_up);
			}
			if ($object_info->energy) {
				$perc_up = eatResults($object_info->weight, $object_info->energy, "energy");
				alter_state($char_info->id, _GSS_ENERGY, $perc_up);
			}

	    $id_actor = Event::create(62, "FOOD=$foodname");

	    $weight = $object_info->weight;
	  } elseif ($contribution < $needed) {
      $consumeStats->update($object_info->name, 0, $object_info->weight);
      $food += $contribution;
	    import_lib("func.expireobject.inc.php");
      expire_object($object_info->id);
			echo "Character $char_info->id ate $object_info->weight g of $object_info->name.\n";
			if ($object_info->healing) {
				$perc_up = eatResults($object_info->weight, $object_info->healing, "strength");
				alter_state($char_info->id, _GSS_HEALTH, $perc_up);
			}
			if ($object_info->energy) {
				$perc_up = eatResults($object_info->weight, $object_info->energy, "energy");
				alter_state($char_info->id, _GSS_ENERGY, $perc_up);
			}

	    $id_actor = Event::create(62, "FOOD=$foodname");
	    $id_actor->forCharacter($char_info->id)->show();

	    $weight = $object_info->weight;
	  } elseif ($contribution > $needed) {
      $food = $eatperday;
	    $weight = floor(100 * ($needed / $object_info->nutrition));
      $consumeStats->update($object_info->name, 0, $weight);

        if ($weight>=1) {
            $stm = $db->prepare("UPDATE objects SET weight = weight - :weight WHERE id = :id");
            $stm->bindInt("weight", $weight);
            $stm->bindInt("id", $object_info->id);
            $stm->execute();
					  echo "Character $char_info->id ate $weight g of $object_info->name.\n";
						if ($object_info->healing) {
							$perc_up = eatResults($weight, $object_info->healing, "strength");
							alter_state($char_info->id, _GSS_HEALTH, $perc_up);
						}
						if ($object_info->energy) {
							$perc_up = eatResults($weight, $object_info->energy, "energy");
							alter_state($char_info->id, _GSS_ENERGY, $perc_up);
						}
	      $id_actor = Event::create(63, "AMOUNT=$weight FOOD=$foodname");
	    }//this eliminates the you eat 0 grams messages
	  }

	  disease_eating_raw($char_info->id, $object_info->typeid, $weight, $db);
	}
      }

      $percentup = $food / $eatperday * _EAT_PERCENT_BACK - _EAT_PERCENT_BACK;
      $percentup *= _SCALESIZE_GSS / 100;

      if ($percentup == 0) { $percentup += _SCALESIZE_GSS / 30; }

      alter_state($char_info->id, _GSS_HUNGER, rand_round(0 - $percentup));
      $hunger = false;

      if ($percentup < 0) {
				$hunger = true;
				$id_actor = Event::create(64, "");
      }
      $id_actor->forCharacter($char_info->id)->highlight($hunger)->show();
    }

		//autoheal based on stomach contents
		$stm = $db->prepare("SELECT value as health FROM states WHERE person = :charId AND type = :type LIMIT 1");
    $stm->bindInt("charId", $char_info->id);
    $stm->bindInt("type", StateConstants::HEALTH);
    $stm->execute();
		if ($stm->rowCount() > 0) {
			$health_info = $stm->fetchObject();

			//Healing of damage gained after manual overeating of healing food
			if ($health_info->health < _SCALESIZE_GSS)
			{
			$str_needed = _SCALESIZE_GSS-$health_info->health;
			echo "Char $char_info->id needs ". $str_needed/100 ." % health.\n";
			$stm = $db->prepare(
				"SELECT s.uid, s.weight, rt.strengthening as healing, rt.energy, rt.name
				FROM stomach s, rawtypes rt
				WHERE s.person = :charId AND s.food=rt.id AND rt.strengthening > 0
				ORDER BY s.eaten_date");
			$stm->bindInt("charId", $char_info->id);
			$stm->execute();
			$healed = 0;

			foreach ($stm->fetchAll() as $object_info) {
				$str_needed = $str_needed-$healed;

				if ($str_needed>=1)
				{
					//Less or exactly as much as needed
					$perc_up = eatResults($object_info->weight, $object_info->healing, "strength");

					if ($perc_up<=$str_needed) {
						alter_state($char_info->id, _GSS_HEALTH, $perc_up);
						if ($object_info->energy) {
							$perc_up2 = eatResults($object_info->weight, $object_info->energy, "energy");
							alter_state($char_info->id, _GSS_ENERGY, $perc_up2);
						}
						$stm = $db->prepare("DELETE FROM stomach WHERE uid = :uid");
						$stm->bindInt("uid", $object_info->uid);
						$stm->execute();
						$healed += $perc_up;
						echo "Character $char_info->id digested all ($object_info->weight g) of $object_info->name to heal ". $perc_up/100 ." % damage.\n";
					}
					else
					{
						//There's more than needed
						$multiplier = $str_needed/$perc_up;
						alter_state($char_info->id, _GSS_HEALTH, floor($perc_up*$multiplier));
						if ($object_info->energy) {
							$perc_up2 = eatResults($object_info->weight, $object_info->energy, "energy");
							alter_state($char_info->id, _GSS_ENERGY, floor($perc_up2*$multiplier));
						}
						$weight = floor($object_info->weight*$multiplier);
						if ($weight>0) {
              $stm = $db->prepare("UPDATE stomach SET weight = weight - :weight WHERE uid = :uid");
              $stm->bindInt("weight", $weight);
              $stm->bindInt("uid", $object_info->uid);
              $stm->execute();
            }
						$healed = $perc_up * $multiplier;
						echo "Character $char_info->id digested $weight g of $object_info->name to heal ". $perc_up*$multiplier/100 ." % damage.\n";
					}
				}//end if healing left to do
			}//end loop of foods
		}//end if damaged
	}//end if num_rows


	//energy restoration if needed
		$stm = $db->prepare("SELECT value as tiredness FROM states WHERE person = :charId AND type = :type LIMIT 1");
		$stm->bindInt("charId", $char_info->id);
		$stm->bindInt("type", StateConstants::TIREDNESS);
		$stm->execute();
		if ($stm->rowCount() > 0)
		{
			$health_info = $stm->fetchObject();

			//Healing of damage gained after manual overeating of healing food
			if ($health_info->tiredness>0)
			{
			$energy_needed = $health_info->tiredness;
			echo "Char $char_info->id needs ". $energy_needed/100 ." % energy.\n";
			$stm = $db->prepare("SELECT s.uid, s.weight, rt.energy, rt.name
				FROM stomach s, rawtypes rt
				WHERE s.person = :charId AND s.food=rt.id AND rt.energy>0
				ORDER BY s.eaten_date");
			$stm->bindInt("charId", $char_info->id);
			$stm->execute();

			$healed = 0;

			foreach ($stm->fetchAll() as $object_info) {
				$energy_needed = $energy_needed-$healed;

				if ($energy_needed>=1)
				{
					//Less or exactly as much as needed
					$perc_up = eatResults($object_info->weight, $object_info->energy, "energy");

					if (($perc_up*-1)<=$energy_needed) {
						alter_state($char_info->id, _GSS_TIREDNESS, $perc_up);

            $stm = $db->prepare("DELETE FROM stomach WHERE uid = :uid");
            $stm->bindInt("uid", $object_info->uid);
            $stm->execute();

						$healed += $perc_up*-1;
						echo "Character $char_info->id digested all ($object_info->weight g) of $object_info->name to regain ". $perc_up*-1/100 ." % energy.\n";
					}
					else
					{
						//There's more than needed
						$multiplier = $energy_needed/$perc_up*-1;
						alter_state($char_info->id, _GSS_TIREDNESS, floor($perc_up*$multiplier));

						$weight = floor($object_info->weight*$multiplier);
            $stm = $db->prepare("UPDATE stomach SET weight = weight - :weight WHERE uid = :uid");
            $stm->bindInt("weight", $weight);
            $stm->bindInt("uid", $object_info->uid);
            $stm->execute();
						$healed = $perc_up * $multiplier;
						echo "Character $char_info->id digested $weight g of $object_info->name to regain ". $perc_up*$multiplier/100 ." % energy.\n";
					}
				}//end if energy restoration left to do
			}//end loop of foods
		}//end if tired
	}//end if num_rows
}
print "done.\n";


include "server/server.footer.inc.php";
