<?php

$objectId = HTTPContext::getInteger('object_id'); 

try {
  $crockery = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasWithinReach($crockery)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$crockery->hasAccessToAction("ingest_all")) {
  CError::throwRedirectTag("char.inventory", "error_action_impossible");
}

$raws = CObject::storedIn($crockery)->type(ObjectConstants::TYPE_RAW)->findAll();


$foods = Pipe::from($raws)->map(function($raw) {
  return new Resource($raw); // all resources
})->filter(function($resource) {
  return $resource->isEdible(); // which are edible
})->toArray();

$stomach = CharacterStomach::ofCharacter($char);

$isStomachFull = false;
$strategy = new CrockeryEatingStrategy();
$eatenFoods = [];
foreach ($foods as $food) {
  try {
    $stomach->eat($food, $food->getWeight(), $strategy);
    $eatenFoods[] = $food;
  
  } catch (InvalidObjectPropertyException $e) {
    CError::throwRedirectTag("char.inventory", "error_cannot_eat_raw TYPE=". $food->getUniqueName());
  
  } catch (ExistingLimitationException $e) {
    $gameDate = $e->getTimeLeft();
    CError::throwRedirectTag("char.inventory", "error_not_eat_after_near_death DAYS=". $gameDate->getDay() .
        " HOURS=". $gameDate->getHour() ." MINS=". $gameDate->getMinute());
  
  } catch (InvalidAmountException $e) {
    CError::throwRedirectTag("char.inventory", "error_amount_invalid");

  } catch (WeightCapacityExceededException $e) {
    $isStomachFull = true;
    break;
  }
}

$foodNames = Pipe::from($eatenFoods)->map(function ($food) {
  return "<CANTR REPLACE NAME=". TagUtil::getRawTagByName($food->getName()) .">";
})->toArray();

$foodNamesStr = urlencode(implode(", ", $foodNames));
// eating event for yourself
if (strlen($foodNamesStr) > 0) {
  Event::create(356, "FOODS=". $foodNamesStr ." STORE=". $crockery->getUniqueName())->forCharacter($char)->show();
}

if ($isStomachFull) {
  CError::throwRedirectTag("char.inventory", "error_full_stomach");
}

$draggingManager = new DraggingManager($char->getId());
$draggingManager->tryFinishingAll();

if ($char->getState(StateConstants::DRUNKENNESS) >= CharacterConstants::PASSOUT_LIMIT) {
  Event::create(357, "")->forCharacter($char)->show();
  Event::create(358, "ACTOR=". $char->getId())->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
}

$statistic = new Statistic("eat_all", Db::get());
foreach ($eatenFoods as $food) {
  $statistic->update($food->getUniqueName(), 0, $food->getWeight());
}

redirect("char.inventory");
