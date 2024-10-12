<?php


/**
 * Class responsible for checking if location/object has a lock.
 * It can be also POTENTIAL keylock (empty hole when nobody built one), not just for existing locks.
 * Can be instantiated for objects and locations which don't have a lock, and then it always allow access
 */
class KeyLock
{
  private $lockId;
  private $locationId; // lock for location (vehicle or building) or null
  private $objectId; // attached to object or null
  /** @var Db */
  private $db;

  private $isLocked = false;

  private function __construct($lockId, $isForLoc, $parentId)
  {
    $this->lockId = $lockId;

    if ($isForLoc) {
      $this->locationId = $parentId;
    } else {
      $this->objectId = $parentId;
    }
    $this->db = Db::get();

    if ($this->hasId()) {
      $stm = $this->db->prepare("SELECT specifics FROM objects WHERE id = :objectId");
      $stm->bindInt("objectId", $this->lockId);
      $lockState = $stm->executeScalar();
      $this->isLocked = $lockState == "locked";
    }
  }

  public static function loadByObjectId($objectId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM objects WHERE attached = :objectId AND type = :type");
    $stm->bindInt("objectId", $objectId);
    $stm->bindInt("type", ObjectConstants::TYPE_INNER_LOCK);
    $lockId = $stm->executeScalar();
    return new KeyLock($lockId, false, $objectId);
  }

  public static function loadByLocationId($locationId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT objecttype_id type FROM obj_properties WHERE property_type = 'LocationLock'");
    $stm->execute();
    $locationLocks = $stm->fetchScalars();

    $db = Db::get();
    $stm = $db->prepareWithList("SELECT id FROM objects WHERE location = :locationId AND type IN (:types)", [
      "types" => $locationLocks,
    ]);
    $stm->bindInt("locationId", $locationId);
    $lockId = $stm->executeScalar();
    return new KeyLock($lockId, true, $locationId);
  }

  public static function loadByLockId($lockId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT objecttype_id type FROM obj_properties WHERE property_type = 'LocationLock'");
    $stm->execute();
    $locationLocks = $stm->fetchScalars();

    $db = Db::get();
    $stm = $db->prepare("SELECT location, attached, type FROM objects WHERE id = :objectId");
    $stm->bindInt("objectId", $lockId);
    $stm->execute();
    list($locId, $attId, $type) = $stm->fetch(PDO::FETCH_NUM);

    if (($locId > 0) && in_array($type, $locationLocks)) {
      return new KeyLock($lockId, true, $locId);
    } elseif (($attId > 0) && ($type == ObjectConstants::TYPE_INNER_LOCK)) {
      return new KeyLock($lockId, false, $attId);
    }
    throw new InvalidArgumentException("no existing lock with id $lockId");
  }

  public function canAccess($charId)
  {
    if (!$this->hasId()) {
      return true;
    }

    if (!$this->isLocked) {
      return true;
    }

    return $this->hasKey($charId);
  }

  public function isLocked()
  {
    return $this->isLocked;
  }

  public function hasKey($charId)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM objects WHERE person = :charId
      AND type = :type AND specifics = :specifics");
    $stm->bindInt("charId", $charId);
    $stm->bindInt("type", ObjectConstants::TYPE_KEY);
    $stm->bindStr("specifics", $this->lockId);
    $keyId = $stm->executeScalar();

    $stm = $db->prepare("SELECT obj.id FROM objects cont
      INNER JOIN objects obj ON obj.attached = cont.id
        AND obj.specifics = :specifics AND obj.type = :key
      WHERE cont.person = :charId AND cont.type = :keyring");
    $stm->bindStr("specifics", $this->lockId);
    $stm->bindInt("key", ObjectConstants::TYPE_KEY);
    $stm->bindInt("charId", $charId);
    $stm->bindInt("keyring", ObjectConstants::TYPE_KEYRING);
    $keyInKeyring = $stm->executeScalar();

    return ($keyId != null) || ($keyInKeyring != null);
  }

  public function redirectToLockpicking()
  {
    redirect("picklock", ["lockId" => $this->lockId]);
    exit();
  }

  public function getId()
  {
    return $this->lockId;
  }

  public function exists()
  {
    return $this->hasId();
  }

  public function hasId()
  {
    return $this->lockId != null;
  }

  public function isObjectLock()
  {
    return $this->objectId != null;
  }

  public function isLocationLock()
  {
    return $this->locationId != null;
  }

  public function getObjectId()
  {
    return $this->objectId;
  }

  public function getLocationId()
  {
    return $this->locationId;
  }
}
