<?php

if (Limitations::getLims($character, Limitations::TYPE_NOT_EAT_AFTER_VOMIT) > 0) {
  CError::throwRedirectTag("char.events", "error_vomit_after_vomit");
}

$stomach = CharacterStomach::ofCharacter($char);
if ($stomach->getStomachContentsWeight() > 0) {
  Limitations::addLim($character, Limitations::TYPE_NOT_EAT_AFTER_VOMIT, Limitations::dhmstoc(1,0,0,0));
  
  $stomach->purge();

  Event::create(247, "ACTOR=" . $char->getId())->nearCharacter($char)->andAdjacentLocations()->except($char)->show();
  Event::create(246, "")->forCharacter($char)->show();

  $char->alterState(StateConstants::TIREDNESS, 5000);
  $char->alterState(StateConstants::DRUNKENNESS, -4000);
}

redirect("char.events");
