<?php

class UseForProject
{

  private $char;
  private $project;
  
  public function __construct(Character $char, Project $project)
  {
    $this->char = $char;
    $this->project = $project;
  }

  public function getRawNeeded($rawName)
  {
    $neededAmount = 0;
    $reqLeft = Parser::rulesToArray($this->project->getReqLeft());
    if (array_key_exists("raws", $reqLeft)) {
      $rawsLeft = Parser::rulesToArray($reqLeft['raws'], ",>");
      if (array_key_exists($rawName, $rawsLeft)) {
        $neededAmount = $rawsLeft[$rawName];
      }
    }
    return $neededAmount;
  }

  public function useRaw(CObject $object, $amount) {

    if ($object->getType() != ObjectConstants::TYPE_RAW) {
      throw new DisallowedActionException("Should never happen! Trying to use ". $object->getId() . " as a raw");
    }
    
    $rawName = ObjectHandler::getRawNameFromId($object->getTypeid());
    
    $neededAmount = $this->getRawNeeded($rawName);

    if ($amount > $neededAmount) {
      $rawName = str_replace(" ", "_", $rawName);
      throw new DisallowedActionException("error_project_use_too_much MATERIAL=$rawName");
    }

    $inInventory = $this->char->hasInInventory($object);
    $inSameLocation = $this->char->isInSameLocationAs($object);
    if (!($inInventory || $inSameLocation)) {
      throw new DisallowedActionException("error_too_far_away");
    }

    $projectInSameLoc = $this->char->isInSameLocationAs($this->project);
    if (!$projectInSameLoc) {
      throw new DisallowedActionException("error_too_far_away");
    }

    
    if ($amount > $object->getWeight()) {
      $rawName = str_replace(" ", "_", $rawName);
      throw new DisallowedActionException("error_use_more_than_have MATERIAL=$rawName");
    }

    if (!Validation::isPositiveInt($amount)) {
      throw new DisallowedActionException("error_use_amount_invalid");
    }

    if ($inInventory) {
      $successful = ObjectHandler::rawToPerson($object->getPerson(), $object->getTypeid(), -1 * $amount);
    } else {
      $successful = ObjectHandler::rawToLocation($object->getLocation(), $object->getTypeid(), -1 * $amount);
    }
    
    if (!$successful) {
      throw new IllegalStateException("Somebody used the same resources twice!".
      " objid: ". $object->getId() .", charId: ". $this->char->getId() .", location: ". $object->getLocation());
    }

    $reqLeft = Parser::rulesToArray($this->project->getReqLeft());
    $rawsLeft = Parser::rulesToArray($reqLeft['raws'], ",>");
    
    $rawsLeft[$rawName] -= $amount;
    
    $rawsLeftRules = Parser::arrayToRules($rawsLeft, ",>");
    $reqLeft["raws"] = $rawsLeftRules;
    $reqLeftRules = Parser::arrayToRules($reqLeft);
    
    $this->project->setReqLeft($reqLeftRules);
    $this->project->setWeight($this->project->getWeight() + $amount);
    $this->project->saveInDb();

    $projectName = urlencode($this->project->getName());
    $material = str_replace(" ", "_", $rawName);

    if ($inInventory) {
      $actorEventId = 85;
      $watcherEventId = 86;
    } else {
      $actorEventId = 317;
      $watcherEventId = 318;
    }

    Event::createPersonalEvent($actorEventId,"MATERIAL=$material AMOUNT=$amount PROJECT=$projectName PROJID=".
      $this->project->getId(), $this->char->getId());
    Event::createEventInLocation($watcherEventId, "MATERIAL=$material PROJECT=$projectName ACTOR=".
      $this->char->getId() ." PROJID=". $this->project->getId(),
        $this->char->getLocation(), Event::RANGE_SAME_LOCATION, array($this->char->getId()));
    
  }

}
