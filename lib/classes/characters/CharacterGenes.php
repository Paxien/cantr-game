<?php

class CharacterGenes
{

  private $charId;
  private $db;

  public function __construct($char, Db $db) {
    if ($char instanceof Character) {
      $this->charId = $char->getId();
    }
    $this->charId = intval($char);
    $this->db = $db;
  }

  public function getExistingTypeIds()
  {
    return $this->db->query("SELECT id AS cnt FROM state_types")->fetchScalars();
  }

  public function readAll()
  {
    $genes = [];
    foreach ($this->getExistingTypeIds() as $geneType) {
      $genes[$geneType] = $this->read($geneType);
    }
    return $genes;
  }

  public function read($geneType) {
    $stm = $this->db->prepare("SELECT value FROM genes WHERE person = :charId AND type = :gene");
    $stm->bindInt("charId", $this->charId);
    $stm->bindInt("gene", $geneType);
    $value = $stm->executeScalar();
    if ($value !== null) {
      return $value;
    }
    return $this->generate($geneType); // return generated value if not already set in database
  }

  public function write($geneType, $value)
  {
    $stm = $this->db->prepare("INSERT INTO genes (person,type,value) VALUES (:charId, :state, :value)");
    $stm->bindInt("charId", $this->charId);
    $stm->bindInt("state", $geneType);
    $stm->bindInt("value", $value);
    $stm->execute();
  }

  public function generate($geneType)
  {
    $stm = $this->db->prepare("SELECT rand_minimum AS minimum, rand_maximum AS maximum FROM state_types WHERE id = :gene LIMIT 1");
    $stm->bindInt("gene", $geneType);
    $stm->execute();
    $boundary = $stm->fetch();
    
    $value = mt_rand($boundary->minimum, $boundary->maximum); // create random gene value in boundaries allowed for this type
    $this->write($geneType, $value); // save them

    return $value;
  }

  public function inheritFrom($parent1, $parent2)
  {
    if ($parent1 instanceof Character) {
      $parent1 = $parent1->getId();
    }
    if ($parent2 instanceof Character) {
      $parent2 = $parent2->getId();
    }

    $parentGenes1 = new CharacterGenes($parent1, $this->db);
    $parentGenes2 = new CharacterGenes($parent2, $this->db);
    $genes1 = $parentGenes1->readAll();
    $genes2 = $parentGenes2->readAll();

    foreach ($this->getExistingTypeIds() as $geneType) {

      //fetch the max value from the state_type table
      $stm = $this->db->prepare("SELECT rand_maximum FROM state_types WHERE id = :gene");
      $stm->bindInt("gene", $geneType);
      $maxValue = $stm->executeScalar();

      $dice = mt_rand() % 100;
      if ($dice >= 55) { // there's 45% chance to inherit a specific gene from one of "parents" and 10% to generate it randomly
        $value = $genes1[$geneType];
        $this->write($geneType, min($value, $maxValue));
      } elseif ($dice >= 10) {
        $value = $genes2[$geneType];
        $this->write($geneType, min($value, $maxValue));
      } else {
        $this->generate($geneType);
      }
    }
  }
} 
