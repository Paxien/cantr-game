<?php

// SANITIZE INPUT
$ocharacter = HTTPContext::getInteger('ocharacter');

/*** CHECK WHETHER PERPETRATOR IS ALREADY DRAGGING OR WORKING ON A PROJECT ***/

$logger = Logger::getLogger(basename(__FILE__));

$db = Db::get();
$db->beginTransaction();

if ($char->isBusy()) {
  CError::throwRedirectTag("char.events", "error_cant_help_already_busy");
}

try {
  $dragging = Dragging::loadByDragger($ocharacter);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_nothing_to_help");
}

try {
  if ($dragging->getVictimType() == DraggingConstants::TYPE_HUMAN) {
    $victim = Character::loadById($dragging->getVictim());
  } else {
    $victim = CObject::loadById($dragging->getVictim());
  }
} catch (InvalidArgumentException $e) {
  $logger->error("impossible to load victim data (id: ". $dragging->getVictim(). ", character: $character)", $e);
  CError::throwRedirectTag("char.events", "error_cant_drag_data_incorrect");
}

if (!in_array($char->getLocation(), array($victim->getLocation(), $dragging->getGoal()))) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if ($dragging->getVictimType() == DraggingConstants::TYPE_HUMAN) {
  import_lib("func.genes.inc.php");
  alter_state($character, _GSS_TIREDNESS, _TIREDNESS_PER_DRAGGING);
}

$dragging->addDragger($char->getId());
$dragging->saveInDb();

$draggingManager = new DraggingManager($char->getId());
$draggingManager->tryFinishingAll();

$db->commit();

redirect("char.events");
