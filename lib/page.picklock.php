<?php

$lockId = HTTPContext::getInteger('lockId');

try {
  $lock = KeyLock::loadByLockId($lockId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirect("char.events", "error_no_lock_to_pick");
}

if ($lock->isObjectLock()) {
  $isNear = ObjectHandler::isObjectInLocation($lock->getObjectId(), $char->getLocation());
  if (!$isNear) {
    CError::throwRedirectTag("char.events", "error_too_far_away");
  }
} else {
  $charLoc = Location::loadById($char->getLocation());
  $targetLoc = Location::loadById($lock->getLocationId());
  if (($charLoc->getId() != $targetLoc->getId()) && !$targetLoc->isAdjacentTo($charLoc)) {
    CError::throwRedirectTag("char.events", "error_too_far_away");
  }
}

$toolsList = ObjectConstants::$TYPES_LOCKPICKING;
$db = Db::get();
$stm = $db->prepareWithIntList("SELECT id, type FROM objects o
  WHERE type IN (:tools) AND person = :charId LIMIT 1", [
    "tools" => $toolsList,
]);
$stm->bindInt("charId", $char->getId());
$stm->execute();
list($lockPickId, $lockPickType) = $stm->fetch(PDO::FETCH_NUM);

if ($lockPickId == null) {
  CError::throwRedirectTag("char.events", "error_exit_no_key_no_lockpick");
}

$alreadyLockPicking = Project::locatedIn($char->getLocation())
  ->type(ProjectConstants::TYPE_PICKING_LOCK)->result($lockId)->exists();
if ($alreadyLockPicking > 0) {
  CError::throwRedirectTag("char.events", "error_lockpick_already");
}

if ($lock->isObjectLock()) {
  $name = "<CANTR OBJNAME ID=". $lock->getObjectId() .">";
} else {
  $name = "<CANTR LOCNAME ID=". $lock->getLocationId() .">";
}

$lockData = array(
  "id" => $lock->getId(),
  "name" => $name
);

$lockPickData = array(
  "type" => $lockPickType,
  "name" => "<CANTR OBJNAME ID={$lockPickId}>"
);

$smarty = new CantrSmarty();
$smarty->assign("lock", $lockData);
$smarty->assign("lockpick", $lockPickData);
$smarty->assign("lock_location_name", htmlspecialchars($name)); // todo, for compatibility with old code


$charInfo = new CharacterInfoView($char);
$charInfo->show();

$smarty->displayLang("page.picklock.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
