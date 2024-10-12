<?php

abstract class Travel {

  private $id;
  private $finished = false;

  /** @var int */
  protected $start;
  /** @var int */
  protected $destination;
  /** @var Connection */
  private $connection;

  private $speedPercent;

  private $alreadyTravelled = 0;
  protected $left;
  protected $needed;
  /** @var Db */
  protected $db;
  /** @var GlobalConfig */
  protected $globalConfig;

  // START INSTANTIATION

  // objects should be constructed through factory method
  protected function __construct(Db $db) {
    $this->db = $db;
    $this->globalConfig = new GlobalConfig($this->db);
  }

  public static function newInstance($subject, Connection $connection, Character $initiator, Db $db) {

    if ($subject instanceof LandVehicle) {
      $travel = new VehicleTravel($subject, $db);
      $fromLocationId = $travel->getVehicle()->getRegion();
    } elseif ($subject instanceof Character) {
      $travel = new FootTravel($subject, $db);
      $fromLocationId = $travel->getTraveler()->getLocation();
    } else {
      throw new InvalidArgumentException("specified object must be of class LandVehicle or Character");
    }

    $fromLocation = Location::loadById($fromLocationId);
    $accessibleRoadTypesProp = $fromLocation->getProperty("AccessibleRoadTypes");
    $rootStartLocation = $fromLocation->getRoot();

    if ($travel->isVehicle()) {
      $vehicleType = $travel->getVehicle()->getVehicleType();
    } else {
      $vehicleType = "walking"; // walking is a valid vehicle type
    }
    if (!$connection->canBeMovedOn($vehicleType, $accessibleRoadTypesProp)) {
      throw new DisallowedActionException("error_need_good_vechicle");
    }

    if (!in_array($rootStartLocation->getId(), array($connection->getStart(), $connection->getEnd()))) {
      throw new DisallowedActionException("error_not_at_start");
    }

    if ($travel->isVehicle()) { // will throw exception if error occurs
      $travel->getVehicle()->tryDrive($initiator);
    }

    // set start and destination of this travel
    $travel->connection = $connection;
    $travel->start = $rootStartLocation->getId();
    $travel->destination = $connection->getOppositeLocation($travel->start);

    if ($travel->getMaxSpeed() == 0) {
      throw new DisallowedActionException("error_speed_too_low");
    }

    // travel distance
    $travel->needed = $travel->getLengthNeeded();
    $travel->left = floatval($travel->needed);

    $travel->speedPercent = 100;
    $travel->departure();

    $travel->saveInDb();

    return $travel;
  }

  protected abstract function specificDeparture();

  public abstract function getMaxSpeed();

  private function departure() {
    $this->specificDeparture();

    $this->updateTravelHistory(false);
  }

  public function setWantedSpeed($wantedSpeed) {
    $this->speedPercent = min(100, round(100 * $wantedSpeed / $this->getMaxSpeed()));
  }

  public function getWantedSpeed() {
    return $this->getMaxSpeed() * $this->speedPercent / 100;
  }

  public static function loadByParticipant(Character $char, $db = null) {
    if ($char == null) {
      throw new InvalidArgumentException("undefined object of type Character");
    }
    if ($db === null) {
      $db = Db::get();
    }

    if ($char->getLocation() == 0) {
      $stm = $db->prepare("SELECT id FROM travels WHERE person = :charId AND type = 0");
      $stm->bindInt("charId", $char->getId());
      $travelId = $stm->executeScalar();
    } else {
      $stm = $db->prepare("SELECT id FROM travels WHERE person = :locationId AND type > 0");
      $stm->bindInt("locationId", $char->getLocation());
      $travelId = $stm->executeScalar();
    }
    return self::loadById($travelId, $db);
  }

  public static function loadById($travelId, Db $db) {
    if (!Validation::isPositiveInt($travelId)) {
      throw new InvalidArgumentException("travel id $travelId is not positive integer");
    }

    $stm = $db->prepare("SELECT * FROM travels WHERE id = :id");
    $stm->bindInt("id", $travelId);
    $stm->execute();
    $fetchedObject = $stm->fetchObject();
    return self::loadFromFetchObject($fetchedObject, $db);
  }

