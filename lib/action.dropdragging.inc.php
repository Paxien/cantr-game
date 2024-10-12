<?php

try {
  $dragging = Dragging::loadByDragger($char->getId());
  $dragging->removeDragger($char->getId());
  $dragging->saveInDb();
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_not_dragging");
}

redirect("char");
