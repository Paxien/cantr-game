<?php

//SANITIZE INPUT
$speed = HTTPContext::getInteger('speed');
$direction = HTTPContext::getInteger('direction');
$hours = HTTPContext::getInteger('hours');

$data = $_REQUEST['data'];

try {
  $sailing = Sailing::loadByVesselId($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.description", "error_alter_not_on_vessel");
}

if ($sailing->isDocking()) {
  CError::throwRedirectTag('char.description', 'error_alter_not_while_docking');
}

if ($data) {

  $sailing->setSpeedPercent($speed);
  $sailing->setDirection($direction);

  if ($hours) {
    $stopTimestamp = GameDate::NOW()->plus(GameDate::fromDate(0, $hours, 0, 0))->getTimestamp();
    $sailing->setSailingStopTimestamp($stopTimestamp);
  } else {
    $sailing->setSailingStopTimestamp(0);
  }

  $sailing->saveInDb();

  $shipName = urlencode ("<CANTR LOCNAME ID=". $sailing->getShip()->getId() .">");
  Event::create(234, "SHIPNAME=$shipName SPEED=". $sailing->getSpeedPercent()
    ." DIRECTION=". $sailing->getDirection())->forCharacter($char)->highlight(false)->show();
  Event::create(233, "SHIPNAME=$shipName SPEED=". $sailing->getSpeedPercent()
    ." DIRECTION=". $sailing->getDirection() ." ACTOR=". $char->getId())
      ->nearCharacter($char)->andAdjacentLocations()->except($char)->show();

  redirect("char.events");
} else {
  $dateFromTimestamp = GameDate::fromTimestamp($sailing->getSailingStopTimestamp());
  $hours = $dateFromTimestamp->minus(GameDate::NOW())->getTimestamp()
    / (GameDateConstants::SECS_PER_MIN * GameDateConstants::MINS_PER_HOUR);

  $smarty = new CantrSmarty;
  $smarty->assign ("DEGREE", $sailing->getDirection());
  $smarty->assign ("CURRENTSPEED", round($sailing->getSpeedPercent()));
  $smarty->assign ("TURNS", GameDateConstants::HOURS_PER_DAY);
  $smarty->assign ("saildirection", $sailing->getDirection());
  $smarty->assign ("sailspeed", round($sailing->getSpeedPercent()));
  $smarty->assign ("sailhours", ceil($hours));

  $smarty->displayLang ("action.adjustsailing.tpl", $lang_abr);
}
