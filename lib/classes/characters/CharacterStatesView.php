<?php

class CharacterStatesView
{
  private $subject;
  private $observer;

  public function __construct(Character $subject, Character $observer)
  {
    $this->subject = $subject;
    $this->observer = $observer;
  }

  public function getInterpretedStateDescriptions()
  {
    $descriptions = $this->getStateDescriptions();
    foreach ($descriptions as &$desc) {
      $tag = TagBuilder::forText($desc)->allowHtml(false)
        ->observedBy($this->observer)->language($this->observer->getLanguage())->build();
      $desc = $tag->interpret();
    }
    return $descriptions;
  }

  public function getStateDescriptions()
  {
    if ($this->subject->isMale()) {
      $he = '<CANTR REPLACE NAME=char_desc_he>';
    } else {
      $he = '<CANTR REPLACE NAME=char_desc_she>';
    }
    $descriptions = array();

    // hunger text
    $hungerLevel = $this->subject->getState(StateConstants::HUNGER);
    if ($hungerLevel >= CharacterConstants::DESC_HUNGER_3_MIN) {
      $descriptions["hunger"] = "$he <CANTR REPLACE NAME=char_desc_is_very_hungry>.";
    } elseif ($hungerLevel >= CharacterConstants::DESC_HUNGER_2_MIN) {
      $descriptions["hunger"] = "$he <CANTR REPLACE NAME=char_desc_is_emaciated>.";
    } elseif ($hungerLevel >= CharacterConstants::DESC_HUNGER_1_MIN) {
      $descriptions["hunger"] = "$he <CANTR REPLACE NAME=char_desc_is_starving>.";
    }

    // drunkenness text
    $drunkennessLevel = $this->subject->getState(StateConstants::DRUNKENNESS);
    if ($drunkennessLevel >= CharacterConstants::PASSOUT_LIMIT) {
      $descriptions["drunkenness"] = "$he <CANTR REPLACE NAME=char_desc_is_unconscious>.";
    } elseif ($drunkennessLevel >= CharacterConstants::DESC_DRUNK_5_MIN) {
      $descriptions["drunkenness"] = "$he <CANTR REPLACE NAME=char_desc_is_falling_down_drunk>.";
    } elseif ($drunkennessLevel >= CharacterConstants::DESC_DRUNK_4_MIN) {
      $descriptions["drunkenness"] = "$he <CANTR REPLACE NAME=char_desc_is_very_drunk>.";
    } elseif ($drunkennessLevel >= CharacterConstants::DESC_DRUNK_3_MIN) {
      $descriptions["drunkenness"] = "$he <CANTR REPLACE NAME=char_desc_is_drunk>.";
    } elseif ($drunkennessLevel >= CharacterConstants::DESC_DRUNK_2_MIN) {
      $descriptions["drunkenness"] = "$he <CANTR REPLACE NAME=char_desc_is_half_drunk>.";
    } elseif ($drunkennessLevel >= CharacterConstants::DESC_DRUNK_1_MIN) {
      $descriptions["drunkenness"] = "$he <CANTR REPLACE NAME=char_desc_is_tipsy>.";
    }

    return $descriptions;
  }


} 
