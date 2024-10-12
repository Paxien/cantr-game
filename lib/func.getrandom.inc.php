<?php

function normal ($location, $std_dev_percent) {

  srand(make_seed());

  $std_dev = $location * $std_dev_percent;
  $r = sqrt (-2 * log (random_percent()));
  $theta = 2 * M_PI * random_percent();

  $result = $location + $std_dev * $r * cos ($theta);

  // Amounts can not be less than zero
  if ($result < 0) $result = normal ($location, $std_dev);

  // Amounts should stay within reasonable bounds  
  if ($result > (2 * $location)) $result = normal ($location, $std_dev);

  return $result;
}

function normal_percent ($location, $std_dev_percent) {

  srand(make_seed());

  $location /= 100;

  $std_dev = $std_dev_percent;

  $r = sqrt (-2 * log (random_percent()));
  $theta = 2 * M_PI * random_percent();

  $result = $location + $std_dev * $r * cos ($theta);

  // Percents must be within the range of 0.0 and 1.00
  if ($result < 0) $result = normal_percent (100*$location, $std_dev);
  if ($result > 1) $result = normal_percent (100*$location, $std_dev);

  return $result;
}

function random_percent () {

  return (mt_rand (1, 100000) / 100000);
}

function make_seed() {

  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}

function rand_round($number)
{
  $rand_val = mt_rand(0, 1000000) / 1000000;
  if ($rand_val <= $number - floor($number)) {
    return (int)ceil($number);
  } else {
    return (int)floor($number);
  }
}
