<?php

/**
 * Immutable ORM class for data from table `objectcategories`
 * Object types being in this category are cached to improve performance
 */
class Category
{
  private $id;

  private static $typesForCategory = [];
  /** @var Db */
  private $db;

  public function __construct($id)
  {
    $this->id = intval($id);
    $this->db = Db::get();
  }

  public function contains($objectType)
  {
    if ($objectType instanceof ObjectType) {
      $objectType = $objectType->getId();
    }
    return in_array($objectType, $this->getTypes());
  }

  public function getTypes()
  {
    if (!array_key_exists($this->id, self::$typesForCategory)) {
      $stm = $this->db->prepare("SELECT id FROM objecttypes WHERE objectcategory = :id");
      $stm->bindInt("id", $this->id);
      $stm->execute();
      self::$typesForCategory[$this->id] = $stm->fetchScalars();
    }
    return self::$typesForCategory[$this->id];
  }

  public function isBuildable()
  {
    $categoryId = $this->getId();
    while ($categoryId != 0) { // check if this object is manufacturable
      $stm = $this->db->prepare("SELECT parent, status FROM objectcategories WHERE id = :id");
      $stm->bindInt("id", $categoryId);
      $stm->execute();
      list($categoryId, $unbuildable) = $stm->fetch(PDO::FETCH_NUM);
      if ($unbuildable) {
        return false;
      }
    }
    return true;
  }

  public function getId()
  {
    return $this->id;
  }
} 
