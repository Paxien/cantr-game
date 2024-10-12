<?php

include_once _LIB_LOC . "/func.genes.inc.php";
require_once _LIB_LOC . "/func.expireobject.inc.php";

abstract class AnimalPack extends Animal {

  public $id;

  public $ok;

  public $name;
  public $type;
  public $location;
  public $number;

  protected $travel_chance;
  protected $reproduction_chance;
  protected $area_types;
  protected $max_in_location;

  protected $attack_chance;
  protected $attack_force;
  protected $strength;
  protected $armour;
  protected $damage;
  protected $resources;

  /** @var Db */
  protected $db;

  abstract protected function removePack();
  abstract public function getDomesticationActions();
  abstract public function isDomesticated();

  public static function loadFromDb($pack_id, $type_fetch = null) {
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM animal_domesticated WHERE from_animal = :animalId");
    $stm->bindInt("animalId", $pack_id);
    if ($u = $stm->executeScalar()) {
      $pack = new DomesticatedAnimalPack($pack_id, $type_fetch);
    } else {
      $pack = new WildAnimalPack($pack_id, $type_fetch);
    }
    return $pack;
  }

  protected function __construct ($pack_id, $type_fetch) {
    $this->db = Db::get();
    $stm = $this->db->prepare("SELECT * FROM animals WHERE id = :animalId");
    $stm->bindInt("animalId", $pack_id);
    $stm->execute();

    if ($stm->rowCount() > 0) {
      $animal_info = $stm->fetchObject();
      $this->ok = true;
      $this->id = $animal_info->id;
      $this->type = $animal_info->type;
      $this->number = $animal_info->number;
      $this->damage = $animal_info->damage;
      $this->location = $animal_info->location;

      $this->get_animal_type($type_fetch); // load the rest from db
    }
  }

  public function get_animal_type($type_fetch = null) {
    if (!$type_fetch->name) {
      $stm = $this->db->prepare("SELECT * FROM animal_types WHERE id = :id");
      $stm->bindInt("id", $this->type);
      $stm->execute();
      $type_fetch = $stm->fetchObject();
    }
    $this->name = $type_fetch->name;
    $this->reproduction_chance = $type_fetch->reproduction_chance;
    $this->area_types = $type_fetch->area_types;
    $this->attack_chance = $type_fetch->attack_chance;
    $this->attack_force = $type_fetch->attack_force;
    $this->strength = $type_fetch->strength;
    $this->armour = $type_fetch->armour;
    $this->resources = $type_fetch->resources;
    $this->travel_chance = $type_fetch->travel_chance;
    $this->max_in_location = $type_fetch->max_in_location;
  }

  public function isHuntingPossible($char_id) {
    $stm = $this->db->prepare("SELECT COUNT(*) FROM hunting WHERE perpetrator = :perpetrator AND location = :locationId AND animal_type = :type LIMIT 1");
    $stm->bindInt("perpetrator", $char_id);
    $stm->bindInt("locationId", $this->location);
    $stm->bindInt("type", $this->type);
    return $stm->executeScalar() == 0;
  }

  public function attack () {
    if ($this->location != 0 ) {
      // Select one random character on same location
      $location_id = $this->location;
      $stm = $this->db->prepare("SELECT COUNT(*) FROM chars WHERE location = :locationId AND status = :active");
      $stm->bindInt("locationId", $location_id);
      $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
      $ncount = $stm->executeScalar();
      if($ncount) {
        $ncount--;
        if ($ncount) {
          $offset = rand(0,$ncount);
        } else {
          $offset = 0;
        }
        $ncount++;
        $stm = $this->db->prepare("SELECT id FROM chars WHERE location = :locationId AND status = :active  LIMIT 1 OFFSET :offset");
        $stm->bindInt("locationId", $location_id);
        $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
        $stm->bindInt("offset", $offset);
        $char_id = $stm->executeScalar();
      }
    }

    if ($ncount && $char_id) {
      $this->attackChar($char_id);
    }
  }

