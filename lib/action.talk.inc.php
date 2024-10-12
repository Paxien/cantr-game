<?php

// SANITIZE INPUT
$to = HTTPContext::getInteger('to');
$message = $_REQUEST['message'];

// Create object about location of character; check whether 'victim' is indeed nearby

$char_loc = new char_location($char->getId());

if ($to != 0) {
  try {
    $toChar = Character::loadById($to);
    if (!$char->isNearTo($toChar)) {
      CError::throwRedirectTag("char.events", "error_person_not_here");
    }
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("char.events", "error_person_not_here");
  }
}

$message = TextFormat::withoutNewlines($message);

//check for ooc chatter for the ooc chatter report
$messageContentMatcher = MessageContentMatcher::newInstance()->withBasicPatterns()->withBracketPatterns();
if ($messageContentMatcher->patternMatches($message)) {
  $umessage = "Char $character says : $message";
  Report::saveInDb("ooc_chatter", $umessage);
}


$escMessage = htmlspecialchars($message);

// Report talking to people around

$escMessage = urlencode( $escMessage );

$drunkenness = $char->getState(StateConstants::DRUNKENNESS);
if ($to == 0) { // if the message is public
  $actorEventId = 5;
  $othersEventId = 4;

  if ($drunkenness >= CharacterConstants::DRUNKEN_SPEECH_MIN) {
    $actorEventId = 370;
    $othersEventId = 369;
  }
  Event::create($actorEventId, "MESSAGE=$escMessage")->forCharacter($char)->show();
  Event::create($othersEventId, "ACTOR=$character MESSAGE=$escMessage")->
    nearCharacter($char)->andAdjacentLocations()->except($char)->show();
} else { // if the message is private
  Event::create(2,  "ACTOR=$character MESSAGE=$escMessage")->forCharacter($toChar)->show();
  Event::create(3, "VICTIM=$to MESSAGE=$escMessage")->forCharacter($char)->show();

  // everybody near
  $peopleNear = $char_loc->chars_near(_PEOPLE_NEAR);
  $actors = array($char->getId(), $toChar->getId());
  $notAnActorPredicate = function ($chId) use ($actors) {
      return !in_array($chId, $actors);
  };

  $drunkennessOverhearAddition = ($drunkenness / _SCALESIZE_GSS * CharacterConstants::OVERHEAR_DRUNKENNESS_ADDITION);
  $overhearChance = CharacterConstants::OVERHEAR_BASE_CHANCE_PERCENT + $drunkennessOverhearAddition;
  $isOverheard = (mt_rand(0,100) < $overhearChance);
  // it rolls for if whisper was heard in general
  // then rolls chance of hearing for individuals later
  if ($isOverheard) {
    $haveOverheard = array_filter($peopleNear, function ($chId) { return mt_rand(0,100) < 15; });
    $haveOverheard = array_filter($haveOverheard, $notAnActorPredicate);

    Event::create(217, "ACTOR=$character VICTIM=$to MESSAGE=$escMessage")->
      forCharacters($haveOverheard)->show();
  } else {
    $haveOverheard = array(); // nobody has overheard
  }

  // people who haven't overheard
  $observers = array_diff($peopleNear, $haveOverheard);
  $observers = array_filter($observers, $notAnActorPredicate);
  Event::create(1, "ACTOR=$character VICTIM=$to")->
    forCharacters($observers)->show();
}

// parrot's repeating code
if ((mt_rand(0,100000)/100000) < AnimalConstants::PARROT_REPEAT_WORD_CHANCE) {
  $db = Db::get();
  $stm = $db->prepare("SELECT o.id FROM objects o INNER JOIN objecttypes ot ON ot.id = o.type
    WHERE o.person = :charId AND ot.rules LIKE '%parroting:yes%' AND
      ot.objectcategory = :category");
  $stm->bindInt("charId", $char->getId());
  $stm->bindInt("category", ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS);
  $parrotId = $stm->executeScalar();
  if (($parrotId !== null) && !Validation::hasCharactersToDescribeActions($message)) {
    $parrot = DomesticatedAnimalObject::loadById($parrotId);
    if (($parrot !== null) && $parrot->isLoyalTo($char)) {
      if (preg_match_all("/\b\S{3,15}?\b/u", $message, $matches, PREG_SET_ORDER)) {
        $match = $matches[array_rand($matches)][0];
        $match = ucfirst($match) . "!";
        if ((mt_rand(0,1000000)/1000000) < AnimalConstants::PARROT_REPEAT_DOUBLE_CHANCE) { // double word
          $match .= " ". $match;
        }
        $match = urlencode(htmlspecialchars($match));
        Event::create(355, "PARROT=". $parrot->getUniqueName() ." WORDS=$match")->
          nearCharacter($char)->andAdjacentLocations()->show();
      }
    }
  }
}

redirect("char");
