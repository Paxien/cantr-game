<?php

$objectId = HTTPContext::getInteger('object');

try {
  $object = CObject::loadById($objectId);
  $rawObject = new Resource($object);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("", "error_too_far_away");
}

$accessStorageInInventory = false;
$strategy = new StandardEatingStrategy();
if ($object->getAttached() > 0) {
  $storage = CObject::loadById($object->getAttached());
  $storageLock = KeyLock::loadByObjectId($storage->getId());
  $accessStorageInInventory = $char->hasInInventory($storage) && $storageLock->canAccess($char->getId());
  $strategy = new CrockeryEatingStrategy();
}

if (!($char->hasInInventory($object) || $accessStorageInInventory)) {
  CError::throwRedirectTag("", "error_too_far_away");
}

$stomach = CharacterStomach::ofCharacter($char);

try {
  $stomach->checkEating($rawObject);
} catch (InvalidObjectPropertyException $e) {
  CError::throwRedirectTag("", "error_cannot_eat_raw TYPE=" . $rawObject->getUniqueName());
} catch (ExistingLimitationException $e) {
  $gameDate = $e->getTimeLeft();
  CError::throwRedirectTag("", "error_not_eat_after_near_death DAYS=" . $gameDate->getDay() .
    " HOURS=" . $gameDate->getHour() . " MINS=" . $gameDate->getMinute());
}


$tags = [
  StateConstants::HUNGER => "char_desc_bar_hunger",
  StateConstants::HEALTH => "char_desc_bar_damage",
  StateConstants::TIREDNESS => "char_desc_bar_tiredness",
  StateConstants::DRUNKENNESS => "char_desc_bar_drunkenness",
];

$results = [];
foreach ($rawObject->getEfficienciesPerGram() as $paramType => $efficiency) {
  $results[] = [
    "name" => TagBuilder::forText("<CANTR REPLACE NAME=" . $tags[$paramType] . ">")->observedBy($char)->build()->interpret(),
    "per100g" => $efficiency * $strategy->getCurrentCoefficient($paramType),
    "toMaximize" => $stomach->neededForMax($paramType, $strategy->getCurrentCoefficient($paramType) * $rawObject->getEatingEffects()[$paramType]),
  ];
}

$stomachText = "<CANTR REPLACE NAME=char_desc_fullness> ";
$stomachText .= $stomach->getStomachContentsWeight() . "/" . $stomach->getStomachMaxCapacity() . "g";
$stomachText = TagBuilder::forText($stomachText)->observedBy($char)->build()->interpret();

echo json_encode([
  "stomach" => $stomachText,
  "results" => $results,
]);