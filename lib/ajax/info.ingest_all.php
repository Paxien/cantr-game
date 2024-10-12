<?php

$objectId = HTTPContext::getInteger('object');

try {
  $crockery = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_object_not_found");
}

if (!$char->hasWithinReach($crockery)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

$rawsInside = CObject::storedIn($crockery)->type(ObjectConstants::TYPE_RAW)->findAll();

/** @var $foods Resource[] */
$foods = Pipe::from($rawsInside)->map(function(CObject $raw) {
  return new Resource($raw);
})->filter(function($resource) {
  return $resource->isEdible();
})->toArray();

$eatingStrategy = new CrockeryEatingStrategy();

$effects = [
  StateConstants::HUNGER => [
    "name" => "char_desc_bar_hunger",
    "value" => 0,
    "sgn" => 1,
  ],
  StateConstants::HEALTH => [
    "name" => "char_desc_bar_damage",
    "value" => 0,
    "sgn" => -1,
  ],
  StateConstants::TIREDNESS => [
    "name" => "char_desc_bar_tiredness",
    "value" => 0,
    "sgn" => 1,
  ],
  StateConstants::DRUNKENNESS => [
    "name" => "char_desc_bar_drunkenness",
    "value" => 0,
    "sgn" => 1,
  ],
];
foreach ($foods as $food) {
  foreach ($food->getEfficienciesPerGram() as $stateType => $efficiency) {
    $effects[$stateType]["value"] += $efficiency * $food->getWeight() * $eatingStrategy->getCurrentCoefficient($stateType);
  }
}

$foodWeight = Pipe::from($foods)->map(function($food) {
  return $food->getWeight();
})->reduce(function($a, $b) {
  return $a + $b;
});

$effectTexts = Pipe::from($effects)->filter(function($effect) {
  return $effect["value"] != 0;
})->map(function($effect) {
  $finalEfficiency = number_format($effect["value"] / 100 * $effect["sgn"], 1);
  return $finalEfficiency . "% <CANTR REPLACE NAME=text_ingest_for_state> <CANTR REPLACE NAME=" . $effect["name"] . ">";
})->toArray();


$stomach = CharacterStomach::ofCharacter($char);
$results = "<CANTR REPLACE NAME=text_eat_from_container_effects WEIGHT=$foodWeight> " . implode(", ", $effectTexts);
$stomachText = "<CANTR REPLACE NAME=char_desc_fullness> ";
$stomachText .= $stomach->getStomachContentsWeight() . "/" . $stomach->getStomachMaxCapacity() . "g";

echo json_encode([
  "results" => TagBuilder::forText($results)->build()->interpret(),
  "stomach" => TagBuilder::forText($stomachText)->build()->interpret(),
]);
