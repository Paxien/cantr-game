<?php

class ConnectionFinder
{
  /**
   * @param $location Location|int location adjacent to the connection or its id
   * @return Connection[]
   */
  public static function connectionsAdjacentTo($location)
  {
    $db = Db::get();
    if ($location instanceof Location) {
      $location = $location->getId();
    }

    if (!Validation::isNonNegativeInt($location)) {
      throw new InvalidArgumentException($location);
    }

    $stm = $db->prepare("SELECT id FROM connections WHERE :id IN (start ,end)");
    $stm->bindInt("id", $location);
    $stm->execute();

    $connections = [];
    foreach ($stm->fetchScalars() as $connId) {
      $connections[] = Connection::loadById($connId);
    }
    return $connections;
  }
}