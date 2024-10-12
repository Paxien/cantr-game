<?php
// in this file managing opening/closing locks - to bulding/vehicles/ships, and inner locks, in containers.
// from now this button will be use too to repair inner lock when it had been destroyed.

$object_id = HTTPContext::getInteger('object_id');

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "too_far_away");
}

if (!$object->hasAccessToAction("lock")) {
  CError::throwRedirectTag("char.objects", "error_action_impossible");
}

$isLocationLock = $object->hasProperty("LocationLock");
$canHaveInnerLock = !$isLocationLock; // we assume that every lock which isn't location lock is an inner lock



//if lock had destroyed so we use this button to repair it.
if ($canHaveInnerLock) {

  $innerLock = CObject::storedIn($object)->type(ObjectConstants::TYPE_INNER_LOCK)->find();
  if ($innerLock === null) {
    // creating new inner lock project
    redirect("build", [
      "targetcontainer" => $object_id,
      "objecttype" => ObjectConstants::TYPE_INNER_LOCK
    ]);
    exit();
  }

  $lockId = $innerLock->getId();
} elseif ($isLocationLock) {
  $lockId = $object->getId();
} else {
  throw new IllegalStateException("lock is neither inner lock nor building/vehicle lock");
}

if (!$char->isInSameLocationAs($object)) {
  CError::throwRedirectTag("char.objects", "error_not_near_lock");
}

//See if it is locked without a key
$isLockedWithoutKey = $object->hasProperty("LockedWithoutKey");

try {
  $keyLock = KeyLock::loadByLockId($lockId);
  if ($isLockedWithoutKey) {
    //allow it to be locked without checking for key
  } else {
    if (!$keyLock->hasKey($char->getId())) {
      CError::throwRedirectTag("char.objects", "error_not_right_key");
    }
  }
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

$oldLockStatus = $keyLock->isLocked() ? 'locked' : 'unlocked';
$newLockStatus = ($oldLockStatus == 'unlocked') ? 'locked' : 'unlocked';

$db = Db::get();
$stm = $db->prepare("UPDATE objects SET specifics = REPLACE(specifics, :oldStatus, :newStatus) WHERE id = :id");
$stm->bindStr("oldStatus", $oldLockStatus);
$stm->bindStr("newStatus", $newLockStatus);
$stm->bindInt("id", $lockId);
$stm->execute();


redirect("char.objects");
