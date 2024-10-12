<?php

try {
  $sailing = Sailing::loadByVesselId($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.description", "error_alter_not_on_vessel");
}

if ($sailing->isDocking()) {
  $turnsToDock = $sailing->getTurnsToDock();
  $dockingPercent = floor(100 - 100 * pow($turnsToDock / ($turnsToDock + 1), 2));
  $tag = "page_desc_docking DOCKING=" . $dockingPercent;
} else {
  $areaType = $sailing->getShip()->getAreaType();
  if ($sailing->getSpeed() > 0) {
    $tag = "page_desc_sailing TYPE=$areaType DIRECTION={$sailing->getDirection()} SPEED={$sailing->getSpeedPercent()}";
  } else {
    $tag = "page_desc_floating TYPE=$areaType";
  }
}

$sailingText = TagBuilder::forTag($tag)->build()->interpret();

$hours = 0;
if ($sailing->getSailingStopTimestamp() > 0) {
  $dateFromTimestamp = GameDate::fromTimestamp($sailing->getSailingStopTimestamp());
  $hours = $dateFromTimestamp->minus(GameDate::NOW())->getTimestamp()
    / (GameDateConstants::SECS_PER_MIN * GameDateConstants::MINS_PER_HOUR);
}

echo json_encode([
  "docking" => $sailing->isDocking(),
  "wantedDirection" => $sailing->getDirection(),
  "resultantDirection" => $sailing->getResultantDirection(),
  "speedPercent" => $sailing->getSpeedPercent(),
  "hours" => ceil($hours),
  "text" => $sailingText,
]);
