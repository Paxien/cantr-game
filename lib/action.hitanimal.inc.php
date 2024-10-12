<?php
require_once "func.expireobject.inc.php";

// SANITIZE INPUT
$tool  = HTTPContext::getString('tool', null);
if ($tool != 'manual') {
  $tool = intval($tool);
}
$force = HTTPContext::getInteger('force', -1);

$accepted = true;
$db = Db::get();

$pack_test = $_POST['pack'];

if (!$pack_test){ // if no pack selected
  CError::throwRedirectTag("char", "error_hunt_target_not_there");
}

$huntingStats = new Statistic("hunting", Db::get());

foreach ($pack_test as $pack_id) {
  $animal_pack = AnimalPack::loadFromDb(intval($pack_id));

  /*** CHECK WHETHER VIOLENCE HAS OCCURED EARLIER BY THIS
     CHARACTER AGAINST THE ANIMALS ON THIS LOCATION ***/
  if ($animal_pack->ok) {
    if (!$char->isInSameLocationAs($animal_pack)) {
      CError::throwRedirect("char.description", "The animal pack you have choosen is not in your location");
    } else if ($force < 0 || $force > 10) { /*** CHECK WHETHER AMOUNT OF FORCE IS ACCEPTABLE ***/
      CError::throwRedirect("char.description", "An illegal amount of force has been entered");
    } else {
      $stm = $db->prepare("SELECT COUNT(*) FROM hunting WHERE location = :locationId AND animal_type = :animalType AND perpetrator = :perpeterator");
      $stm->bindInt("locationId", $animal_pack->location);
      $stm->bindInt("animalType", $animal_pack->type);
      $stm->bindInt("perpeterator", $char->getId());
      $count_previous_violence = $stm->executeScalar();
      if ($count_previous_violence) {
        CError::throwRedirectTag("char.description","error_hunt_only_once");
      }
    }
  } else {
    CError::throwRedirectTag("char.description","error_hunt_target_not_there");
  }

    /*** RECORDING THE VIOLENCE ***/

    $toolnr = $tool;

    if ($tool == 'manual') { 
      $toolnr = 0; 
    }

    $stm = $db->prepare("INSERT INTO hunting (perpetrator, turn, turnpart, location, animal_type)
      VALUES (:perpetrator, :day, :hour, :locationId, :animalType)");
  $stm->bindInt("perpetrator", $char->getId());
    $stm->bindInt("day", GameDate::NOW()->getDay());
    $stm->bindInt("hour", GameDate::NOW()->getHour());
    $stm->bindInt("locationId", $animal_pack->location);
    $stm->bindInt("animalType", $animal_pack->type);
    $stm->execute();

    /*** CALCULATING THE AMOUNT OF FORCE THE WEAPON WILL GIVE THE ANIMAL ***/

    $hunting_skill = read_state($character,_GSS_HUNTING);

    if ($tool != 'manual') {
      $weapon = $tool;
      try {
        $weaponData = new Weapon(CObject::loadById($tool));
        $weaponData->checkUseBy($char);
        $hit_damage = $weaponData->getAnimalHit();
      } catch (InvalidArgumentException $e) {
        CError::throwRedirectTag("char.events", "error_not_your_weapon");
      } catch (AnimalNotLoyalException $e) {
        CError::throwRedirectTag("char.events", "error_attack_animal_not_loyal");
      }
    } else { // use bare hands
      $hit_damage = _HIT_PERCENT_DOWN;
      $weapon = 15; # special for use in events
    }
    
        $down = floor(read_state($character,_GSS_HEALTH) / _SCALESIZE_GSS
        * ($hunting_skill / 16667
           + read_state($character,_GSS_STRENGTH) / 25000)
        * (1 - read_state($character,_GSS_TIREDNESS) / _SCALESIZE_GSS)
        * $force / 10
        * $hit_damage
        * 1.5);

      $damage = $animal_pack->hurt_animal ($char->getId(), $down);

      $animal_pack->name = urlencode(str_replace(" ","_",$animal_pack->name));

      if ($tool != 'manual') {
        $decay_factor= $force/80;
        usage_decay_object($tool,$decay_factor);
      }

      /*** THE PERPETRATOR GAINS ON SKILLS ***/

      alter_state($character, _GSS_HUNTING, round(max($force / 2, 2)));
      alter_state($character, _GSS_STRENGTH, round(max($force / 5, 1)));
      alter_state($character, _GSS_TIREDNESS, $force * 5);

      /*** NOW CHECK EFFECTS ON VICTIM ***/

      $skill = urlencode("<CANTR REPLACE NAME=skill_adjective_" . get_skill_adjective($hunting_skill) . ">");

       // Record hit
      $stm = $db->prepare("INSERT INTO kills (aggressor, victim, animal, kills, damage) VALUES (:aggressor, 0, :animalType, 0, :damage1)
                   ON DUPLICATE KEY UPDATE damage = damage + :damage2");
      $stm->bindInt("aggressor", $char->getId());
      $stm->bindInt("animalType", $animal_pack->type);
      $stm->bindInt("damage1", $down);
      $stm->bindInt("damage2", $down);
      $stm->execute();
      if ($damage == -1) {

        // Record kill
        $stm = $db->prepare("UPDATE kills SET kills = kills + 1 WHERE aggressor = :aggressor AND victim = 0 AND animal = :animalType");
        $stm->bindInt("aggressor", $char->getId());
        $stm->bindInt("animalType", $animal_pack->type);
        $stm->execute();
      
        /*** AN ANIMAL DIED ***/
        Event::create(166, "ACTOR={$char->getId()} ANIMAL=$animal_pack->name WEAPON=$weapon SKILL=$skill")
          ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
        Event::create(167, "ANIMAL=$animal_pack->name WEAPON=$weapon SKILL=$skill")->forCharacter($char)->show();

        $huntingStats->update($animal_pack->name, $animal_pack->location);

      } else {

        /*** THE WEAKEST ANIMAL GETS HURT ***/
        if ($tool == 'manual' && $force == 0) {  # poking animals

          $animal_pack->name = urlencode("<CANTR REPLACE NAME=animal_".str_replace(" ","_",$animal_pack->name)."_o>");
          Event::create(31, "ACTOR={$char->getId()} ANIMAL=$animal_pack->name")
            ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
          Event::create(32, "ANIMAL=$animal_pack->name")->forCharacter($char)->show();

          $interaction_type = 2;
        } else {  # hurting animals

          Event::create(168, "ACTOR={$char->getId()} ANIMAL=$animal_pack->name WEAPON=$weapon SKILL=$skill")
            ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
          Event::create(169, "ANIMAL=$animal_pack->name WEAPON=$weapon DAMAGE=$damage SKILL=$skill")
            ->forCharacter($char)->show();
          $interaction_type = 1;
        }

        // Record interaction only when animal survives - no point to store
        // interaction of dead animals
        $stm = $db->prepare("INSERT INTO animal_interaction (perpetrator_type, perpetrator_id, victim_type, victim_id, interaction_type)
          VALUES (1, :perpetrator, 2, :victim, :interactionType)");
        $stm->bindInt("perpetrator", $char->getId());
        $stm->bindInt("victim", $animal_pack->type);
        $stm->bindInt("interactionType", $interaction_type);
        $stm->execute();
      }
}


redirect("char");
