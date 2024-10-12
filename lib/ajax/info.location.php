<?php

$locationId = HTTPContext::getInteger('location_id');

try {
  $location = Location::loadById($locationId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("", "error_too_far_away");
}

$charLocation = null;
if ($char->getLocation() != 0) {
  $charLocation = Location::loadById($char->getLocation());
}

$detailedData = [
  "visibleObjects" => [],
  "signs" => [],
];

$charIsInLocation = $charLocation != null;

$isInLocation = $charIsInLocation && $location->getId() == $charLocation->getId();
$isAdjacent = $charIsInLocation && $location->isAdjacentTo($charLocation);
$isClose = $isAdjacent || $isInLocation;

$locationWithOutgoingConnections = $charLocation;
if ($charIsInLocation
  && $charLocation->getType() == LocationConstants::TYPE_VEHICLE
  && $charLocation->getRegion() > 0
) {
  $parentLocation = Location::loadById($charLocation->getRegion());
  if ($parentLocation->getType() == LocationConstants::TYPE_OUTSIDE) {
    $locationWithOutgoingConnections = $parentLocation;
  }
}

$connectionLeadingToLocation = null;
if ($charIsInLocation) {
  $connectionLeadingToLocation = Pipe::from(Connection::outgoingConnections($locationWithOutgoingConnections))
    ->filter(function(Connection $connection) use ($location, $locationWithOutgoingConnections) {
      return $connection->isIncidentTo($locationWithOutgoingConnections->getId()) &&
        $connection->getOppositeLocation($locationWithOutgoingConnections->getId()) == $location->getId();
    })->first();
}

if ($isClose) {
  $locationNaming = new LocationNaming($location, Db::get());
  $hasSignAlteringTool = $locationNaming->hasSignAlteringTool($char);
  $locationsWithAlterableSigns = [LocationConstants::TYPE_BUILDING, LocationConstants::TYPE_VEHICLE];


  $allNames = $locationNaming->getAllNames($char);
  array_shift($allNames); // the first name is already visible as "name"

  $detailedData["visibleObjects"] = Pipe::from($location->getVisibleObjects($isClose))->map(function($objectId) {
    return TagBuilder::forText("<CANTR OBJNAME ID=" . $objectId . ">")->build();
  })->map(function(tag $tag) {
    return $tag->interpret();
  })->toArray();
  $detailedData["signs"] = $allNames;

} elseif ($connectionLeadingToLocation != null) {
  $detailedData["connectionToLocation"] = $connectionLeadingToLocation->getId();
}


if ($isClose || $connectionLeadingToLocation) {
  $detailedData += [
    "typeName" => TagBuilder::forLocDesc($location)->twice()->build()->interpret(),
  ];
}

if ($isAdjacent) {
  $doorToOutside = $locationId == $charLocation->getRegion();
  $innerLocation = $doorToOutside ? $charLocation : $location;
  $detailedData += [
    "canKnock" => $innerLocation->getType() == LocationConstants::TYPE_BUILDING,
    "canPointAt" => !$doorToOutside,
  ];
}

if ($isInLocation) {
  $customDescription = Descriptions::getDescription($location->getId(), Descriptions::TYPE_BUILDING);
  $customDescription = str_replace("\n", "<br />", $customDescription);

  $detailedData += [
    "customDescription" => $customDescription,
  ];
}

echo json_encode([
    "locationId" => $location->getId(),
    "name" => TagBuilder::forLocation($location->getId())->allowHtml(false)->twice()->build()->interpret(),
    "isAdjacent" => $isAdjacent,
  ] + $detailedData);
