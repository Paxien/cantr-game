<?php

class FootTravel extends Travel
{
  /** @var  Character */
  private $traveler; // who is travelling, null if vehicle

  public function __construct(Character $traveler, Db $db)
  {
    parent::__construct($db);
    $this->traveler = $traveler;
  }

  /**
   * @return Character
   */
  public function getTraveler()
  {
    return $this->traveler;
  }

  public function getMaxSpeed()
  {
    return $this->getMaxWalkingSpeed($this->traveler, $this->getConnection());
  }

  protected function specificDeparture()
  {
    $this->reportDepartureOfTraveler();
    $stm = $this->db->prepare("UPDATE chars SET location = 0 WHERE id = :charId");
    $stm->bindInt("charId", $this->traveler->getId());
    $stm->execute();
  }

  private function reportDepartureOfTraveler()
  {
    $roadTypeName = $this->getConnectionTypeNameTag();
    $fromName = urlencode("<CANTR LOCNAME ID=$this->start>");
    $destName = urlencode("<CANTR LOCNAME ID=$this->destination>");
    $travelerId = $this->traveler->getId();

    Event::createEventInLocation(23, "ACTOR=$travelerId PLACE=$fromName ROADNAME=$roadTypeName DESTINATION=$destName",
      $this->start, Event::RANGE_SAME_LOCATION, array($travelerId));
    Event::createPersonalEvent(22, "PLACE=$fromName ROADNAME=$roadTypeName DESTINATION=$destName", $travelerId);
  }


  public function getMaxWalkingSpeed(Character $traveler, Connection $connection)
  {
    if (!$connection->canBeMovedOn("walking")) {
      return 0;
    }
    $speed = _WALKING_SPEED - $traveler->getInventoryWeight() / _WALKING_WEIGHT_DELAY;
    $speed = min($speed, $connection->getConnectionTypePartFor("walking")->getType()->getSpeedLimit());
    $speed /= TravelConstants::TURNS_PER_DAY;
    return max($speed, 0);
  }


  /**
   * REAL speed affected by wantedSpeed.
   */
  public function getSpeed()
  {
    return $this->getWantedSpeed();
  }

  public function isVehicle()
  {
    return false;
  }

  protected function preArrival()
  {
    $this->traveler->setLocation($this->destination);
    $this->traveler->saveInDb();

    $travelerId = $this->traveler->getId();

    // notify arrive for traveler
    $roadTypeName = $this->getConnectionTypeNameTag();
    $destName = urlencode("<CANTR LOCNAME ID=$this->destination>");
    $startName = urlencode("<CANTR LOCNAME ID=$this->start>");
    Event::createPersonalEvent(65, "PLACE=$destName", $travelerId);

    $stm = $this->db->prepare("SELECT COUNT(*) FROM raws WHERE location = :locationId");
    $stm->bindInt("locationId", $this->destination);
    $rawtypesCount = $stm->executeScalar();
    $stm = $this->db->prepare("SELECT SUM(number) FROM animals WHERE location = :locationId");
    $stm->bindInt("locationId", $this->destination);
    $animalsCount = $stm->executeScalar();
    Event::createPersonalEvent(196, "LOCATION=$this->destination NUMRAW=$rawtypesCount NUMANIMALS=$animalsCount", $travelerId);

    $stm = $this->db->prepare("SELECT COUNT(*) FROM locations WHERE type IN (2,3,5) and region = :locationId");
    $stm->bindInt("locationId", $this->destination);
    $buildsVehsCount = $stm->executeScalar();
    Event::createPersonalEvent(194, "NUMBUILDINGS=$buildsVehsCount", $travelerId);

    $stm = $this->db->prepare("SELECT COUNT(*) FROM chars WHERE status = :active AND location = :locationId");
    $stm->bindInt("locationId", $this->destination);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $peopleCount = $stm->executeScalar();
    $peopleCount--;
    Event::createPersonalEvent(193, "NUMCHARS=$peopleCount", $travelerId);

    $stm = $this->db->prepare("SELECT COUNT(*) FROM objects WHERE location = :locationId");
    $stm->bindInt("locationId", $this->destination);
    $objectsCount = $stm->executeScalar();
    $stm = $this->db->prepare("SELECT COUNT(*) FROM objects WHERE location = :locationId AND type = 1");
    $stm->bindInt("locationId", $this->destination);
    $notesCount = $stm->executeScalar();
    Event::createPersonalEvent(192, "NUMOBJECTS=$objectsCount NUMNOTES=$notesCount", $travelerId);

    $projectsCount = Project::locatedIn($this->destination)->count();
    $stm = $this->db->prepare("SELECT count(*) FROM (SELECT DISTINCT project FROM chars
      WHERE location = :locationId AND status = :active AND project > 0) AS tab");
    $stm->bindInt("locationId", $this->destination);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $activeProjectsCount = $stm->executeScalar();
    Event::createPersonalEvent(191, "NUMPROJECTS=$projectsCount NUMACTIVE=$activeProjectsCount", $travelerId);

    // notify that traveler arrived for people in location
    Event::createEventInLocation(66, "ACTOR=$travelerId DESTINATION=$destName ROAD=$roadTypeName ORIGIN=$startName",
      $this->destination, Event::RANGE_SAME_LOCATION, array($travelerId));
  }

  protected function getConnectionTypeNameTag()
  {
    $vehicleType = "walking";

    $name = $this->getConnection()->getConnectionTypePartFor($vehicleType)->getType()->getName();
    return "road_$name";
  }

  private function canContinueTravel()
  {
    return false;
  }

  protected function updatePosition()
  {
    // do nothing, character position is not stored anywhere.
  }

  /**
   * @return boolean true if this travel type can span for more than one connection.
   */
  protected function canTravelMultipleLocations()
  {
    return false;
  }

  /**
   * @param Connection $connection
   * @param Travel $travel
   * @return Connection|null
   */
  protected function getConnectionUsedToContinueTravel(Connection $connection, Travel $travel)
  {
    return null;
  }

  protected function notifyLocationPassing($from, $by, $to)
  {
    // can never happen
  }

  protected function notifyTurnAround($actorId)
  {
    $actor = Character::loadById($actorId);
    Event::create(20, "")->forCharacter($actor)->show();

    Event::create(21, "ACTOR={$actor->getId()}")
      ->nearCharacter($actor)->andAdjacentLocations()->except($actor)->show();
  }

  protected function getPersonColumnValue()
  {
    return $this->traveler->getId();
  }

  protected function getTypeColumnValue()
  {
    return 0;
  }

  protected function getVehicleId()
  {
    return 0;
  }

  protected function postSaveInDb()
  {
    // intentionally do nothing
  }

  protected function getResultantSpeed($possibleToTravel)
  {
    return $possibleToTravel;
  }

  public function getParticipatingCharacterIds()
  {
    return [$this->traveler->getId()];
  }
}