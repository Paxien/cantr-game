<?php

// Brian Kernighan's way of counting bits
// see: http://graphics.stanford.edu/~seander/bithacks.html#CountBitsSetKernighan

function count_bits($number) {

  $c = 0;

  for ($c = 0; $number > 0; $c++)
    $number &= $number - 1;

  return $c;
}

function display_bits($number, $bits) {

  $s = "";

  for ($i = 0; $i < $bits; $i++) {

    $s = ($number & 1 ? "1" : "0") . $s;
    $number >>= 1;
  }
    
  return $s;
}

?>