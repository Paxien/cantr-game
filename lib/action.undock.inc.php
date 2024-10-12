<?php

$direction = HTTPContext::getInteger('direction', null);

$db = Db::get();
$db->beginTransaction();

$ship = Ship::loadById($char->getLocation());

if ($ship->getLocation()->getType() != LocationConstants::TYPE_VEHICLE) {
  CError::throwRedirectTag("char.description", "error_vehicle_cant_sail");
}

if (!$ship->canMoveOn(ConnectionConstants::TYPE_SEA) && !$ship->canMoveOn(ConnectionConstants::TYPE_LAKE)) {
  CError::throwRedirectTag("char.description", "error_not_on_vessel");
}

if ($ship->getLocation()->getRegion() == 0) {
  CError::throwRedirectTag("char.description", "error_nothing_to_undock_from");
}

// true = trying to undock NOW
if ($ship->canUndockInSitu() || isset($direction)) {
  $undockedFromId = $ship->getLocation()->getRegion();
  try {
    if ($ship->canUndockInSitu()) {
      $sailing = $ship->undock();
    } else {
      $sailing = $ship->undock($direction);
    }
    $sailing->saveInDb();
  } catch (NotOnWaterException $e) {
    CError::throwRedirectTag("char.description", "error_undock_not_on_water");
  } catch (IllegalStateException $e) {
    CError::throwRedirectTag("char.description", "error_already_undocked");
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("char.description", "error_already_undocked");
  }

  $db->commit();

  redirect("char.description");
} else {
  // has to select direction in the form

  $directions = $ship->getUndockingDirections();

  if (count($directions) == 0) {
    mail($GLOBALS['emailProgramming'], "No exit routes for undock attempt for " . $ship->getId(),
      "Vessel: " . $ship->getId() . " (docked to " . $ship->getLocation()->getRegion() . ")",
      "From: Programming Department <".$GLOBALS['emailProgramming'].">");
  }

  import_lib("func.getdirection.inc.php");
  $dirsWithDesc = array();
  foreach ($directions as $dirInDegrees => $desc) {
    $dirsWithDesc[$dirInDegrees] = getdirectionrawname($dirInDegrees);
  }

  $smarty = new CantrSmarty();
  $smarty->assign("dirs", $dirsWithDesc);
  $smarty->displayLang("page.undock.tpl", $lang_abr);
  $db->commit();
}
