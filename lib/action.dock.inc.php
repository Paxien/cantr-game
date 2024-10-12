<?php

$dock = HTTPContext::getInteger('dock');

try {
  $sailing = Sailing::loadByVesselId($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.description", "error_not_sailing");
}

try {
  $goal = Location::loadById($dock);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.description", "error_dock_not_close");
}

if (!in_array($goal->getId(), $sailing->getDockable())) {
  CError::throwRedirectTag("char.description", "error_dock_not_close");
}

if (!in_array($goal->getType(), array(LocationConstants::TYPE_OUTSIDE,
    LocationConstants::TYPE_BUILDING, LocationConstants::TYPE_SAILING_SHIP))) {
  CError::throwRedirectTag("char.description", "error_dock_not_close");
}

$sailing->startDockingTo($goal, $char);
$sailing->saveInDb();

redirect("char.events");
