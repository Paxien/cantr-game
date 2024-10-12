<?php

function getlisteners($freq, $x_start, $y_start, $distance)
{
  $db = Db::get();

  function GetReceivers(&$searchObj, $x, $y, $distance, $allowRepeaters, Db $db)
  {
    $stm = $db->prepare("
      SELECT item, type, repeater, x, y
      FROM radios
      WHERE repeater != 2 AND
        ABS(x - :x) <= :distance1 AND ABS(y - :y) <= :distance2
        AND frequency = :frequency");
    $stm->bindFloat("x", $x);
    $stm->bindFloat("y", $y);
    $stm->bindFloat("distance1", $distance);
    $stm->bindFloat("distance2", $distance);
    $stm->bindInt("frequency", $searchObj->freq);
    $stm->execute();
    while (list ($item, $objecttype, $type, $nx, $ny) = $stm->fetch(PDO::FETCH_NUM)) {
      if (check_range($nx, $ny, $x, $y, $distance)) {
        if ($type == 0) { // 0 - receiver
          $searchObj->listeners[$item] = 1;
        } else { // 1 - repeater
          if ($allowRepeaters && !$searchObj->repeaters[$item]) {
            $searchObj->repeaters[$item] = 1;
            GetReceivers($searchObj, $nx, $ny, $searchObj->types->ranges[$objecttype], false, $db);
          }
        }
      }
    }
  }

  $radioTypes = CObject::GetRadioTypes();

  $searchObj = new stdClass();
  $searchObj->freq = $freq;
  $searchObj->types = $radioTypes;

  GetReceivers($searchObj, $x_start, $y_start, $distance, true, $db);

  $result = array();
  if ($searchObj->listeners) {
    foreach ($searchObj->listeners as $id => $dummy) {
      $result[] = $id;
    }
  }

  return $result;
}

function check_range($x1, $y1, $x2, $y2, $range)
{
  return $range >= Measure::distance($x1, $y1, $x2, $y2);
}

?>
