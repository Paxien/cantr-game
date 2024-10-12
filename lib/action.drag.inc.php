<?php

$object_d = HTTPContext::getInteger('object_id');
$ocharacter = HTTPContext::getInteger('ocharacter');
$goal = HTTPContext::getInteger('goal');
$amount = HTTPContext::getInteger('amount');

if (!(Validation::isPositiveInt($object_d) XOR Validation::isPositiveInt($ocharacter))) {
  CError::throwRedirectTag("char.events", "error_cant_drag_data_incorrect");
}

if ($object_d > 0) {
  $victim = CObject::loadById($object_d);
  $type = DraggingConstants::TYPE_OBJECT;
} elseif ($ocharacter > 0) {
  $victim = Character::loadById($ocharacter);
  $type = DraggingConstants::TYPE_HUMAN;
}

$isVictimHuman = ($type == DraggingConstants::TYPE_HUMAN);

if (!$isVictimHuman && ($victim->getSetting() == ObjectConstants::SETTING_FIXED)) {
  CError::throwRedirectTag("char.objects", "error_drag_not_draggable");
}

$areValidLocations = ($victim->getLocation() > 0) &&
  (($goal > 0) || ($isVictimHuman && ($goal == DraggingConstants::GOAL_FROM_PROJECT)));

if (!$areValidLocations) {
  CError::throwRedirectTag("char.events", "error_drag_goal_too_far_away");
}

if ($char->getLocation() != $victim->getLocation()) {
  CError::throwRedirectTag("char.events", "error_drag_victim_too_far_away");
}

if (!$isVictimHuman && ($victim->getSetting() == ObjectConstants::SETTING_QUANTITY)) {
  if (!Validation::isPositiveInt($amount) || ($amount > $victim->getAmount())) {
    CError::throwRedirectTag("char.objects", "error_drag_incorrect_amount");
  } else {
    $weight = $amount * $victim->getUnitWeight();
  }
} else { // human or non-quantity object -> value doesn't matter
  $weight = 0;
}

try {
  $alreadyExistingDragging = Dragging::loadByVictim($type, $victim->getId());
  if ($alreadyExistingDragging != null) {

    $draggers = $alreadyExistingDragging->getDraggers();
    foreach ($draggers as &$dragger) {
      $tag = new Tag("<CANTR CHARNAME ID=$dragger>", false);
      $dragger = $tag->interpret();
    }

    $draggersString = urlencode(implode(", ", $draggers));

    if ($isVictimHuman) {
      $tag = new Tag("<CANTR CHARNAME ID=" . $victim->getId() . ">", false);
      $drag = $tag->interpret();
      CError::throwRedirect("char.events", "<CANTR REPLACE NAME=error_drag_person_already_drag DRAGGED=" . urlencode($drag) . " DRAGGER=$draggersString>");
    } else {
      CError::throwRedirect("char.events", "<CANTR REPLACE NAME=error_drag_object_already_drag DRAGGER=$draggersString>");
    }
  }
} catch (InvalidArgumentException $e) {
}

if ($char->isBusy()) {
  CError::throwRedirectTag("char.events", "error_cant_drag_already_busy");
}

try {
  $dragging = Dragging::newInstance($type, $victim->getId(), $goal, $weight);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_cant_drag_data_incorrect");
}

if ($isVictimHuman) {
  import_lib("func.genes.inc.php");
  alter_state($char->getId(), _GSS_TIREDNESS, _TIREDNESS_PER_DRAGGING);
}

$dragging->addDragger($char->getId());
$dragging->saveInDb();

$draggingManager = new DraggingManager($char->getId());
$draggingManager->tryFinishingAll();

$redirectTo = ($isVictimHuman) ? "char.events" : "char.objects";
redirect($redirectTo);
