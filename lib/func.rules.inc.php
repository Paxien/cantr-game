<?php

function explodeRules ($rules, $separators) {
  //echo "[$rules]<br>";
  $rest = substr ($separators, 2);
  $lines = explode ($separators [0], $rules);
  $R = new stdClass();
  foreach ($lines as $line) if ($line != "") {
    //echo "($line)<br>";
    list ($var, $val) = explode ($separators [1], $line);
    if (is_numeric ($val))
      $R->$var = $val;
    elseif (!$val)
      $R->$var = true;
    elseif ($rest) {
      $val = explodeRules ($val, $rest);
      $R->$var = $val;
    }
  }
  return $R;
}

function explodeBuildReq ($rules) {
  return explodeRules ($rules, ";:,>");
}

//function checking that object with some ID should have access to some action (by buttons)
//in his actually position. it prevents users to run actions for items that is not allowed.
function objectHaveAccessToAction( $object_id, $actionname ) {
  $db = Db::get();
  $stm = $db->prepare("SELECT ot.id as objecttype, obj.location as objectlocation, loc.type as locationtype
    FROM objects as obj INNER JOIN objecttypes ot ON ot.id = obj.type
    LEFT JOIN locations loc ON loc.id = obj.location WHERE obj.id = :objectId");
  $stm->bindInt("objectId", $object_id);
  $stm->execute();
  $data = $stm->fetchObject();

  $inInventory = ( $data->objectlocation == 0 );

  if ($inInventory) {
    $columnName  = "show_instructions_inventory";
  } else {
    $columnName  = "show_instructions_outside";
  }
  $stm = $db->prepare("SELECT `$columnName` FROM objecttypes WHERE id = :id");
  $stm->bindInt("id", $data->objecttype);
  $columnData = $stm->executeScalar();
  return strpos( $columnData, $actionname ) !== false;
}
