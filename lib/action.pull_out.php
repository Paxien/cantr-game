<?php

$fromLocId = HTTPContext::getInteger('from');
$return = $_REQUEST['return'];

try {
  $charLoc = Location::loadById($char->getLocation());
  $fromLoc = Location::loadById($fromLocId);
  
  if (($charLoc->getId() == 0) || ($fromLoc->getId() == 0)) {
    throw new InvalidArgumentException("");
  }
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if (!$charLoc->isAdjacentTo($fromLoc)) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

if ($char->isBusy()) {
  CError::throwRedirectTag("char.buildings", "error_cant_drag_already_busy");
}

$fromInnerToOuter = ($fromLoc->getRegion() == $charLoc->getId());

$innerLoc = ($fromInnerToOuter) ? $fromLoc : $charLoc;
$outerLoc = ($fromInnerToOuter) ? $charLoc : $fromLoc;

$shipTypes = Location::getShipTypeArray();

$checkInnerLock = true;
$checkOuterLock = in_array($fromLoc->getArea(), $shipTypes) && in_array($charLoc->getArea(), $shipTypes);

if ($checkInnerLock) {
  $lock = KeyLock::loadByLocationId($innerLoc->getId());
  if (!$lock->canAccess($char->getId())) {
    CError::throwRedirectTag("char.buildings", "error_enter_no_key");
  }
}
if ($checkOuterLock) {
  $lock = KeyLock::loadByLocationId($outerLoc->getId());
  if (!$lock->canAccess($char->getId())) {
    CError::throwRedirectTag("char.buildings", "error_enter_no_key");
  }
}


$db = Db::get();
$stm = $db->prepareWithIntList("SELECT o.id FROM objects o WHERE o.location = :locationId AND
  o.setting IN (:settings) AND o.weight > 0
  AND NOT EXISTS (SELECT d.id FROM dragging d WHERE d.victim = o.id
    AND d.victimtype = :victimType)
  ORDER BY RAND() LIMIT 1", [
  "settings" => [ObjectConstants::SETTING_PORTABLE, ObjectConstants::SETTING_QUANTITY, ObjectConstants::SETTING_HEAVY],
]);
$stm->bindInt("locationId", $fromLoc->getId());
$stm->bindInt("victimType", DraggingConstants::TYPE_OBJECT);
$objectToPull = $stm->executeScalar();

$stm = $db->prepare("SELECT c.id FROM chars c WHERE location = :locationId
    AND status = :active
  AND NOT EXISTS (SELECT d.id FROM dragging d WHERE d.victim = c.id
    AND d.victimtype = :victimType)
  ORDER BY RAND() LIMIT 1");
$stm->bindInt("locationId", $fromLoc->getId());
$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->bindInt("victimType", DraggingConstants::TYPE_OBJECT);
$charToPull = $stm->executeScalar();

if ($objectToPull !== null) {
  $maxWeight = Dragging::getMaxWeightPossibleToDrag($char->getId());
  $dragging = Dragging::newInstance(DraggingConstants::TYPE_OBJECT,
    $objectToPull, $char->getLocation(), $maxWeight);
} elseif ($charToPull !== null) {
  $dragging = Dragging::newInstance(DraggingConstants::TYPE_HUMAN,
    $charToPull, $char->getLocation());
    
  import_lib("func.genes.inc.php");
  alter_state($char->getId(), _GSS_TIREDNESS, _TIREDNESS_PER_DRAGGING);
} else {
  CError::throwRedirectTag("char.buildings", "error_nothing_to_pull");
}

$dragging->addDragger($char->getId());
$dragging->saveInDb();

Event::createPersonalEvent(343, "LOC=$from", $char->getId());
Event::createPublicEvent(344, "LOC=$from ACTOR=". $char->getId(), $char->getId(),
  Event::RANGE_SAME_LOCATION, array($char->getId()));

$draggingManager = new DraggingManager($char->getId());
$draggingManager->tryFinishingAll();

if (!isset($return)) {
  $return = "char.buildings";
}

redirect($return);
