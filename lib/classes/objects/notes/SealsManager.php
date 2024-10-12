<?php

class SealsManager
{
  /** @var CObject */
  private $object;
  /** @var Db */
  private $db;

  public function __construct(CObject $object)
  {
    $this->object = $object;
    $this->db = Db::get();
  }

  public function getAll($includeAnonymous = false, $broken = false)
  {
    $stm = $this->db->prepare("SELECT name FROM seals WHERE note = :objectId AND broken = :broken " .
      (!$includeAnonymous ? " AND anonymous = 0" : "") . " ORDER BY name");
    $stm->bindInt("objectId", $this->object->getId());
    $stm->bindBool("broken", $broken);
    $stm->execute();
    return $stm->fetchScalars();
  }

  public function addSeal($name)
  {
    $stm = $this->db->prepare("INSERT INTO seals (note, name, anonymous, broken) VALUES
      (:objectId, :name, false, false)");
    $stm->bindInt("objectId", $this->object->getId());
    $stm->bindStr("name", $name);
    $stm->execute();
  }

  public function addAnonymousSeal()
  {
    $stm = $this->db->prepare("INSERT INTO seals (note, name, anonymous, broken) VALUES
      (:objectId, '', true, false)");
    $stm->bindInt("objectId", $this->object->getId());
    $stm->execute();
  }

  public function breakAll()
  {
    $stm = $this->db->prepare("UPDATE seals SET broken = true WHERE note = :objectId");
    $stm->bindInt("objectId", $this->object->getId());
    $stm->execute();
  }

  /**
   * Removes exactly one seal with the specified name. It does nothing if seal with specified name doesn't exist.
   * @param $name string name of seal to remove. Empty string means removing anonymous seal
   */
  public function remove($name)
  {
    $stm = $this->db->prepare("DELETE FROM seals WHERE note = :objectId AND name = :name LIMIT 1");
    $stm->bindInt("objectId", $this->object->getId());
    $stm->bindStr("name", $name);
    $stm->execute();
  }
}
