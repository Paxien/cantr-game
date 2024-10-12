<?php

$birdId = HTTPContext::getInteger('bird_id');
$toWhichHome = HTTPContext::getInteger('to_which_home');


try {
  $birdObject = CObject::loadById($birdId);
  $birdAnimalObject = DomesticatedAnimalObject::loadById($birdId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasWithinReach($birdObject)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

try {
  $messengerBird = new MessengerBird($birdObject, Db::get());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_cannot_be_messenger_bird");
}

function locationSuitableToDispatchMessengerBird(Character $char)
{
  if ($char->isTravelling()) {
    return true;
  }

  try {
    $charLocation = Location::loadById($char->getLocation());

    if ($charLocation->isOutside() || $charLocation->isSailingShip()) {
      return true;
    }

    if ($charLocation->isVehicle()) {
      $parentLocation = Location::loadById($charLocation->getRegion());
      return $parentLocation->isOutside() || $parentLocation->isSailingShip();
    }

    $messengerHomeInLocationExists = CObject::locatedIn($charLocation)->hasProperty("MessengerBirdHome")->exists();
    return $messengerHomeInLocationExists;
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("char.inventory", "error_too_far_away");
  }
}

if (!locationSuitableToDispatchMessengerBird($char)) {
  CError::throwRedirectTag("char.inventory", "error_need_access_to_air_or_nest");
}

try {
  $messengerBird->dispatchToHome($char, $toWhichHome);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_messenger_bird_invalid_home");
}

$messengerBird->saveInDb();
