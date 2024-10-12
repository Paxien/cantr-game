<?php

try {
  $location = Location::loadById($char->getLocation()); 
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("", "error_too_far_away");
}

function sortByName($a, $b) {
  return strcmp($a["name"], $b["name"]);
}

$locations = $location->getSublocations();

$locData = array();
foreach ($locations as $locId) {
  $tag = new Tag("<CANTR LOCNAME ID=". $locId .">", false);
  $tag = new Tag($tag->interpret(), false);
  $locName = $tag->interpret();
  $tag = new Tag("<CANTR LOCDESC ID=". $locId .">", false);
  $tag = new Tag($tag->interpret(), false);
  $locDesc = $tag->interpret();
  $locData[] = array("id" => $locId, "name" => $locName, "type" => $locDesc);
}

usort($locData, "sortByName");

$outsideData = array();
try {
  $outsideId = $location->getRegion();
  if (($location->getType() == LocationConstants::TYPE_OUTSIDE) || ($outsideId == 0)) {
    throw new InvalidArgumentException("");
  }
  
  $existingLoc = Location::loadById($outsideId); // if fails then location doesn't exist
  $tag = new Tag("<CANTR LOCNAME ID=". $outsideId .">", false);
  $tag = new Tag($tag->interpret(), false);
  $locName = $tag->interpret();
  $tag = new Tag("<CANTR LOCDESC ID=". $outsideId .">", false);
  $tag = new Tag($tag->interpret(), false);
  $locDesc = $tag->interpret();
  $outsideData[] = array("id" => $outsideId, "name" => $locName, "type" => $locDesc);
} catch (InvalidArgumentException $e) {}

echo json_encode(
  array(
    "sublocations" => $locData,
    "outside" => $outsideData
  )
);
