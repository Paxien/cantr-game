<?php

try {
  $sailing = Sailing::loadByVesselId($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.description", "error_alter_not_on_vessel");
}

if (!$sailing->isDocking()) {
  CError::throwRedirectTag("char.description", "error_not_currently_docking");
}

if (!$sailing->getShip()->onWater()) {
  CError::throwRedirectTag("char.description", "error_cancel_docking_not_on_water");
}

if (ceil($sailing->getDistanceToDockingTarget() / $sailing->getSpeed()) == 1) { // can't 
  CError::throwRedirectTag("char.description", "error_too_close_to_stop_docking");
}

if ($data) {
  $ship = $sailing->getShip();
  Event::create(180, "SHIPNAME=". $ship->getId() ." TARGET=". $sailing->getDockingTarget() .
    " ACTOR=". $char->getId())->inLocation($ship->getLocation())->except($char)->show();

  Event::create(181, "SHIPNAME=". $ship->getId() ." TARGET=". $sailing->getDockingTarget())->
    forCharacter($char)->show();

  $sailing->setDockingTarget(null);
  $sailing->setSpeedPercent(0);
  $sailing->setDirection(0);
  $sailing->updateDockable();
  $sailing->saveInDb();

  redirect("char.description");
} else {

  $smarty = new CantrSmarty();
  $smarty->assign ("SHIPNAME", $sailing->getShip()->getId());
  $smarty->assign ("TARGET", $sailing->getDockingTarget());
  
  $smarty->displayLang ("action.canceldocking.tpl", $lang_abr); 
}
