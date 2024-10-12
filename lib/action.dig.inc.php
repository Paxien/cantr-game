<?php

// SANTITIZE INPUT
$amount = HTTPContext::getInteger('amount');
$repeat = HTTPContext::getInteger('repeat');

$rawtype = HTTPContext::getInteger('rawtype');

$gathering = new RawsGathering($char, $rawtype, $amount, $repeat, Db::get());

if ($gathering->validate()) {
  $gathering->dig();

  redirect("char");
  exit;
} else {
  CError::throwRedirect("char.description", $gathering->error);
}
