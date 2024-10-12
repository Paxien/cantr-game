<?php

class DomesticatedAnimalPack extends AnimalPack {
  
  protected $fullness;
  protected $specifics;
  
  protected $type_details;
  protected $food_type;
  protected $food_amount;
  
  public function __construct($pack_id, $type_fetch = null) {
    parent::__construct($pack_id, $type_fetch);
    if ($this->ok) {
      $stm = $this->db->prepare("SELECT fullness, specifics FROM animal_domesticated WHERE from_animal = :id");
      $stm->bindInt("id", $this->id);
      $stm->execute();
      if ($dom_info = $stm->fetchObject()) {
        $this->fullness = $dom_info->fullness;
        $this->specifics = $dom_info->specifics;
      }
      else {
        $this->ok = false;
      }
    }
  }
  
  protected function removePack() {
    $stm = $this->db->prepare("DELETE FROM animals WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $this->id);
    $stm->execute();
    $stm = $this->db->prepare("DELETE FROM animal_domesticated WHERE from_animal = :id LIMIT 1");
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }
  
  public function getDomesticationActions() {
    $actions = array();
    
    if (strpos($this->specifics, "milking") !== false) {
      $actions['milking'] = true;
    }
    if (strpos($this->specifics, "shearing") !== false) {
      $actions['shearing'] = true;
    }
    if (strpos($this->specifics, "collecting") !== false) {
      $actions['collecting'] = true;
    }
    if ($this->damage > 0) {
      $actions['healing'] = true;
    }
    
    $actions['separating'] = true;
    
    return $actions;
  }
  
  public function get_animal_type($type_fetch = null) {
    $stm = $this->db->prepare("SELECT type_details, food_type, food_amount FROM animal_domesticated_types WHERE of_animal_type = :animalType");
    $stm->bindInt("animalType", $this->type);
    $stm->execute();
    if ($fetch = $stm->fetchObject()) {
      $this->type_details = $fetch->type_details;
      $this->food_type = $fetch->food_type;
      $this->food_amount = $fetch->food_amount;
      parent::get_animal_type($type_fetch);
    }
    else {
      $this->ok = false;
    }
  }
  
  public function incorporateAnimalObject($animalObject) {
    ($animalObject->getType() == $this->type) or die("incorporateAnimalObject: can't merge animals of different types!");

    $animalAnnihilated = $animalObject->annihilate();
    if (!$animalAnnihilated) { // race condition security
      return false;
    }
    
    $this->fullness = round( ( $this->fullness * $this->number + $animalObject->getFullness() ) / ($this->number + 1) );
    
    $packSpecifics = Parser::rulesToArray($this->specifics);
    $objectSpecifics = Parser::rulesToArray( $animalObject->getSpecificsString() );
    
    foreach ( array("milking", "shearing", "collecting") as $atype ) {
      $ruleName = $atype.'_raws';
      if ( $packSpecifics[$ruleName] ) {
        $packSpecifics[$ruleName] = $this->averageSpecifics($packSpecifics[$ruleName], $this->number, $objectSpecifics[$ruleName], 1);
      }
    }
    
    $this->number++;
    $this->specifics = Parser::arrayToRules($packSpecifics);
    $stm = $this->db->prepare("UPDATE animals SET number = :number WHERE id = :id");
    $stm->bindInt("number", $this->number);
    $stm->bindInt("id", $this->id);
    $stm->execute();
    $stm = $this->db->prepare("UPDATE animal_domesticated SET fullness = :fullness, specifics = :specifics WHERE from_animal = :id");
    $stm->bindInt("fullness", $this->fullness);
    $stm->bindStr("specifics", $this->specifics);
    $stm->bindInt("id", $this->id);
    $stm->execute();
    
    return true;
  }
  
  /*
    Same animal type = obliged to have same keys in array
  */
  private function averageSpecifics($f_spec, $f_num, $s_spec, $s_num) {
    $f_rawTab = Parser::rulesToArray($f_spec, ",>");
    $s_rawTab = Parser::rulesToArray($s_spec, ",>");
    $result = array();
    
    foreach ($f_rawTab as $rawName => $nothing) {
      $result[$rawName] = round( ($f_rawTab[$rawName] * $f_num + $s_rawTab[$rawName] * $s_num) / ( $f_num + $s_num ) );
    }
    return Parser::arrayToRules($result, ",>");
  }
  
  public function getSpecificsString() {
    return $this->specifics;
  }
  
  public function setSpecifics($string) {
    $this->specifics = $string;
    $stm = $this->db->prepare("UPDATE `animal_domesticated` SET `specifics` = :specifics WHERE `from_animal` = :id");
    $stm->bindStr("specifics", $this->specifics);
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }
  
  public function getFullness() {
    return $this->fullness;
  }
  
  public function setFullness($value) {
    $value = intval($value);
    $this->fullness = max(0, min( _SCALESIZE_GSS, $value ) );
    $stm = $this->db->prepare("UPDATE `animal_domesticated` SET `fullness` = :fullness WHERE `from_animal` = :id");
    $stm->bindInt("fullness", $this->fullness);
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }
  
  public function isDomesticated() {
    return true;
  }
  
  public function getFoodTypes() {
    $types = array();
    if ($this->food_type & 1) $types['hay'] = AnimalConstants::FODDER_HAY_ID;
    if ($this->food_type & 2) $types['vegetable'] = AnimalConstants::FODDER_VEGETABLES_ID;
    if ($this->food_type & 4) $types['meat'] = AnimalConstants::FODDER_MEAT_ID;
    return $types;
  }
  
  public function getFoodAmount() {
    return $this->food_amount;
  }
  
  public function setNumber($value) {
    $this->number = intval($value);
    $stm = $this->db->prepare( "UPDATE animals SET number = :number WHERE id = :id" );
    $stm->bindInt("number", $this->number);
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }
  
  public function getTypeDetailsString() {
    return $this->type_details;
  }
}