  public function attackChar($char_id) {
    $attack_force = floor(normal($this->attack_force, _ANIMAL_DEV_ATTACK));

    // Record attack
    $stm = $this->db->prepare("INSERT INTO animal_interaction (perpetrator_type, perpetrator_id, victim_type, victim_id, interaction_type)
        VALUES (2, :perpetrator, 1, :victim,1)");
    $stm->bindInt("perpetrator", $this->id);
    $stm->bindInt("victim", $char_id);
    $stm->execute();

    $this->hurt_char($char_id, $attack_force);
  }


  protected function hurt_char ($id, $down) {

    if (CharacterHandler::isNearDeath($id)) { // don't attack chars in NDS
      return;
    }

    $down *= _SCALESIZE_GSS / 100; // To adjust to new scale type

    // Tell the character how many hit points it received;
    // have it record its updated health
    $protection = 0;

    $stm = $this->db->prepare("SELECT o.id, unique_name, type, rules FROM objects o INNER JOIN objecttypes ot ON ot.id = o.type
      WHERE person = :charId AND ot.rules LIKE '%shield%' ORDER BY deterioration DESC");
    $stm->bindInt("charId", $id);
    $stm->execute();
    foreach ($stm->fetchAll() as $shield) {

      $rules = Parser::rulesToArray($shield->rules);
        if (array_key_exists("shield", $rules)) {
          $shieldProtection = $rules["shield"];
          if ($shieldProtection > $protection) {

            $prot_id = $shield->id;
            $protection = $shieldProtection;

            $prot_desc = urlencode("<CANTR REPLACE NAME=item_{$shield->unique_name}_o>");
          }
        }
    }

    $current_health = read_state($id, _GSS_HEALTH);

    if ($protection) {

      $up = floor(
        (read_state($id, _GSS_HUNTING)
          / 16667
          + read_state($id, _GSS_STRENGTH)
          / 25000)
        * (1.2
          - read_state($id, _GSS_TIREDNESS)
          / _SCALESIZE_GSS)
        * $protection
        * 150);
      $decay_factor = 1/8;

      if ($up <= $down)
        $down -= $up;
      else {
        $up = $down;
        $decay_factor = $decay_factor * $down / $up;
        $down = 0;
      }

      usage_decay_object($prot_id,$decay_factor);
    }

    $animal_tag = str_replace(" ","_", $this->name);
    $animal = urlencode("<CANTR REPLACE NAME=animal_". $animal_tag ."_o>");

    if ($down > $current_health) {
      Event::createPersonalEvent(36, "ANIMAL=$animal ANIMAL_TAG=$animal_tag", $id);

      $char = Character::loadById($id);
      $char->intoNearDeath(CharacterConstants::CHAR_DEATH_ANIMAL, $this->type);
      $char->saveInDb();

      $watcherEventType = 35; // killed by animal
    } else {
      alter_state ($id, _GSS_HEALTH, 0 - $down);

      $down = floor($down / 100);
      $up = floor($up / 100);

      if ($protection) {
        Event::createPersonalEvent(39, "ANIMAL=$animal ANIMAL_TAG=$animal_tag PERCENT=$down SAVED=$up DEFENSE=$prot_desc", $id);
      } else {
        Event::createPersonalEvent(38, "ANIMAL=$animal ANIMAL_TAG=$animal_tag PERCENT=$down", $id);
      }
      $watcherEventType = 37; // hurt by animal
    }
    Event::createPublicEvent($watcherEventType, "ANIMAL=$animal ANIMAL_TAG=$animal_tag VICTIM=$id", $id, Event::RANGE_NEAR_LOCATIONS, array($id));
  }

  public function hurt_animal ($char, $attack_force) {

    $down = $attack_force - floor((($this->strength - $this->damage)
				   / $this->strength) * $this->armour);
    if ($down < 0)
      $down = 0;

    if (($this->damage + $down) >= $this->strength) {
      $this->number--;
      if ($this->number > 0) {
        $stm = $this->db->prepare("UPDATE animals SET number=number-1,damage=0 WHERE id = :id LIMIT 1");
        $stm->bindInt("id", $this->id);
        $stm->execute();
      } else {
        $this->removePack();
        $this->ok = false;
      }

      $raws = Parser::rulesToArray($this->resources, ",>");

      foreach ($raws as $rawName => $baseAmount) {

        $stm = $this->db->prepare("SELECT id FROM rawtypes WHERE name = :name");
        $stm->bindStr("name", $rawName);
        $type = $stm->executeScalar();

        $result_amount = floor (normal ($baseAmount, .2));

        $stm = $this->db->prepare("SELECT SUM(weight) FROM objects WHERE person = :charId AND type != 1 AND weight > 0");
        $stm->bindInt("charId", $char);
        $char_weight = $stm->executeScalar();

        if (($char_weight + $result_amount) <= CharacterConstants::INVENTORY_WEIGHT_MAX) { // into inventory
          ObjectHandler::rawToPerson($char, $type, $result_amount);
        } elseif (Validation::isPositiveInt($this->location)) { // on the ground
          ObjectHandler::rawToLocation($this->location, $type, $result_amount);
        }
      }
      $down = -1;
    } else {
      $stm = $this->db->prepare("UPDATE animals SET damage = damage + :change WHERE id = :id");
      $stm->bindInt("change", $down);
      $stm->bindInt("id", $this->id);
      $stm->execute();
    }

    return $down;
  }

  public function decrementNumber() {
    $stm = $this->db->prepare("UPDATE animals SET number = number -1 WHERE id = :id AND number = :number");
    $stm->bindInt("id", $this->id);
    $stm->bindInt("number", $this->number);
    $stm->execute();
    $decrementedCorrectly = $stm->rowCount() > 0;

    if (!$decrementedCorrectly) {
      return false;
    }

    $this->number--;
    if ($this->number == 0) {
      $this->removePack();
      $this->ok = false;
    }
    return true;
  }

  public function incrementNumber() {
    $this->number++;
    $stm = $this->db->prepare("UPDATE animals SET number = :number WHERE id = :id");
    $stm->bindInt("number", $this->number);
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }

  public function getName() {
    return str_replace(" ", "_", $this->name);
  }

  public function getNameTag() {
    $name = $this->getName();
    if ($this->number == 1) {
      return "<CANTR REPLACE NAME=animal_{$name}_s>";
    }
    else {
      return "<CANTR REPLACE NAME=animal_{$name}_p>";
    }
  }

  public function getId() {
    return $this->id;
  }

  public function getType() {
    return $this->type;
  }

  public function getNumber() {
    return $this->number;
  }

  public function getDamage() {
    return $this->damage;
  }

  public function getStrength() {
    return $this->strength;
  }

  public function getAttackChance() {
    return $this->attack_chance;
  }

  public function setDamage($value) {
    $this->damage = max(0, min($this->strength, intval($value)) );
    $stm = $this->db->prepare("UPDATE animals SET damage = :damage WHERE id = :id");
    $stm->bindInt("damage", $this->damage);
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }

  public function getLocation() {
    return $this->location;
  }
}