  public static function loadFromFetchObject(stdClass $fetchedObject, Db $db) {
    if ($fetchedObject->id == null) {
      throw new InvalidArgumentException("invalid fetched object");
    }
    if ($fetchedObject->type == 0) {
      $traveler = Character::loadById($fetchedObject->person);
      $travel = new FootTravel($traveler, $db);
    } else {
      $vehicle = LandVehicle::loadById($fetchedObject->person);
      if (!$vehicle->canMoveOnLand()) {// todo
        throw new InvalidArgumentException("vehicle " . $vehicle->getId() , " is not a land vehicle");
      }
      $travel = new VehicleTravel($vehicle, $db);

    }
    $travel->id = $fetchedObject->id;

    $travel->start = $fetchedObject->locfrom;
    $travel->destination = $fetchedObject->locdest;
    $travel->connection = Connection::loadById($fetchedObject->connection);

    $travel->speedPercent = $fetchedObject->speed_percent;

    $travel->left = floatval($fetchedObject->travleft);
    $travel->needed = $fetchedObject->travneeded;

    return $travel;
  }

  // END INSTANTIATION


  public function isFinished() {
    return $this->finished;
  }


  public function getPos() {
    $stm = $this->db->prepare("SELECT x, y FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $this->start);
    $stm->execute();
    $posStart = $stm->fetchObject();
    $stm = $this->db->prepare("SELECT x, y FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $this->destination);
    $stm->execute();
    $posDest = $stm->fetchObject();

    $part = 1 - $this->left / $this->needed;
    $posX = $posStart->x + $part * ($posDest->x - $posStart->x);
    $posY = $posStart->y + $part * ($posDest->y - $posStart->y);
    return array("x" => round($posX), "y" => round($posY));
  }

  public function makeProgress() {

    $continue = true;
    for ($i = 0; $continue && $i < 20; $i++) {
      $continue = $this->makeProgressIteration();
    }

    $this->saveInDb();
  }

  protected abstract function getResultantSpeed($possibleToTravel);

  public function makeProgressIteration()
  {
    // important: wantedSpeed is speed wanted by driver, $speed is actual speed affected by fuel
    $possibleToTravel = $this->getWantedSpeed() * $this->globalConfig->getTravelProgressRatio() - $this->alreadyTravelled;

    $speed = $this->getResultantSpeed($possibleToTravel);

    $this->alreadyTravelled += $speed;
    $this->left -= $speed;

    $this->updatePosition();

    if ($this->hasMovedToEndOfRoad()) {
      if ($this->successfulTravelContinuation()) {
        return true;
      } else {
        $this->arrive();
      }
    }
    return false;
  }

  private function hasMovedToEndOfRoad()
  {
    return ($this->speedPercent > 0) && ($this->left <= 0);
  }

  protected function setConnection(Connection $newConnection)
  {
    $this->connection = $newConnection;
  }

  private function getLengthNeeded()
  {
    return $this->connection->getLength();
  }

  /**
   * @return boolean true if this travel can span for more than one connection.
   */
  protected abstract function canTravelMultipleLocations();

  private function successfulTravelContinuation()
  {
    if ($this->canTravelMultipleLocations()) {
      return $this->travelContinuation();
    }
    return false;
  }

  /**
   * @param Connection $connection
   * @param Travel $travel
   * @return Connection|null connection in traveler's location that should be used to continue the travel
   */
  protected abstract function getConnectionUsedToContinueTravel(Connection $connection, Travel $travel);

  private function travelContinuation()
  {
    $newConnection = $this->getConnectionUsedToContinueTravel($this->getConnection(), $this);
    if ($newConnection != null) {
      $to = $newConnection->getOppositeLocation($this->destination);
      $this->notifyLocationPassing($this->start, $this->destination, $to);

      $this->setConnection($newConnection);
      $this->start = $this->destination;
      $this->destination = $this->getConnection()->getOppositeLocation($this->start);

      $this->left = $this->needed = $this->getLengthNeeded();

      return true;
    }

    return false;
  }

  protected abstract function updatePosition();

  protected abstract function notifyLocationPassing($from, $by, $to);

  protected abstract function preArrival();

  private function arrive() {
    $this->preArrival();

    $this->updateTravelHistory(true);
    $this->finished = true;
  }

  public function moveToTheCloserLocation() {
    if ($this->getFractionDone() <= 0.5) {
      $this->commitTurnAround();
    }
    $this->arrive();
  }

  public abstract function getParticipatingCharacterIds();

  protected function updateTravelHistory($arrive) {
    $arrive = $arrive ? 1 : 0;
    $charsToUpdate = $this->getParticipatingCharacterIds();

    $gameDate = GameDate::NOW();
    $loc = $arrive ? $this->destination : $this->start;
    foreach ($charsToUpdate as $charId) {
      $stm = $this->db->prepare("INSERT INTO travelhistory (person, location, arrival, day, hour, vehicle)
        VALUES (:charId, :locationId, :arrival, :day, :hour, :vehicleId)");
      $stm->bindInt("charId", $charId);
      $stm->bindInt("locationId", $loc);
      $stm->bindInt("arrival", $arrive);
      $stm->bindInt("day", $gameDate->getDay());
      $stm->bindInt("hour", $gameDate->getHour());
      $stm->bindInt("vehicleId", $this->getVehicleId());
      $stm->execute();
    }
  }

  public function getId() {
    return $this->id;
  }

  public function getConnectionId() {
    return $this->connection->getId();
  }

  public function getStart() {
    return $this->start;
  }

  public function getDestination() {
    return $this->destination;
  }

  public function getLeft()
  {
    return $this->left;
  }

  public function getFractionLeft()
  {
    return $this->left / $this->needed;
  }

  public function getFractionDone()
  {
    return (1 - $this->getFractionLeft());
  }


  protected abstract function notifyTurnAround($actorId);

  public function turnAround($actorId) {
    if (!Validation::isPositiveInt($actorId)) {
      throw new InvalidArgumentException("character id $actorId who turns around is not a positive integer");
    }

    $this->commitTurnAround();

    $this->notifyTurnAround($actorId);
  }

  public function commitTurnAround()
  {
    $destination = $this->destination;
    $this->destination = $this->start;
    $this->start = $destination;
    $this->left = $this->needed - $this->left;
  }

  protected abstract function getPersonColumnValue();

  protected abstract function getTypeColumnValue();

  /**
   * Used in travel history report.
   * @return int vehicle id or 0 if no vehicle is used
   */
  protected abstract function getVehicleId();

  protected abstract function postSaveInDb();

  public function saveInDb() {
    $person = $this->getPersonColumnValue();
    $type = $this->getTypeColumnValue();

    if (!$this->id) { // create a new travel
      $stm = $this->db->prepare("INSERT INTO travels (locfrom, locdest, speed_percent,
        travleft, travneeded, person, connection, type)
        VALUES (:start, :destination, :speedPercent, :left, :needed, :person, :connection, :type)");
      $stm->bindInt("start", $this->start);
      $stm->bindInt("destination", $this->destination);
      $stm->bindInt("speedPercent", $this->speedPercent);
      $stm->bindFloat("left", $this->left);
      $stm->bindInt("needed", $this->needed);
      $stm->bindInt("person", $person);
      $stm->bindInt("connection", $this->connection->getId());
      $stm->bindInt("type", $type);
      $stm->execute();
      $this->id = $this->db->lastInsertId();
    } elseif ($this->id && $this->finished) { // delete, because travel has ended
      $stm = $this->db->prepare("DELETE FROM travels WHERE id = :id");
      $stm->bindInt("id", $this->id);
      $stm->execute();
    } else {
      $stm = $this->db->prepare("UPDATE travels SET locfrom = :start, locdest = :destination, connection = :connection,
      speed_percent = :speedPercent, travleft = :left, travneeded = :needed, person = :person, type = :type WHERE id = :id");
      $stm->bindInt("start", $this->start);
      $stm->bindInt("destination", $this->destination);
      $stm->bindInt("connection", $this->connection->getId());
      $stm->bindInt("speedPercent", $this->speedPercent);
      $stm->bindFloat("left", $this->left);
      $stm->bindInt("needed", $this->needed);
      $stm->bindInt("person", $person);
      $stm->bindInt("type", $type);
      $stm->bindInt("id", $this->id);
      $stm->execute();
    }
    $this->postSaveInDb();
  }

  protected abstract function isVehicle();

  /**
   * @return Connection
   */
  protected function getConnection()
  {
    return $this->connection;
  }
}
