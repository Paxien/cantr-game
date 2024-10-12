<?php

include_once("func.genes.inc.php");
require_once("func.expireobject.inc.php");

// SANITIZE INPUT
$character = HTTPContext::getInteger('character');
$to = HTTPContext::getInteger('to');
$force = HTTPContext::getInteger('force');
$tmp_tool = HTTPContext::getString('tool', null);
if ($tmp_tool && $tmp_tool != 'manual') {
  $tmp_tool = HTTPContext::getInteger('tool');
}
$tool = $tmp_tool;

$db = Db::get();

try {
  $victim = Character::loadById($to);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_hit_too_far");
}

if (!$victim->isAlive()) {
  CError::throwRedirectTag("char.events", "error_target_dead_char");
}

if ($victim->isNearDeath()) {
  CError::throwRedirectTag("char.events", "error_victim_in_near_death_state");
}

/*** CHECK WHETHER THE VICTIM IS CLOSEBY ENOUGH ***/

if (!$char->isNearTo($victim, true)) {
  CError::throwRedirectTag("char.events", "error_hit_too_far");
}

/*** CHECK WHETHER PLAYERS IS NOT TOO NEW ***/
$playerInfo = Request::getInstance()->getPlayer();
$registerDay = $playerInfo->getRegisterDay();

if (GameDate::NOW()->getDay() < $registerDay + 4) {
  CError::throwRedirectTag("char.events", "error_hit_too_new");
}

/*** CHECK WHETHER AMOUNT OF FORCE IS ACCEPTABLE ***/
if (!Validation::inRange($force, [0, 10])) {
  CError::throwRedirect("char.events", "An illegal amount of force has been entered");
}

/*** CHECK WHETHER VIOLENCE HAS OCCURED EARLIER ***/

if (Limitations::getLims($char->getId(), Limitations::TYPE_VIOLENCE_ATTACK_CHAR, $victim->getId()) > 0) {
  $ctimeleft = Limitations::getTimeLeft($char->getId(), Limitations::TYPE_VIOLENCE_ATTACK_CHAR, $to);
  $toNextAttack = GameDate::fromTimestamp($ctimeleft)->getArray();
  $minToNextAttack = $toNextAttack['minute'];
  $hourToNextAttack = $toNextAttack['hour'] + $toNextAttack['day'] * GameDateConstants::HOURS_PER_DAY;
  CError::throwRedirect("char.events", "<CANTR REPLACE NAME=error_hit_only_once MINUTES=$minToNextAttack HOURS=$hourToNextAttack>");
}

/*** IN CASE NO PROBLEMS: CONTINUE ***/

