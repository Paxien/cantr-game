<?php

class WildAnimalPack extends AnimalPack {
  
  protected $domesticable_into = null;
  protected $tame_rules = null;
  
  public function __construct($pack_id, $type_fetch) {
    parent::__construct($pack_id, $type_fetch);
  }
  
  protected function removePack() {
    $stm = $this->db->prepare("DELETE FROM animals WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $this->id);
    $stm->execute();
  }
  
  public function get_animal_type($type_fetch = null) {
    if (!$type_fetch->name) {
      $stm = $this->db->prepare("SELECT * FROM animal_types WHERE id = :id");
      $stm->bindInt("id", $this->type);
      $stm->execute();
      $type_fetch = $stm->fetchObject();
    }
    $this->domesticable_into = $type_fetch->domesticable_into;
    parent::get_animal_type($type_fetch);
  }
  
  public function getDomesticationActions() {
    $actions = array();
    if ($this->domesticable_into > 0) {
      $actions['taming'] = true;
    }
    return $actions;
  }
  
  public function getDomesticableInto() {
    return $this->domesticable_into;
  }
  
  
  public function isDomesticated() {
    return false;
  }
  
  public function getTameRulesString() {
    if ($this->tame_rules == null) {
      if ( is_numeric($this->domesticable_into) ) {
        $stm = $this->db->prepare("SELECT tame_rules FROM animal_domesticated_types WHERE of_animal_type = :animalType");
        $stm->bindInt("animalType", $this->domesticable_into);
        $this->tame_rules = $stm->executeScalar();
      }
      else { // that animal isn't domesticable!
        return null;
      }
    }
    return $this->tame_rules;
  }
  
  public function getTameRulesArray($recursive = true) {
    $rulesArray = Parser::rulesToArray( $this->getTameRulesString() );
    
    if ($recursive && $rulesArray != null) { // to avoid foreach error
      foreach ($rulesArray as $key => &$value) {
        $value = Parser::rulesToArray($value, ",>");
      }
    }
    return $rulesArray;
  }
}
