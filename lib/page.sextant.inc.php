<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');
$action = $_REQUEST['action'];

show_title("<CANTR REPLACE NAME=title_sextant>");

echo "<div class=\"page\">";
echo "<table><TR><TD>";

// Check whether sextant is either in inventory or location of character
try {
  $object = CObject::loadById($object_id);
  $sextantExists = (int) $object->getType() === ObjectConstants::TYPE_SEXTANT && $char->hasWithinReach($object);
} catch (InvalidArgumentException $e) {
  $sextantExists = false;
}

if ($sextantExists) {
  $pos = $char->getPos();
  $x = round($pos['x']);
  $y = round($pos['y']);
  $specificsSeparator = ":";

  if ($action == "reset") {
    // Reset origin to current location
    $object->setSpecifics(implode($specificsSeparator, [$x, $y]));
    $object->saveInDb();
  }

  $origin = explode($specificsSeparator, $object->getSpecifics());

  if (count($origin) < 2) {

    echo "<CANTR REPLACE NAME=sextant_no_origin>";
  } else {

    $x = $x - $origin[0];
    $y = $y - $origin[1];

    // We don't want the borders of the map (0 or 6000 lines) to be visible
    // so always take the shortest distance
    if ($x > 3000) $x -= 6000;
    if ($x < -3000) $x += 6000;
    if ($y > 3000) $y -= 6000;
    if ($y < -3000) $y += 6000;

    // Also calculate distance and direction to origin
    $distance = round(sqrt($x*$x + $y*$y));

    $xdir = -$x; // To be able to compare with util.setdirections.inc.php
    $ydir = -$y;
    $direction = $ydir != 0 ? $ydir > 0 ? rad2deg(atan($xdir / $ydir)) : 180 + rad2deg(atan($xdir / $ydir)) : ($xdir >= 0 ? 90 : 270);
    $direction = (360 + round(90 - $direction)) % 360;

    $x = round($x);
    $y = round($y);

    echo "<CANTR REPLACE NAME=sextant_info X=$x Y=$y DIR=$direction DIST=$distance>";
  }

  echo "<BR><BR><CENTER><A HREF=\"index.php?page=sextant&action=reset&object_id=$object_id\"><CANTR REPLACE NAME=button_reset></A></CENTER>";

  echo "<BR><BR><CENTER><A HREF=\"index.php?page=char\"><CANTR REPLACE NAME=back_to_character></A></CENTER>";
} else {
  CError::throwRedirect("char.events", "error_sextant_not_here");
}

echo "</TR></TD></TABLE>";
echo "</div>";
