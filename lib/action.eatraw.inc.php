<?php

/* ****** VALIDATE ****** */
$object_id = HTTPContext::getInteger('object_id');
$amount = HTTPContext::getInteger('amount');

$returnPage = "char.inventory";

try {
  $foodObject = CObject::loadById($object_id);
  $food = new Resource($foodObject);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag($returnPage, "error_too_far_away");
}

if ($food->getPerson() > 0) {
  $strategy = new StandardEatingStrategy();
  if (!$char->hasInInventory($foodObject)) {
    CError::throwRedirectTag($returnPage, "error_object_not_found");
  }
} elseif ($food->getAttached() > 0) {
  $strategy = new CrockeryEatingStrategy();

  $returnPage = "retrieve&object_id=". $food->getAttached();
  if (!$foodObject->isAccessibleInStorage($char, true, true)) {
    CError::throwRedirectTag($returnPage, "error_object_not_found");
  }
} else {
  CError::throwRedirectTag($returnPage, "error_object_not_found");
}

$stomach = CharacterStomach::ofCharacter($char);

$eatingEverything = $food->getWeight() == $amount;

try {
  $stomach->eat($food, $amount, $strategy);
} catch (InvalidObjectPropertyException $e) {
  CError::throwRedirectTag($returnPage, "error_cannot_eat_raw TYPE=". $food->getUniqueName());
} catch (ExistingLimitationException $e) {
  $gameDate = $e->getTimeLeft();
  CError::throwRedirectTag($returnPage, "error_not_eat_after_near_death DAYS=". $gameDate->getDay() .
      " HOURS=". $gameDate->getHour() ." MINS=". $gameDate->getMinute());
} catch (InvalidAmountException $e) {
  CError::throwRedirectTag($returnPage, "error_amount_invalid");
} catch (WeightCapacityExceededException $e) {
  CError::throwRedirectTag($returnPage, "error_full_stomach");
}

if ($eatingEverything) {
  Event::create(62, "FOOD=". $food->getUniqueName())->forCharacter($char)->show(); //you ate all of your x
} else {
  Event::create(63, "AMOUNT=$amount FOOD=". $food->getUniqueName())->forCharacter($char)->show();
}

$draggingManager = new DraggingManager($char->getId());
$draggingManager->tryFinishingAll();

if ($char->getState(StateConstants::DRUNKENNESS) >= CharacterConstants::PASSOUT_LIMIT) {
  Event::create(357, "")->forCharacter($char)->show();
  Event::create(358, "ACTOR=". $char->getId())->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
}

$statistic = new Statistic("eat", Db::get());
$statistic->update($food->getUniqueName(), 0, $amount);

redirect($returnPage);
