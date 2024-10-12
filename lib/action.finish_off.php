<?php

$to = HTTPContext::getInteger('to');

$db = Db::get();

try {
  $victim = Character::loadById($to);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_victim_doesnt_exist");
}

// Is victim alive? Maybe somebody already finished him off
if (!$victim->isAlive()) {
  CError::throwRedirectTag("char.events", "error_target_dead_char");
}

if (!$char->isNearTo($victim, true)) {
  CError::throwRedirectTag("char.events", "error_hit_too_far");
}

$existingHealingProject = Project::locatedIn($victim->getLocation())
  ->type(ProjectConstants::TYPE_HEAL_NEAR_DEATH)
  ->subtype($victim->getId())->find();
// if victim is currently being healed (somebody works on the project in the same location)
if ($existingHealingProject) {
  $stm = $db->prepare("SELECT COUNT(*) FROM chars WHERE project = :projectId");
  $stm->bindInt("projectId", $existingHealingProject->getId());
  $activeHealer = $stm->executeScalar();
  if ($activeHealer > 0) {
    CError::throwRedirectTag("char.events", "error_finish_off_when_being_healed");
  }
}

// perpetrator holds ANY weapon (pillows highly encouraged)
$stm = $db->prepare("SELECT o.id FROM objects o
  INNER JOIN objecttypes ot ON ot.id = o.type WHERE o.person = :charId
    AND rules LIKE '%hit:%' LIMIT 1");
$stm->bindInt("charId", $char->getId());
$weaponId = $stm->executeScalar();

if (!$weaponId) {
  CError::throwRedirectTag("char.events", "error_finish_off_not_hold_weapon");
}

// is near death
if (!$victim->isNearDeath()) {
  CError::throwRedirectTag("char.events", "error_target_not_near_death");
}


// actor finished you off - before victim's death to have it visible in email
Event::create(308, "ACTOR=$character")->forCharacter($victim)->show();

// the reason of NDS is already stored there
$victim->dieCharacter($victim->getDeathCause(), $victim->getDeathWeapon(), false);
$victim->saveInDb();

// delete near death state from db
$stm = $db->prepare("DELETE FROM char_near_death WHERE char_id = :charId LIMIT 1");
$stm->bindInt("charId", $to);
$stm->execute();

// other events
Event::create(307, "VICTIM=$to")->forCharacter($char)->show();
Event::create(309, "ACTOR=$character VICTIM=$to")->nearCharacter($char)->andAdjacentLocations()
  ->except([$char, $victim])->show();

redirect("char.events");
