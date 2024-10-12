<?php

$object_id = HTTPContext::getInteger('object_id');

try {
  $crockery = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_object_not_found");
}

if (!$char->hasWithinReach($crockery)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

$link = "index.php?page=ingest_all&data=yes&object_id=$object_id";
show_title("<CANTR REPLACE NAME=ingest_all_old_title>");
echo '<div class="page">';
echo "<CANTR REPLACE NAME=ingest_all_old_text OBJECT=$object_id>";
echo "<br><br>";
echo '<div class="centered"><a href="index.php?page=char.inventory">';
echo '<img src="'. _IMAGES .'/button_back2.gif" title="<CANTR REPLACE NAME=back_to_previous>">';
echo '</a>';
echo "<a href='$link'> ";
echo '<img src="'. _IMAGES .'/button_forward2.gif">';
echo "</a></div>";
echo "</div>";

