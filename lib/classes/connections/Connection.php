<?php

/**
 * Stores data from tables `connections` and `connecttypes` (roads in the game)
 */
class Connection
{
  private $id;
  private $start;
  private $end;
  private $length;
  private $direction; // angle in degrees from start to end
  /** @var ConnectionPart[] */
  private $parts;

  private $costFactor;

  /*
   * Should be instantiated through static factory method
   */
  /** @var Logger */
  private $logger;
  /** @var Db */
  private $db;

  private function __construct(Db $db)
  {
    $this->logger = Logger::getLogger(__CLASS__);
    $this->db = $db;
  }

  /**
   * Static factory method for connection.
   * @param $id int id of connection
   * @return Connection object being connection
   * @throws InvalidArgumentException when such connection doesn't exist
   */
  public static function loadById($id)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT id, start, end, length, direction
      FROM connections WHERE id = :id");
    $stm->bindInt("id", $id);
    $stm->execute();
    if ($conn = $stm->fetchObject()) {
      $partsStm = $db->prepare("SELECT type, deterioration FROM connection_parts WHERE connection = :connectionId");
      $partsStm->bindInt("connectionId", $id);
      $partsStm->execute();
      $parts = Pipe::from($partsStm->fetchAll())->map(function($obj) {
        return new ConnectionPart($obj->deterioration, $obj->type);
      })->toArray();
      $connection = new self($db);
      $connection->id = $conn->id;
      $connection->start = $conn->start;
      $connection->end = $conn->end;
      $connection->length = $conn->length;
      $connection->direction = $conn->direction;
      $connection->parts = $parts;
      $connection->costFactor = $connection->calculateCostFactor();
      return $connection;
    }
    throw new InvalidArgumentException("connection of id $id doesn't exist!");
  }

  public function getId()
  {
    return $this->id;
  }

  public function getStart()
  {
    return $this->start;
  }

  public function getEnd()
  {
    return $this->end;
  }

  public function getTypeIds()
  {
    return Pipe::from($this->parts)->map(function(ConnectionPart $part) {
      return $part->getType()->getId();
    })->toArray();
  }

  public function getParts()
  {
    return $this->parts;
  }

  public function getLength()
  {
    return $this->length;
  }

  public function getCostFactor()
  {
    return $this->costFactor;
  }

  public function getDirection()
  {
    return $this->direction;
  }

  public function getAllowedVehicles($limitToParts = null)
  {
    $allowedVehicles = [];
    foreach ($this->parts as $part) {
      $allowedVehicles = array_merge($allowedVehicles, $part->getType()->getAllowedVehicles());
    }

    return array_values(array_unique($allowedVehicles));
  }

  public function getConnectionTypePartFor($vehicleTypeId) // or "walking"
  {
    foreach ($this->parts as $part) {
      if (in_array($vehicleTypeId, $part->getType()->getAllowedVehicles())) {
        return $part;
      }
    }
    throw new Exception(); // todo, no such vehicle
  }

  public function canBeMovedOn($vehicleTypeId, $limitToParts = null)
  {
    foreach ($this->getParts() as $connTypePart) {
      $partIsAllowed = $limitToParts === null || in_array($connTypePart->getType()->getName(), $limitToParts);
      if ($partIsAllowed && in_array($vehicleTypeId, $connTypePart->getType()->getAllowedVehicles())) {
        return true;
      }
    }
    return false;
  }

  /**
   * Only to get direction from one of two locations which are ends of the connection
   */
  public function getDirectionFromLocation($locationId)
  {
    if ($locationId == $this->getEnd()) {
      return ($this->getDirection() + 180) % 360;
    } elseif ($locationId == $this->getStart()) {
      return $this->getDirection();
    } else {
      throw new InvalidArgumentException("$locationId is not a valid start/end of connection $this->id");
    }
  }

  /**
   * Only to get opposite location when locationId is start or end
   * @param $locationId
   * @return int location id
   * @throws InvalidArgumentException when the location is not start or end of this connection
   */
  public function getOppositeLocation($locationId)
  {
    if ($locationId == $this->getEnd()) {
      return $this->getStart();
    } elseif ($locationId == $this->getStart()) {
      return $this->getEnd();
    } else {
      throw new InvalidArgumentException("$locationId is not a valid start/end of connection $this->id");
    }
  }

  public function isIncidentTo($locationId)
  {
    return $this->getStart() == $locationId || $this->getEnd() == $locationId;
  }

  public function getTypeNames()
  {
    return Pipe::from($this->parts)->map(function(ConnectionPart $typePart) {
      return $typePart->getType()->getName();
    })->toArray();
  }

  private function calculateCostFactor()
  {
    $startLoc = Location::loadById($this->start);
    $endLoc = Location::loadById($this->end);

    $startCost = $startLoc->getProperty("RoadFactor");
    $endCost = $endLoc->getProperty("RoadFactor");
    return ($startCost + $endCost) / 2;
  }

  public function isBeingImproved(ConnectionPart $part)
  {
    foreach ($this->getOngoingImprovements() as $improvement) {
      if ($part->getType()->getId() == $improvement["targetType"]->getImprovedFrom()) {
        return true;
      }
    }
    return false;
  }

  public function getOngoingImprovements()
  {
    $stm = $this->db->prepare("SELECT id, result FROM projects WHERE result LIKE :result
      AND location IN (:start, :end) AND type = :type");
    $stm->bindStr("result", "{$this->id}:%");
    $stm->bindInt("start", $this->start);
    $stm->bindInt("end", $this->end);
    $stm->bindInt("type", ProjectConstants::TYPE_IMPROVING_ROADS);
    $stm->execute();
    return Pipe::from($stm->fetchAll())->map(function($project) {
      list($connectionId, $targetType) = explode(":", $project->result);
      return ["id" => $project->id, "targetType" => ConnectionType::loadById($targetType)];
    })->toArray();

  }

  public function getPotentialImprovements()
  {
    $stm = $this->db->prepareWithIntList("SELECT id FROM connecttypes WHERE improved_from IN (:improvedFrom)", [
      "improvedFrom" => $this->getTypeIds(),
    ]);
    $stm->execute();
    $improvements = $stm->fetchScalars();

    $partTypeNames = Pipe::from($this->getParts())->map(function(ConnectionPart $part) {
      return $part->getType()->getName();
    })->toArray();

    $improvementTargetTypeNames = Pipe::from($this->getOngoingImprovements())->map(function($improvement) {
      return $improvement["targetType"]->getName();
    })->toArray();

    $stm = $this->db->query("SELECT id, improve_requirements AS req FROM connecttypes WHERE improved_from = 0");
    foreach ($stm->fetchAll() as $buildable) {
      $requirements = Parser::rulesToArray($buildable->req);
      if (!array_key_exists('raws', $requirements)) { // it's just impossible to build
        continue;
      }
      if (array_key_exists('hasnopart', $requirements)) {
        $forbiddenPartTypeNames = explode(",", $requirements['hasnopart']);
        if (array_intersect(array_merge($partTypeNames, $improvementTargetTypeNames), $forbiddenPartTypeNames)) { // if any part has forbidden type then disallow that
          continue;
        }
      }
      if (array_key_exists("haspart", $requirements)) { // if there is no required part then disallow building a such
        if (!in_array($requirements['haspart'], $partTypeNames)) {
          continue;
        }
      }
      $improvements[] = $buildable->id;
    }
    return $improvements;
  }

  public function canBeImprovedTo(ConnectionType $nextType)
  {
    if ($nextType->isPrimaryType()) { // somebody is already building the same new part
      $improvementTargetTypeNames = Pipe::from($this->getOngoingImprovements())->map(function($improvement) {
        return $improvement["targetType"]->getName();
      })->toArray();
      $currentImprovements = Pipe::from($this->parts)->map(function(ConnectionPart $part) {
        return $part->getType()->getName();
      })->toArray();
      if (in_array($nextType->getName(), $improvementTargetTypeNames) ||
        in_array($nextType->getName(), $currentImprovements)
      ) {
        return false;
      }
    } else {
      $partBeingImproved = $this->getConnectionPartImprovableTo($nextType);
      if ($partBeingImproved === null) { // it should be improvement of existing part but it doesn't exist
        return false;
      }
    }
    return in_array($nextType->getId(), $this->getPotentialImprovements());
  }

  /**
   * @param ConnectionType $type type to be improved to
   * @return ConnectionPart|null appropriate part or null if there is no such part
   */
  public function getConnectionPartImprovableTo(ConnectionType $type)
  {
    foreach ($this->parts as $part) {
      if ($type->getImprovedFrom() == $part->getType()->getId()) {
        return $part;
      }
    }
    return null;
  }

  /**
   * @param ConnectionType $type type which this part should use
   * @return ConnectionPart|null appropriate part or null if there is no such part
   */
  public function getConnectionPartWithType(ConnectionType $type)
  {
    foreach ($this->parts as $part) {
      if ($type->getId() == $part->getType()->getId()) {
        return $part;
      }
    }
    return null;
  }

  public function getRawsToImproveTo(ConnectionType $toType)
  {
    $requirements = $this->getImproveRequirementArray($toType);
    if (array_key_exists("raws", $requirements)) {
      $raws = Parser::rulesToArray($requirements["raws"], ",>");
      $factor = $this->length * $this->costFactor;
      return Pipe::from($raws)->map(function($raw) use ($factor) {
        return round($raw * $factor);
      })->toArray();
    }
    throw new InvalidArgumentException($toType->getId() . " isn't a valid improvement type");
  }

  public function getDaysToImproveTo(ConnectionType $toType)
  {
    $requirements = $this->getImproveRequirementArray($toType);
    if (array_key_exists("days", $requirements)) {
      $factor = $this->length * $this->costFactor;
      return $requirements["days"] * $factor;
    }
    throw new InvalidArgumentException($toType->getId() . " isn't valid improvement type");
  }

  private function getImproveRequirementArray(ConnectionType $toType)
  {
    $stm = $this->db->prepare("SELECT improve_requirements FROM connecttypes WHERE id = :id");
    $stm->bindInt("id", $toType->getId());
    $requirements = $stm->executeScalar();
    return Parser::rulesToArray($requirements);
  }

  // END IMPROVING ROADS


  /**
   * I assume this class isn't used to create/remove connections, so it's really just to save type
   */
  public function saveInDb()
  {
    $stm = $this->db->prepare("UPDATE connections SET start = :start, end = :end,
      direction = :direction, length = :length WHERE id = :id");
    $stm->bindInt("start", $this->start);
    $stm->bindInt("end", $this->end);
    $stm->bindInt("direction", $this->direction);
    $stm->bindInt("length", $this->length);
    $stm->bindInt("id", $this->id);
    $stm->execute();

    $stm = $this->db->prepare("DELETE FROM connection_parts WHERE connection = :connectionId");
    $stm->bindInt("connectionId", $this->getId());
    $stm->execute();

    $stm = $this->db->prepare("INSERT INTO connection_parts (connection, type, deterioration)
      VALUES (:connectionId, :type, :deterioration)");
    foreach ($this->parts as $part) {
      $stm->bindInt("connectionId", $this->getId());
      $stm->bindInt("type", $part->getType()->getId());
      $stm->bindInt("deterioration", $part->getDeterioration());
      $stm->execute();
    }
  }

  public static function getTypeIdByName($name)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM connecttypes WHERE name = :name");
    $stm->bindStr("name", $name);
    $typeId = $stm->executeScalar();
    if ($typeId == null) {
      throw new InvalidArgumentException("$name is not valid name of connection (road) type");
    }
    return $typeId;
  }

  public static function getTypeNameById($id)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT name FROM connecttypes WHERE id = :id");
    $stm->bindInt("id", $id);
    $typeName = $stm->executeScalar();
    if ($typeName == null) {
      throw new InvalidArgumentException("$id is not valid id of connection (road) type");
    }
    return $typeName;
  }

  public function addPart(ConnectionType $targetType)
  {
    $this->parts[] = new ConnectionPart(0, $targetType->getId());
  }

  public function removePart(ConnectionPart $part)
  {
    if (($key = array_search($part, $this->parts)) !== false) {
      unset($this->parts[$key]);
      $this->parts = array_values($this->parts);
    } else {
      $this->logger->warn("trying to remove part of type " . $part->getType()->getId()
        . " for connection " . $this->id . " which doesn't have such part");
    }
  }

  public static function outgoingConnections(Location $location)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM connections WHERE start = :locationId1 OR end = :locationId2");
    $stm->bindInt("locationId1", $location->getId());
    $stm->bindInt("locationId2", $location->getId());
    $stm->execute();
    $connectionIds = $stm->fetchScalars();
    return Pipe::from($connectionIds)->map(function($connectionId) {
      return Connection::loadById($connectionId);
    })->toArray();
  }
}