if (true) {

  if ($tool) {

    if ($victim->getId() == $char->getId()) { // suicide
      $min_state = _SCALESIZE_GSS / 3.0;
      $fraction = -1;
    } else {
      $min_state = 0;
      $fraction = random_percent();
    }
    
    $drunkenness = $char->getState(_GSS_DRUNKENNESS) / _SCALESIZE_GSS;
    $chance = (_VIOLENCE_HIT_CHANCE / 100) - ($drunkenness * 0.4);
      
    $successful = ($fraction < $chance);
    
    $skillRatio = 60/100; //preset to the fighting skill relevance of fighting bare hand
    $strengthRatio = 40/100; //

    /*** RECORDING THE VIOLENCE ***/

    Limitations::addLim($char->getId(), Limitations::TYPE_VIOLENCE_ATTACK_CHAR,
      GameDate::fromDate(1, 0, 0, 0)->getTimestamp(), $victim->getId());
    
    $toolnr = $tool;

    if ($tool == 'manual') { $toolnr = 0; }

    $now = GameDate::NOW();

    $stm = $db->prepare("INSERT INTO violence (turn, turnpart, minute, second, perpetrator, victim, type)
      VALUES (:day, :hour, :minute, :second, :perpetrator, :victim, :type)");
    $stm->bindInt("day", $now->getDay());
    $stm->bindInt("hour", $now->getHour());
    $stm->bindInt("minute", $now->getMinute());
    $stm->bindInt("second", $now->getSecond());
    $stm->bindInt("perpetrator", $char->getId());
    $stm->bindInt("victim", $to);
    $stm->bindInt("type", $toolnr);
    $stm->execute();
    /*** CALCULATING THE AMOUNT OF FORCE ***/

    $fightingSkillAttacker = max($char->getState(_GSS_FIGHTING), $min_state);
    $strengthSkillAttacker = max($char->getState(_GSS_STRENGTH), $min_state);
    $health = max($char->getState(_GSS_HEALTH), $min_state) / _SCALESIZE_GSS;
    $tiredness = min($char->getState(_GSS_TIREDNESS), (_SCALESIZE_GSS - $min_state)) / _SCALESIZE_GSS;
    $drunkenness = $char->getState(_GSS_DRUNKENNESS) / _SCALESIZE_GSS;
    
    try {
      if ($tool == "manual") {
        $weaponData = new Fists();
      } else {
        $weaponData = new Weapon(CObject::loadById($tool));
      }
      
      $weaponData->checkUseBy($char);

      $skillRatio = $weaponData->getSkillRelevance();
      $strengthRatio = $weaponData->getStrengthRelevance();
      if ($successful) {
        $down = floor(
          ((($fightingSkillAttacker / _SCALESIZE_GSS) * $skillRatio) +
            (($strengthSkillAttacker / _SCALESIZE_GSS) * $strengthRatio)) *
          (1 - $tiredness) * (1 + $drunkenness * $weaponData->getDrunkennessInfluence()) * $health *
          ($force / 10) * $weaponData->getHit() * 150
        );
        
        $down = floor(normal($down, .1));
      } else {
        $down = 0;
      }
      
      $weaponData->applyHitDeterioration($force);

      $weapon = urlencode($weaponData->getNameTag());
    } catch (InvalidArgumentException $e) {
      CError::throwRedirectTag("char.events", "error_not_your_weapon");
    } catch (TooFarAwayException $e) {
      CError::throwRedirectTag("char.events", "error_not_your_weapon");
    } catch (AnimalNotLoyalException $e) {
      CError::throwRedirectTag("char.events", "error_attack_animal_not_loyal");
    }

    /*** CHECKING AVAILABLE PROTECTION ***/

    $currentHealth = $victim->getState(_GSS_HEALTH);

    $stm = $db->prepare("SELECT o.id, o.type, ot.unique_name, ot.rules FROM objects o
        INNER JOIN objecttypes ot ON ot.id = o.type AND ot.rules LIKE '%shield:%'
      WHERE person = :charId");
    $stm->bindInt("charId", $victim->getId());
    $stm->execute();

    $getProtectionValue = function($shield) {
      return Parser::rulesToArray($shield->rules)['shield'];
    };
    
    $shields = $stm->fetchAll();
    $initial = array_shift($shields);
    
    $bestShield = array_reduce($shields, function($s1, $s2) use ($getProtectionValue) {
      return ($getProtectionValue($s1) >= $getProtectionValue($s2)) ? $s1 : $s2;
    }, $initial);
    
    $up = 0;
    if ($bestShield !== null) {
      $protection = $getProtectionValue($bestShield);
      $prot_id = $bestShield->id;
      $shieldRules = Parser::rulesToArray($bestShield->rules);
      if (array_key_exists('weapon_skill_relevance', $shieldRules)) {
        $shieldSkillRelevance = ($shieldRules['weapon_skill_relevance'] / 100);
      } else {
        $shieldSkillRelevance = 0.5;
      }
      
      $prot_desc = urlencode("<CANTR REPLACE NAME=item_". $bestShield->unique_name ."_o>");
      
      $percent = random_percent();
      $chance = normal_percent(_SHIELD_BLOCK_CHANCE, _VIOLENCE_DEV);

      $fightingSkillDefender = $victim->getState(_GSS_FIGHTING);
      $strengthSkillDefender = $victim->getState(_GSS_STRENGTH);
      $tiredness = $victim->getState(_GSS_TIREDNESS) / _SCALESIZE_GSS;
      $drunkenness = $victim->getState(_GSS_DRUNKENNESS) / _SCALESIZE_GSS;
      
      if ($percent < $chance) {
        $up = floor(
            (($fightingSkillDefender * $shieldSkillRelevance / _SCALESIZE_GSS) +
            ($strengthSkillDefender * (1 - $shieldSkillRelevance) / _SCALESIZE_GSS)) *
          (1 - $tiredness) * (1 + $drunkenness * 0.25) * ($force / 10) * $protection * 150);
        $up = floor( normal( $up, .1 ) );
        
        $decay_factor = $force / 80;
        
        if ($up <= $down) {
          $down -= $up;
        } else {
          $decay_factor *= ($down / $up);
          $up = $down;
          $down = 0;
        }
        usage_decay_object($prot_id, $decay_factor);
      } else {
        $up = 0;
      }
    }

    /*** THE ACTUAL RESULT ***/

    if (true) {

      /*** THE PERPETRATOR GAINS ON SKILLS ***/
      $rawGain = $force * (1 - ($char->getState(_GSS_TIREDNESS) / _SCALESIZE_GSS));
      $skillGain = ceil($rawGain * $skillRatio);
      $strengthGain = ceil($rawGain * $strengthRatio);
      alter_state($character, _GSS_FIGHTING, max($skillGain, 1));
      alter_state($character, _GSS_STRENGTH, max($strengthGain, 1));
      alter_state($character, _GSS_TIREDNESS, $force * 150);

      /*** NOW CHECK EFFECTS ON VICTIM ***/

      $skill_actor = urlencode("<CANTR REPLACE NAME=skill_adjective_" . get_skill_adjective($fightingSkillAttacker) . ">");
      if ($bestShield !== null) {
        $skill_victim = urlencode("<CANTR REPLACE NAME=skill_adjective_" . get_skill_adjective($fightingSkillDefender) . ">");
      }

      // Record hit
      $stm = $db->prepare("INSERT INTO kills (aggressor, victim, animal, kills, damage) VALUES (:aggressor, :victim, 0, 0, :damage1)
                   ON DUPLICATE KEY UPDATE damage = damage + :damage2");
      $stm->bindInt("aggressor", $char->getId());
      $stm->bindInt("victim", $to);
      $stm->bindInt("damage1", $down);
      $stm->bindInt("damage2", $down);
      $stm->execute();

      if (($currentHealth - $down) <= 0) {
        
        // Record kill
        $stm = $db->prepare("UPDATE kills SET kills = 1 WHERE aggressor = :aggressor AND victim = :victim AND animal = 0");
        $stm->bindInt("aggressor", $char->getId());
        $stm->bindInt("victim", $to);
        $stm->execute();
      
        /*** OOOPS ... THE CHARACTER GOES INTO NDS ***/
        Event::create(50, "ACTOR=$character WEAPON=$weapon SKILL_ACTOR=$skill_actor")->forCharacter($victim)->show();

        $victim->intoNearDeath(CharacterConstants::CHAR_DEATH_VIOLENCE, $weaponData->getType());
        $victim->saveInDb();
        
        Event::create(49, "VICTIM=$to WEAPON=$weapon SKILL_ACTOR=$skill_actor")->forCharacter($char)->show();
        Event::create(51, "ACTOR=$character VICTIM=$to WEAPON=$weapon SKILL_ACTOR=$skill_actor")->
            nearCharacter($char)->andAdjacentLocations()->except([$char, $victim])->show();
        
      } else {
        
        /*** THE CHARACTER GETS HURT ***/

        alter_state($to, _GSS_HEALTH, -1 * $down);
        $damage_done = floor($down / 100);
        $damage_avoided = floor($up / 100);

        if ($tool == 'manual' && $force == 0) {
          Event::create(53, "ACTOR=$character")->forCharacter($victim)->show();
          Event::create(52, "VICTIM=$to")->forCharacter($char)->show();
          Event::create(54, "ACTOR=$character VICTIM=$to")->nearCharacter($char)->
            andAdjacentLocations()->except([$char, $victim])->show();
        } else {
          if ($successful) {
            if ($bestShield !== null) {
              if ($damage_avoided || $damage_done == 0) { // shield is ineffecient only if there was damage done
                Event::create(58, "ACTOR=$character WEAPON=$weapon DAMAGE=$damage_done SAVED=$damage_avoided PROTECTION=$prot_desc SKILL_ACTOR=$skill_actor SKILL_VICTIM=$skill_victim")->forCharacter($victim)->show();
              } else {
                Event::create(59, "ACTOR=$character WEAPON=$weapon DAMAGE=$damage_done SKILL_ACTOR=$skill_actor")->forCharacter($victim)->show();
              }
            } else {
              Event::create(57, "ACTOR=$character WEAPON=$weapon DAMAGE=$damage_done SKILL_ACTOR=$skill_actor")->forCharacter($victim)->show();
            }
            Event::create(60, "VICTIM=$to WEAPON=$weapon DAMAGE=$damage_done SKILL_ACTOR=$skill_actor")->forCharacter($char)->show();
          } else {
            Event::create(56, "ACTOR=$character WEAPON=$weapon")->forCharacter($victim)->show();
            Event::create(55, "VICTIM=$to WEAPON=$weapon")->forCharacter($char)->show();
          }
          Event::create(61, "ACTOR=$character VICTIM=$to WEAPON=$weapon SKILL_ACTOR=$skill_actor")->
              nearCharacter($char)->andAdjacentLocations()->except([$char, $victim])->show();
        }
      }
    }

    // victim char can be weaker and now able to be dragged
    $draggingManager = new DraggingManager($victim->getId());
    $draggingManager->tryFinishingAll();
    
    redirect("char.events");
    
  } else {
    $stm = $db->prepare("SELECT o.id FROM objects o
        INNER JOIN objecttypes ot ON ot.id = o.type AND ot.rules LIKE '%hit%'
      WHERE o.person = :charId GROUP BY o.type");
    $stm->bindInt("charId", $char->getId());
    $stm->execute();

    $weaponArray = array();
    foreach ($stm->fetchScalars() as $weaponId) {
      try {
        $weaponData = new Weapon(CObject::loadById($weaponId));
        $weaponData->checkUseBy($char);
        
        $weaponArray[] = array(
          'hit'  => $weaponData->getHit(),
          'id'   => $weaponId,
          'name' => $weaponData->getNameTag()
        );
      } catch (Exception $e) {} // nothing serious, just skip a weapon
    }
    
    usort($weaponArray, function($a, $b) {
      return $a['hit'] < $b['hit'];
    });
    
    $weaponArray[] = array(
      'hit' => _HIT_PERCENT_DOWN,
      'id' => "manual",
      'name' => "<CANTR REPLACE NAME=weapon_bare_fist>",
    );
    
    $smarty = new CantrSmarty;
    
    $smarty->assign ("victim", $victim->getId());
    $smarty->assign ("weapons", $weaponArray);
    
    $smarty->displayLang ("action.hit.tpl", $lang_abr); 
  }
}
