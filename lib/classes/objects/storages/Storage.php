<?php

class Storage
{
  const RETRIEVE_TO_INVENTORY = "inventory";
  const RETRIEVE_TO_GROUND = "ground";

  private $id;
  private $storageObj;
  private $capacity;
  /** @var array */
  private $allowedRestrictionGroups;

  private $logger;
  /** @var Db */
  private $db;

  public function __construct(CObject $object)
  {
    $storeDetails = $object->getProperty("Storage");
    if (!$storeDetails) {
      throw new InvalidArgumentException("it's not a storage");
    }

    $this->capacity = $storeDetails['capacity'];
    $this->allowedRestrictionGroups = array_key_exists("allowedRestrictionGroups",
      $storeDetails) ? $storeDetails["allowedRestrictionGroups"] : [];
    $this->storageObj = $object;
    $this->id = $object->getId();

    $this->logger = Logger::getLogger(__CLASS__);
    $this->db = Db::get();
  }

  // todo - currently there's no difference between "amount" and "weight", because we assume that
  // only raws can be stored/retrieved in quantity.
  // It should be fixed in the future to allow coins and others.
  /**
   * @param CObject $object
   * @param Character $char
   * @param $target
   * @param null $amount
   *
   * @throws
   * @throws InvalidAmountException
   * @throws InvalidObjectSettingException
   * @throws NoKeyToInnerLockException
   * @throws TooFarAwayException
   * @throws WeightCapacityExceededException
   */
  public function retrieve(CObject $object, Character $char, $target, $amount = null)
  {
    $weight = $amount;
    if ($object->isQuantity()) {
      if (!Validation::isPositiveInt($weight) || $weight > $object->getWeight()) {
        throw new InvalidAmountException();
      }
    } else {
      $weight = $object->getWeight();
    }

    // check if storage and its content is within reach
    if ($object->getAttached() != $this->getId()) {
      throw new TooFarAwayException();
    }

    try {
      $object->tryAccessibilityInStorage($char, true, false);
    } catch (NoKeyToInnerLockException $e) {
      throw new NoKeyToInnerLockException();
    } catch (Exception $e) {
      throw new TooFarAwayException();
    }

    $storageInInventory = $char->hasInInventory($this->storageObj);

    // trying to take dead body to inventory, disallowed until implementation of heavy objects
    if ($target == self::RETRIEVE_TO_INVENTORY
      && $object->getType() == ObjectConstants::TYPE_DEAD_BODY) {
      throw new InvalidObjectSettingException();
    }

    // temporarily allow no quantity objects except raws
    $isRaw = $object->getType() == ObjectConstants::TYPE_RAW;
    $isFixed = $object->getSetting() == ObjectConstants::SETTING_FIXED;
    if ($isFixed || ($object->isQuantity() && !$isRaw)) {
      throw new InvalidObjectSettingException();
    }

    if (!$storageInInventory && $target == Storage::RETRIEVE_TO_INVENTORY
      && ($char->getInventoryWeight() + $weight) > $char->getMaxInventoryWeight()) {
      throw new WeightCapacityExceededException();
    }

    if ($object->getType() == ObjectConstants::TYPE_RAW) {
      $rawType = $object->getTypeid();
      $successful = ObjectHandler::rawToContainer($this->getId(), $rawType, (-1) * $weight);
      if (!$successful) { // somebody is trying to clone raws
        throw new InvalidAmountException();
      }
      if ($target == Storage::RETRIEVE_TO_INVENTORY) {
        ObjectHandler::rawToPerson($char->getId(), $rawType, $weight);
      } else {
        ObjectHandler::rawToLocation($char->getLocation(), $rawType, $weight);
      }
    } else {
      $stm = $this->db->prepare("SELECT id FROM objects WHERE id = :objectId
        AND location = :locationId AND person = :charId AND attached = :storageId");
      $stm->bindInt("objectId", $object->getId());
      $stm->bindInt("locationId", $object->getLocation());
      $stm->bindInt("charId", $object->getPerson());
      $stm->bindInt("storageId", $object->getAttached());
      $isObjectInTheSamePlace = $stm->executeScalar();
      if (!$isObjectInTheSamePlace) {
        throw new InvalidAmountException(); // object is probably already retrieved
      }
      $object->setAttached(0);
      if ($target == Storage::RETRIEVE_TO_INVENTORY) {
        $object->setPerson($char->getId());
      } else {
        $object->setLocation($char->getLocation());
      }
      $object->setOrdering(0);
      $object->saveInDb();

      $storageId = $this->getId();
      // reduce container weight
      do {
        $stm = $this->db->prepare("SELECT weight FROM objects WHERE id = :objectId");
        $stm->bindInt("objectId", $storageId);
        $currentWeight = $stm->executeScalar();

        if ($currentWeight >= $weight) {
            $stm = $this->db->prepare("UPDATE objects SET weight = weight - :weightChange WHERE id = :objectId");
            $stm->bindInt("weightChange", $weight);
            $stm->bindInt("objectId", $storageId);
            $stm->execute();
        } else {
            // Log an error or throw an exception to handle the situation where the new weight would be negative
            $this->logger->error("Unable to update object weight, as the new weight would be negative.");
            return; // Stop the execution since this is probably a race condition (e.g. a double click)
        }

        $stm = $this->db->prepare("SELECT attached FROM objects WHERE id = :objectId");
        $stm->bindInt("objectId", $storageId);
        $storageId = $stm->executeScalar();
      } while ($storageId > 0);
    }
    $this->storageObj->setWeight($this->storageObj->getWeight() - $weight);

    if (!$object->isQuantity()) {
      try {
        $translocationMonitor = new TranslocationMonitor();
        $goal = $target == self::RETRIEVE_TO_INVENTORY ? $char : Location::loadById($char->getLocation());
        $translocationMonitor->recordObjectTranslocation($this->storageObj, $goal, $object);
      } catch (Exception $e) {
        $this->logger->warn("Unable to record retrieving an object {$object->getId()}" .
          " from {$this->storageObj->getId()} by character {$char->getId()}", $e);
      }
    }
  }

  public function store(CObject $object, Character $char, $amount = null)
  {
    $weight = $amount; // currently assume that quantity object is always raw (1 unit=1gram) TODO!!!
    if ($object->isQuantity()) {
      if (!Validation::isPositiveInt($weight) || $weight > $object->getWeight()) {
        throw new InvalidAmountException();
      }
    } else {
      $weight = $object->getWeight();
    }

    if (!$char->hasWithinReach($this->storageObj) || !$char->hasWithinReach($object)) {
      throw new TooFarAwayException();
    }

    if (($object->getSetting() != ObjectConstants::SETTING_PORTABLE)
      && !($object->isQuantity() && $object->getType() == ObjectConstants::TYPE_RAW)) {
      throw new InvalidObjectSettingException();
    }

    if ($object->getId() == $this->getId()) { // trying to store inside of itself
      throw new InvalidObjectSettingException();
    }

    $storingRestrictionGroup = $object->getProperty("StorageRestrictionGroup");
    if ($storingRestrictionGroup !== null) {
      if (!in_array($storingRestrictionGroup, $this->allowedRestrictionGroups)) {
        throw new InvalidStorageType($this->storageObj->getUniqueName() . " can't hold a restriction group: " . $storingRestrictionGroup);
      }
    }

    // needed until "heavy objects" get implemented
    if ($char->hasInInventory($this->storageObj)
      && $object->getType() == ObjectConstants::TYPE_DEAD_BODY) {
      throw new InvalidObjectSettingException();
    }

    $objectInInventory = $char->hasInInventory($object);

    // Make sure the total weight carried by the character doesn't exceed the maximum
    // only when resources which you want to store are on the ground
    if (!$objectInInventory && $char->hasInInventory($this->storageObj)) {
      if ($char->getInventoryWeight() + $weight > $char->getMaxInventoryWeight()) {
        throw new WeightCapacityExceededException();
      }
    }

    if ($weight > $this->getSpaceLeft()) {
      throw new StorageCapacityExceededException();
    }

    $needKeyToLock = !$this->storageObj->hasProperty("IgnoreStoringRestrictions");
    //add checking for closed containers.
    $containerLock = KeyLock::loadByObjectId($this->getId());
    if ($needKeyToLock && !$containerLock->canAccess($char->getId())) {
      throw new NoKeyToInnerLockException();
    }

    //perform

    // it's a raw // TODO! only for raws, should be generic to allow coins
    if ($object->getType() == ObjectConstants::TYPE_RAW) {
      $rawType = $object->getTypeid();
      if ($char->hasInInventory($object)) {
        $successful = ObjectHandler::rawToPerson($object->getPerson(), $rawType, -1 * $weight);
      } else {
        $successful = ObjectHandler::rawToLocation($object->getLocation(), $rawType, -1 * $weight);
      }
      if (!$successful) {
        throw new InvalidAmountException(); // something's wrong with initial pile
      }
      ObjectHandler::rawToContainer($this->getId(), $rawType, $weight);
    } else { // not a raw
      $stm = $this->db->prepare("SELECT id FROM objects WHERE id = :objectId
        AND location = :locationId AND person = :charId");
      $stm->bindInt("objectId", $object->getId());
      $stm->bindInt("locationId", $object->getLocation());
      $stm->bindInt("charId", $object->getPerson());
      $isObjectInTheSamePlace = $stm->executeScalar();
      if (!$isObjectInTheSamePlace) {
        throw new InvalidAmountException(); // object is probably already stored
      }
      $object->setLocation(0);
      $object->setPerson(0);
      $object->setAttached($this->getId());
      $object->setOrdering(0);
      $object->saveInDb();
      $stm = $this->db->prepare("UPDATE objects SET weight = weight + :weightChange WHERE id = :objectId");
      $stm->bindInt("weightChange", $object->getWeight());
      $stm->bindInt("objectId", $this->getId());
      $stm->execute();
    }
    $this->storageObj->setWeight($this->storageObj->getWeight() + $weight);
    // just to keep ORM object synced

    if (!$object->isQuantity()) {
      try {
        $translocationMonitor = new TranslocationMonitor();
        $translocationMonitor->recordObjectTranslocation($char, $this->storageObj, $object);
      } catch (Exception $e) {
        $this->logger->warn("Unable to record storing an object {$object->getId()} into a storage", $e);
      }
    }
  }

  public function getPrintableData(Character $observer)
  {
    $storeData = [];
    $storeData['id'] = $this->getId();
    $storeData['name'] = TagBuilder::forObject($this->storageObj, true)
      ->observedBy($observer)->build()->interpret();

    $storeData['space'] = "locked";

    $needKeyToLock = !$this->storageObj->hasProperty("IgnoreStoringRestrictions");
    $containerLock = KeyLock::loadByObjectId($this->getId());

    if (!$needKeyToLock || $containerLock->canAccess($observer->getId())) {
      $storeData['space'] = $this->getSpaceLeft(); // some storages don't require key
    }
    $storeData['description'] = $this->storageObj->getDescription($this->getId());
    return $storeData;
  }

  public function getCapacity()
  {
    return $this->capacity;
  }

  public function getBaseWeight()
  {
    return $this->storageObj->getTypeWeight();
  }

  public function getSpaceLeft() // only weight of stored items matters (inner lock doesn't)
  {
    return $this->getCapacity() - ($this->storageObj->getWeight() - $this->getBaseWeight());
  }

  public function getId()
  {
    return $this->id;
  }
}
