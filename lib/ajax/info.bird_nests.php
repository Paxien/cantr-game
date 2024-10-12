<?php

$locationId = $char->getLocation();

$birdNestsIds = CObject::locatedIn($locationId)->hasProperty("MessengerBirdHome")->findIds();

$birdNests = Pipe::from($birdNestsIds)->map(function($birdNestId) {
  return [
    "id" => $birdNestId,
    "name" => TagBuilder::forObject($birdNestId, true)->build()->interpret(),
  ];
})->toArray();

echo json_encode([
  "birdNests" => $birdNests,
]);
