<?php

/**
 * Domesticated animals in form visible on objects page
 */
class DomesticatedAnimalObject extends Animal
{

  protected $id;
  protected $name;
  protected $unique_name;
  protected $location;
  protected $object_type;
  protected $animal_type;
  protected $weight;

  protected $fullness;
  protected $food_type;
  protected $food_amount;

  protected $specifics;
  protected $type_details;
  protected $tame_rules;

  protected $loyal_to;
  protected $loyalty;
  protected $can_be_loyal;
  /** @var Db */
  private $db;

  public static function loadById($object_id)
  {
    return new DomesticatedAnimalObject($object_id);
  }

  public static function loadFromDb($object_id)
  {
    return self::loadById($object_id);
  }

  public function __construct($object_id)
  {
    $this->db = Db::get();
    $stm = $this->db->prepare("SELECT o.*, ot.unique_name, ot.objectcategory FROM objects o
      INNER JOIN objecttypes ot ON ot.id = o.type
      WHERE o.id = :objectId");
    $stm->bindInt("objectId", $object_id);
    $stm->execute();
    $fetch = $stm->fetchObject();
    if ($fetch->objectcategory != ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS) { // TODO do not return null in constructor
      return null;
    }
    $this->id = $fetch->id;
    $this->location = $fetch->location;
    $this->object_type = $fetch->type;
    $this->weight = $fetch->weight;
    $this->unique_name = $fetch->unique_name;

    $stm = $this->db->prepare("SELECT ad.fullness, ad.specifics, adt.food_type, adt.food_amount, adt.tame_rules,
        ad.loyal_to, ad.loyalty, adt.can_be_loyal,
        at.name, at.id AS animal_type, adt.type_details
      FROM animal_domesticated ad
      INNER JOIN animal_domesticated_types adt ON adt.of_object_type = :objectTypeId
      INNER JOIN animal_types at ON at.id = adt.of_animal_type
      WHERE ad.from_object = :objectId");
    $stm->bindInt("objectTypeId", $this->object_type);
    $stm->bindInt("objectId", $object_id);
    $stm->execute();
    $fetch_da = $stm->fetchObject();

    if (!$fetch_da) {
      return null;
    }

    $this->name = $fetch_da->name;
    $this->animal_type = $fetch_da->animal_type;

    $this->fullness = $fetch_da->fullness;
    $this->food_type = $fetch_da->food_type;
    $this->food_amount = $fetch_da->food_amount;

    $this->specifics = $fetch_da->specifics;
    $this->type_details = $fetch_da->type_details;
    $this->tame_rules = $fetch_da->tame_rules;

    $this->loyal_to = $fetch_da->loyal_to;
    $this->loyalty = $fetch_da->loyalty;
    $this->can_be_loyal = $fetch_da->can_be_loyal;
  }

  public function annihilate()
  {
    import_lib("func.expireobject.inc.php");

    $animalExpired = expire_object($this->id);
    if (!$animalExpired) {
      return false;
    }

    $stm = $this->db->prepare("DELETE FROM animal_domesticated WHERE from_object = :objectId");
    $stm->bindInt("objectId", $this->id);
    $stm->execute();

    $this->id = null;
    $this->location = null;
    $this->animal_type = null;
    $this->specifics = null;
    $this->loyal_to = null;

    return true;
  }

  public function getRawPoolArray($action)
  {
    $pool = Parser::rulesToArray($this->specifics);
    $typePool = Parser::rulesToArray($this->type_details);

    $pool = $pool[$action];
    $pool = Parser::rulesToArray($pool, ",>");
    $typePool = $typePool[$action];
    $typePool = Parser::rulesToArray($typePool, ",>");

    $raws = array();
    foreach ($typePool as $rawtype => $data) {
      $raw['name'] = $rawtype;
      list ($raw['maxAmount'], $raw['dailyIncrease'], $raw['dailyHarvest']) = explode(">", $data); // get "type" data about that raw
      $raw['amount'] = $pool[$rawtype]; // get current amount

      $raws[$rawtype] = $raw;
    }
    return $raws;
  }

  public function getFullness()
  {
    return $this->fullness;
  }

  public function getSpecificsString()
  {
    return $this->specifics;
  }

  public function getTypeDetailsString()
  {
    return $this->type_details;
  }

  public function getTameRulesArray()
  {
    return Parser::rulesToArray($this->getTameRulesString());
  }

  public function getTameRulesString()
  {
    return $this->tame_rules;
  }

  public function getType()
  {
    return $this->animal_type;
  }

  public function getObjectType()
  {
    return $this->object_type;
  }

  public function isDomesticated()
  {
    return true;
  }

  public function getLocation()
  {
    return $this->location;
  }

  public function getLoyalTo()
  {
    return $this->loyal_to;
  }

  public function isLoyalTo(Character $char)
  {
    return $this->getLoyalTo() == $char->getId();
  }

  public function getName()
  {
    return str_replace(" ", "_", $this->name);
  }

  public function getUniqueName()
  {
    return $this->unique_name;
  }

  public function getNameTag()
  {
    return "<CANTR REPLACE NAME=animal_{$this->name}_s>";
  }

  public function getWeight()
  {
    return $this->weight;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getCanBeLoyal()
  {
    return $this->can_be_loyal;
  }

}
