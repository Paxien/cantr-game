<?php

if (!$char->isNearDeath()) {
  CError::throwRedirectTag("char.events", "error_suicide_not_near_death");
}

// maybe this char is already dead (somebody clicked a few times)
if (!$char->isAlive()) {
  CError::throwRedirectTag("player", "error_play_dead_char");
}

/*
 * KILL KILL KILL!!!
 */

$char->dieCharacter($char->getDeathCause(), $char->getDeathWeapon(), false);
$char->saveInDb();

$db = Db::get();
$stm = $db->prepare("DELETE FROM char_near_death WHERE char_id = :charId LIMIT 1");
$stm->bindInt("charId", $char->getId());
$stm->execute();

Event::create(302, "")->forCharacter($char)->show();
Event::create(303, "VICTIM=$character GENDER=$death_info->sex")
  ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();

redirect("player");
