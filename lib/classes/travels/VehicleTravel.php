<?php

class VehicleTravel extends Travel
{

  /** @var LandVehicle */
  private $vehicle;

  public function __construct(LandVehicle $vehicle, Db $db)
  {
    parent::__construct($db);
    $this->vehicle = $vehicle;
  }

  /**
   * @return LandVehicle
   */
  public function getVehicle()
  {
    return $this->vehicle;
  }

  public function getMaxSpeed()
  {
    $speed = $this->vehicle->getMaxSpeed($this->getConnection());
    $speed /= TravelConstants::TURNS_PER_DAY;
    return $speed;
  }

  protected function specificDeparture()
  {
    $this->reportDepartureOfVehicle();
    $this->vehicle->setRegion(0);
  }

  private function reportDepartureOfVehicle()
  {
    $roadTypeName = $this->getConnectionTypeNameTag();
    $fromName = urlencode("<CANTR LOCNAME ID=$this->start>");
    $destName = urlencode("<CANTR LOCNAME ID=$this->destination>");
    $vehicleId = $this->vehicle->getId();
    $vehName = urlencode("<CANTR LOCNAME ID=$vehicleId>");

    Event::createEventInLocation(25, "VEHICLE=$vehName PLACE=$fromName ROADNAME=$roadTypeName DESTINATION=$destName",
      $this->start, Event::RANGE_SAME_LOCATION);

    Event::createEventInLocation(24, "VEHICLE=$vehName PLACE=$fromName ROADNAME=$roadTypeName DESTINATION=$destName",
      $this->vehicle->getId(), Event::RANGE_SAME_LOCATION);
  }

  public function getSpeed()
  {
    return $this->vehicle->getSpeed($this->getWantedSpeed(), $this->getMaxSpeed());
  }

  public function isVehicle()
  {
    return true;
  }

  protected function preArrival()
  {
    $arrivalLocation = $this->vehicle->getPreferredArrivalSublocationOverride($this->destination);
    $this->vehicle->setRegion($arrivalLocation);

    $roadTypeName = $this->getConnectionTypeNameTag();

    $destName = urlencode("<CANTR LOCNAME ID=$this->destination>");
    $startName = urlencode("<CANTR LOCNAME ID=$this->start>");

    $vehicleId = $this->vehicle->getId();
    $vehicleName = urlencode("<CANTR LOCNAME ID=$vehicleId>");

    if ($arrivalLocation == $this->destination) {
      Event::create(65, "PLACE=$destName")->inLocation($vehicleId)->show();
      Event::create(67, "VEHICLE=$vehicleName DESTINATION=$destName ROAD=$roadTypeName ORIGIN=$startName")
        ->inLocation($this->destination)->show();
    } else {
      $arrivalLocationName = urlencode("<CANTR LOCNAME ID=$arrivalLocation>");
      Event::create(377, "BUILDING=$arrivalLocationName LOCATION=$destName")->inLocation($vehicleId)->show();
      $arrivalEvent = Event::create(378, "VEHICLE=$vehicleName DESTINATION=$destName BUILDING=$arrivalLocationName ROAD=$roadTypeName ORIGIN=$startName");
      $arrivalEvent->inLocation($arrivalLocation)->show();
      $arrivalEvent->inLocation($this->destination)->show();
    }
  }

  protected function getConnectionTypeNameTag()
  {

    $vehicleType = $this->getVehicle()->getVehicleType();

    $name = $this->getConnection()->getConnectionTypePartFor($vehicleType)->getType()->getName();
    return "road_$name";
  }

  protected function getConnectionUsedToContinueTravel(Connection $connection, Travel $travel)
  {
    return $this->vehicle->travelContinuation($connection, $travel);
  }


  protected function updatePosition()
  {
    $pos = $this->getPos();
    $this->vehicle->setCoordinates($pos["x"], $pos["y"]);
  }

  /**
   * @return boolean true if this travel type can span for more than one connection.
   */
  protected function canTravelMultipleLocations()
  {
    return $this->vehicle->canTravelMultipleLocations();
  }

  protected function notifyLocationPassing($from, $by, $to)
  {
    {
      Event::create(353, "PASSED=$by DEST=$to")->inLocation($this->vehicle->getLocation())->show();
      Event::create(354, "FROM=$from DEST=$to VEH=" . $this->vehicle->getId())->
      inLocation(Location::loadById($by))->show();
    }
  }

  protected function notifyTurnAround($actorId)
  {
    Event::createEventInLocation(20, "", $this->vehicle->getId(), Event::RANGE_SAME_LOCATION);
    $turnLoc = new char_location($actorId);
    $chars = $turnLoc->chars_near(_PEOPLE_NEAR); // get all chars near

    $stm = $this->db->prepareWithIntList("SELECT id FROM chars WHERE id IN (:ids) AND location != :locationId", [
      "ids" => $chars,
    ]);
    $stm->bindInt("locationId", $this->vehicle->getId());
    $stm->execute();
    $chars = $stm->fetchScalars();

    Event::create(21, "ACTOR=$actorId")->forCharacters($chars)->show();
  }

  protected function getPersonColumnValue()
  {
    return $this->vehicle->getId();
  }

  protected function getTypeColumnValue()
  {
    return $this->vehicle->getVehicleType();
  }

  protected function getVehicleId()
  {
    return $this->vehicle->getId();
  }

  protected function postSaveInDb()
  {
    $this->vehicle->saveInDb();
  }

  protected function getResultantSpeed($possibleToTravel)
  {
    $toGo = min($possibleToTravel, $this->left);
    // if there's less left than speed then less fuel will be needed
    $speed = $this->vehicle->getSpeed($toGo, $this->getMaxSpeed());
    $this->vehicle->reduceFuel($speed, $toGo);
    return $speed;
  }

  public function getParticipatingCharacterIds()
  {
    $stm = $this->db->prepare("SELECT id FROM chars WHERE location = :locationId AND status = :active");
    $stm->bindInt("locationId", $this->vehicle->getId());
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();
    return $stm->fetchScalars();
  }
}