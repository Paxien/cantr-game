<?php

$db = Db::get();
$stm = $db->prepare("SELECT target FROM bookmark_whispering
  WHERE owner = :charId ORDER BY id");
$stm->bindInt("charId", $char->getId());
$stm->execute();
$targets = $stm->fetchScalars();

$charLoc = new char_location($char->getId());

$targetPairs = array();
foreach ($targets as $target) {
  $tag = new Tag("<CANTR CHARNAME ID={$target}>", false);
  $tag = new Tag($tag->interpret(), false);
  $targetPairs[] = array(
    "id" => $target,
    "name" => $tag->interpret(),
    "near" => $charLoc->char_isnear($target)
  );
}

echo json_encode(array("targets" => $targetPairs));
