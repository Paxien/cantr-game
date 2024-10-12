<?php

function getdirection($x1,$y1,$x2,$y2) {

  $y_diff = $y2 - $y1;
  $x_diff = $x2 - $x1;

  if ($y_diff == 0) {

    if ($x_diff >= 0) {

      $alpha = 90;
    } else {
 
      $alpha = 270;
    }
  } else {

    if ($y_diff > 0) {
      
      $alpha = rad2deg(atan($x_diff/$y_diff));
    } else {

      $alpha = 180 + rad2deg(atan($x_diff/$y_diff));
    }
  }

  $degree = (360 + round(90 - $alpha)) % 360;

  return $degree;
}

/**
 * @deprecated use MapUtil::getDirectionTag instead
 */
function getdirectionname($degree) {
  return "<CANTR REPLACE NAME=". getdirectionrawname($degree) .">";
}

/**
 * @deprecated use MapUtil::getDirectionTagName instead
 */
function getdirectionrawname($degree) {

  $degree = floor($degree);

  while ($degree < 0) { $degree = $degree + 360; }
  if ($degree > 360) { $degree = $degree % 360; }

  if ($degree <= 11) { $name = "wind_region_n"; }
  if (($degree > 11)  AND ($degree <= 34))  { $name = "wind_region_nne"; }
  if (($degree > 34)  AND ($degree <= 56))  { $name = "wind_region_ne"; }
  if (($degree > 56)  AND ($degree <= 79))  { $name = "wind_region_ene"; }
  if (($degree > 79)  AND ($degree <= 101)) { $name = "wind_region_e"; }
  if (($degree > 101) AND ($degree <= 124)) { $name = "wind_region_ese"; }
  if (($degree > 124) AND ($degree <= 146)) { $name = "wind_region_se"; }
  if (($degree > 146) AND ($degree <= 169)) { $name = "wind_region_sse"; }
  if (($degree > 169) AND ($degree <= 191)) { $name = "wind_region_s"; }
  if (($degree > 191) AND ($degree <= 214)) { $name = "wind_region_ssw"; }
  if (($degree > 214) AND ($degree <= 236)) { $name = "wind_region_sw"; }
  if (($degree > 236) AND ($degree <= 259)) { $name = "wind_region_wsw"; }
  if (($degree > 259) AND ($degree <= 281)) { $name = "wind_region_w"; }
  if (($degree > 281) AND ($degree <= 304)) { $name = "wind_region_wnw"; }
  if (($degree > 304) AND ($degree <= 326)) { $name = "wind_region_nw"; }
  if (($degree > 326) AND ($degree <= 349)) { $name = "wind_region_nnw"; }
  if (($degree > 349) AND ($degree <= 360)) { $name = "wind_region_n"; }

  return $name;
}

/**
 * @deprecated use MapUtil::getDistanceTagName instead
 */
function getdistancerawname($distance) {
  if ($distance <= 10) {
    return "right_next_to";
  } elseif ($distance <= 21) {
    return "very_close";
  } elseif ($distance <= 30) {
    return "nearby";
  } elseif ($distance <= 70) {
    return "fairly_close_by";
  } elseif ($distance <= 100) {
    return "in_the_distance";
  } elseif ($distance <= 150) {
    return "long_distance_away";
  } else {
    return "very_far_away";
  }
}

