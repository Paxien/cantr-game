<?php

abstract class Animal {
  public static function getFedTagFromValue($fness, $gender = "") {
    return "<CANTR REPLACE NAME=animal_level_". self::getFedTextFromValue($fness) ." GENDER=$gender>";
  }
  
  public static function getFedTextFromValue($fness) {
    if ($fness >= 8000)
      return "fattened";
    elseif ($fness >= 6000)
      return "well_fed";
    elseif ($fness >= 4000)
      return "fed";
    elseif ($fness >= 2500)
      return "hungry";
    else return "starving";
  }
  
  public static function breedingActionsArray ($app = "") {
    return array("milking".$app, "shearing".$app, "collecting".$app, "butchering".$app);
  }
}
