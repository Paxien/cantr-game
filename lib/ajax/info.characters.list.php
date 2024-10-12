<?php

$charLoc = new char_location($char->getId());

$chars = $charLoc->chars_near(_PEOPLE_NEAR);

$charsData = array();
foreach ($chars as $charId) {
  $tag = new Tag("<CANTR CHARNAME ID=$charId>", false);
  $tag = new Tag($tag->interpret(), false);
  $charsData[] = array("id" => $charId, "name" => $tag->interpret());
}


echo json_encode(
  array(
    "characters" => $charsData
  )
);
