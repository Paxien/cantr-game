<?php

$birdId = HTTPContext::getInteger('bird_id');
$whichHome = HTTPContext::getInteger('which_home');
$birdNestId = HTTPContext::getInteger('bird_nest_id');


try {
  $birdObject = CObject::loadById($birdId);
  $birdAnimalObject = DomesticatedAnimalObject::loadById($birdId);
  $messengerBird = new MessengerBird($birdObject, Db::get());
  $birdNest = CObject::loadById($birdNestId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasWithinReach($birdObject)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasWithinReach($birdNest)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if ($birdNest->getSetting() != ObjectConstants::SETTING_FIXED) {
  CError::throwRedirectTag("char.inventory", "error_bird_nest_must_be_fixed");
}

if (!$birdObject->hasProperty("MessengerBird")) {
  CError::throwRedirectTag("char.inventory", "error_cannot_be_messenger_bird");
}

if (!$birdNest->hasProperty("MessengerBirdHome")) {
  CError::throwRedirectTag("char.inventory", "error_invalid_bird_home");
}

try {
  $birdNestLocation = Location::loadById($birdNest->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

$nestRootLocation = $birdNestLocation->getRoot();
if ($nestRootLocation->getType() != LocationConstants::TYPE_OUTSIDE) { // nest's location's root must be a central area
  CError::throwRedirectTag("char.inventory", "error_bird_nest_must_be_fixed");
}

if (!$birdAnimalObject->isLoyalTo($char)) {
  CError::throwRedirectTag("char.inventory", "error_animal_not_loyal_to_you");
}

if ($whichHome == MessengerBird::FIRST_MESSENGER_HOME) {
  $messengerBird->setFirstHome($birdNest);
  $messengerBird->setFirstHomeRoot($nestRootLocation);
} elseif ($whichHome == MessengerBird::SECOND_MESSENGER_HOME) {
  $messengerBird->setSecondHome($birdNest);
  $messengerBird->setSecondHomeRoot($nestRootLocation);
} else {
  CError::throwRedirectTag("char.inventory", "error_invalid_bird_home");
}

Event::create(384, "WHICH_HOME=$whichHome " .
  "HOME_NEST_ID=" . $birdNest->getId() . " BIRD_ID=" . $birdObject->getId())
  ->forCharacter($char)->show();
Event::create(385, "ACTOR=" . $char->getId() . " WHICH_HOME=$whichHome " .
  "HOME_NEST_ID=" . $birdNest->getId() . " BIRD_ID=" . $birdObject->getId())
  ->nearCharacter($char)->except($char)->show();

$messengerBird->saveInDb();
