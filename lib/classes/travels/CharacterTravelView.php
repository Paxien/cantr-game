<?php

class CharacterTravelView
{
  private $subject;
  private $observer;

  public function __construct(Character $subject, Character $observer)
  {
    $this->subject = $subject;
    $this->observer = $observer;
  }

  public function getInterpretedDescription()
  {
    $tag = TagBuilder::forText($this->getDescription())->allowHtml(false)
      ->observedBy($this->observer)->language($this->observer->getLanguage())->build();
    return $tag->interpret();
  }

  public function getDescription()
  {
    $otherCharTravelling = false;
    $travelInfo = array();

    try {
      $otherCharTravel = Travel::loadByParticipant($this->subject);
      $otherCharTravelling = true;
      if ($otherCharTravel->isVehicle()) {
        $travelInfo["type"] = "travelling";
      } else {
        $travelInfo["type"] = "walking";
      }
    } catch (InvalidArgumentException $e) {}

    $travelInfo["isTravelling"] = $otherCharTravelling;

    $ownCharTravelling = false;

    try {
      $ownCharTravel = Travel::loadByParticipant($this->observer);
      $ownCharTravelling = true;
    } catch (InvalidArgumentException $e) {}

    if ($otherCharTravelling) {
      import_lib("func.getdirection.inc.php");

      $connection = Connection::loadById($otherCharTravel->getConnectionId());
      $direction = $connection->getDirectionFromLocation($otherCharTravel->getStart());
      $travelInfo["direction"] = getdirectionname($direction);

      if ($ownCharTravelling && ($this->subject->getId() != $this->observer->getId())) {
        $ownCharSpeed = $ownCharTravel->getSpeed();
        $otherCharSpeed = $otherCharTravel->getSpeed();
        $travelInfo["ownSpeed"] = $ownCharSpeed;
        $travelInfo["hisSpeed"] = $otherCharSpeed;

        if ($otherCharTravel->getDestination() == $ownCharTravel->getDestination()) {
          $difference = $ownCharTravel->getFractionLeft() - $otherCharTravel->getFractionLeft();
        } else {
          $difference = $ownCharTravel->getFractionLeft() - (1 - $otherCharTravel->getFractionLeft());
        }
        $travelInfo["difference"] = $difference;

        if ($otherCharTravel->getSpeed() && $ownCharTravel->getSpeed()) {
          $travelInfo["isMoving"] = true;
          $travelInfo["isSameDirection"] = $otherCharTravel->getDestination() == $ownCharTravel->getDestination();
        }
      }

      if ($this->subject->isMale()) {
        $he = "<CANTR REPLACE NAME=char_desc_he>";
      } else {
        $he = "<CANTR REPLACE NAME=char_desc_she>";
      }

      $languageId = $this->observer->getLanguage();
      $smarty = new CantrSmarty();
      $smarty->assign("he", $he);
      $smarty->assign("travel", $travelInfo);
      $smarty->assign("lookingAtYourself", $this->subject->getId() == $this->observer->getId());
      ob_start();
      $smarty->displayLang("template.travel.tpl", LanguageConstants::$LANGUAGE[$languageId]["lang_abr"]);
      return ob_get_clean();
    }
    return "";
  }
}
