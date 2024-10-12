<?php

class LocationNaming
{
  private $location;
  private $db;

  public function __construct(Location $location, Db $db)
  {
    $this->location = $location;
    $this->db = $db;
  }

  /**
   * Return name remembered by a specified observer (if name was set) and all signs.
   * @param Character $observer the character whose perspective for remembered name is used.
   * @return string[] names of location
   */
  public function getAllNames(Character $observer)
  {
    $names = $this->getSignNames();

    $rememberedName = $this->getMainName($observer);
    if ($rememberedName != $names[0]) {
      array_unshift($names, $rememberedName);
    }

    return $names;
  }

  public function getSignNames()
  {
    $stm = $this->db->prepare("SELECT name FROM signs WHERE location = :locationId ORDER BY signorder");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->execute();
    return $stm->fetchScalars();
  }

  public function getMainName(Character $observer)
  {
    return TagBuilder::forLocation($this->location)->observedBy($observer)->allowHtml(false)->build()->interpret();
  }

  public function hasSignAlteringTool(Character $char)
  {
    $stm = $this->db->prepare("SELECT COUNT(o.id) FROM objects o
          INNER JOIN objecttypes ot ON ot.id = o.type 
          WHERE o.person = :charId AND ot.rules LIKE '%signwriting%' LIMIT 1");
    $stm->bindInt("charId", $char->getId());
    return $stm->executeScalar() > 0;
  }

  public function applySignsChange($projectResult)
  {
    list($changeType, $signOrder, $additionalParam) = explode(":", $projectResult, 3);
    switch ($changeType) {
      case NamingConstants::SIGN_CHANGE:
        $this->renameSign($signOrder, $additionalParam);
        break;
      case NamingConstants::SIGN_REMOVE :
        $this->removeSign($signOrder);
        break;
      case NamingConstants::SIGN_MOVE:
        $this->moveSign($signOrder, $additionalParam);
        break;
      case NamingConstants::SIGN_ADD:
        $this->addSign($signOrder, $additionalParam);
        break;
    }
  }

  private function renameSign($signOrder, $urlencodedNewName)
  {
    $newName = urldecode($urlencodedNewName);
    $stm = $this->db->prepare("UPDATE signs SET name = :newName WHERE location = :locationId AND signorder = :signOrder LIMIT 1");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->bindStr("newName", $newName);
    $stm->bindInt("signOrder", $signOrder);
    $stm->execute();

    if ($signOrder == 1) {
      $this->location->setName($newName);
      $this->location->saveInDb();
    }
  }

  private function removeSign($signOrder)
  {
    $stm = $this->db->prepare("DELETE FROM signs WHERE location = :locationId AND signorder = :signOrder LIMIT 1");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->bindInt("signOrder", $signOrder);
    $stm->execute();

    $stm = $this->db->prepare("UPDATE signs SET signorder = signorder - 1 WHERE location = :locationId AND signorder > :signOrder");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->bindInt("signOrder", $signOrder);
    $stm->execute();

    if ($signOrder == 1) {
      $stm = $this->db->prepare("SELECT name FROM signs WHERE location = :locationId AND signorder = 1");
      $stm->bindInt("locationId", $this->location->getId());
      $newName = $stm->executeScalar();
      $this->location->setName($newName);
      $this->location->saveInDb();
    }
  }

  private function moveSign($signOrder, $targetSignOrder)
  {
    $stm = $this->db->prepare("UPDATE signs SET signorder = 0 WHERE location = :locationId AND signorder = :signOrder LIMIT 1");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->bindInt("signOrder", $signOrder);
    $stm->execute();

    $stm = $this->db->prepare("UPDATE signs SET signorder = signorder - 1 WHERE location = :locationId AND signorder > :signOrder AND signorder <= :targetSignOrder");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->bindInt("signOrder", $signOrder);
    $stm->bindInt("targetSignOrder", $targetSignOrder);
    $stm->execute();

    $stm = $this->db->prepare("UPDATE signs SET signorder = signorder + 1 WHERE location = :locationId AND signorder >= :targetSignOrder");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->bindInt("targetSignOrder", $targetSignOrder);
    $stm->execute();

    $stm = $this->db->prepare("UPDATE signs SET signorder = :targetSignOrder WHERE location = :locationId AND signorder = 0 LIMIT 1");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->bindInt("targetSignOrder", $targetSignOrder);
    $stm->execute();

    if (($targetSignOrder == 1) || ($signOrder == 1)) {
      $stm = $this->db->prepare("SELECT name FROM signs WHERE location = :locationId AND signorder = 1");
      $stm->bindInt("locationId", $this->location->getId());
      $newName = $stm->executeScalar();
      $this->location->setName($newName);
      $this->location->saveInDb();
    }
  }

  private function addSign($signOrder, $urlencodedNewName)
  {
    $stm = $this->db->prepare("UPDATE signs SET signorder = signorder + 1 WHERE location = :locationId AND signorder >= :signOrder");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->bindInt("signOrder", $signOrder);
    $stm->execute();

    $newName = urldecode($urlencodedNewName);
    $stm = $this->db->prepare("INSERT INTO signs (location, name, signorder) VALUES (:locationId, :newName, :signOrder)");
    $stm->bindInt("locationId", $this->location->getId());
    $stm->bindInt("signOrder", $signOrder);
    $stm->bindStr("newName", $newName);
    $stm->execute();

    if ($signOrder == 1) {
      $this->location->setName($newName);
      $this->location->saveInDb();
    }
  }
}
