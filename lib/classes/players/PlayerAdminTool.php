<?php

class PlayerAdminTool
{

  private $playerId = 0;
  private $db;

  // Constructor
  public function __construct($playerId, Db $db)
  {
    $this->playerId = $playerId;
    $this->db = $db;
  }

  /**
   * Load all characters of the player
   * @return Character[]
   */
  public function getCharacters()
  {
    $stm = $this->db->prepare("SELECT * FROM chars WHERE player = :playerId ORDER BY lastdate, lasttime, name");
    $stm->bindInt("playerId", $this->playerId);
    $stm->execute();
    $characterList = [];
    foreach ($stm->fetchAll() as $charInfo) {
      $char = Character::loadFromFetchObject($charInfo);
      $characterList[] = $char;
    }
    return $characterList;
  }

  private function getReadableLocName(Location $location)
  {
    if ($location->getName() != "") {
      return $location->getName();
    }
    $stm = $this->db->prepare("SELECT name as oldName, usersname as guessedName 
      FROM oldlocnames WHERE id = :locationId LIMIT 1");
    $stm->bindInt("locationId", $location->getId());
    $stm->execute();
    $names = $stm->fetchObject();
    if ($names->oldName || $names->guessedName) {
      if (strcasecmp($names->oldName, $names->guessedName) == 0) {
        return $names->oldName;
      }
      return "$names->oldName ($names->guessedName)";
    }

    $stm = $this->db->prepare("SELECT name FROM charnaming WHERE observed = :locationId
                              AND type = :entryType GROUP BY name ORDER BY COUNT(name) DESC LIMIT 1");
    $stm->bindInt("locationId", $location->getId());
    $stm->bindInt("entryType", NamingConstants::TYPE_LOCATION);
    return $stm->executeScalar();
  }

  public function getCharacterDetails(Character $char, GameDate $turnInfo, $listKnownAs, $listKnows, $travels)
  {
    $charInfo = new stdClass();
    $charInfo->id = $char->getId();
    $charInfo->name = $char->getName();

    $locFullName = "";
    if ($char->getLocation() > 0) {
      $charLoc = Location::loadById($char->getLocation());

      $locFullName .= $this->getReadableLocName($charLoc);

      $rootLoc = $charLoc->getRoot();
      if (!$charLoc->isOutside() && $charLoc->getRegion() > 0) {
        $parentLoc = Location::loadById($charLoc->getRegion());
        if ($parentLoc->getId() != $rootLoc->getId()) {
          $locFullName .= " <small style='color: grey;'>IN</small> " . $this->getReadableLocName($parentLoc);
        }
      }
      if ($rootLoc->getId() != $charLoc->getId()) {
        $locFullName .= " <small style='color: grey;'>IN</small> " . $this->getReadableLocName($rootLoc);
      }
    }
    $charInfo->isAlive = $char->isAlive();
    $charInfo->isDead = !$char->isAlive() && !$char->isPending();

    $charInfo->slotBlocked = ($char->getStatus() > CharacterConstants::CHAR_ACTIVE) ? $char->getSlotBlockedDays($turnInfo->getDay()) : 0;

    if ($char->isAlive() && $char->isTravelling()) {
      $travel = Travel::loadByParticipant($char);
      $locFrom = Location::loadById($travel->getStart());
      $locTo = Location::loadById($travel->getDestination());
      $locFullName .= " <FONT COLOR=\"#888\">" . $this->getReadableLocName($locFrom) .
        " -> " . $this->getReadableLocName($locTo) . "</FONT>";
    }
    $charInfo->locFullName = $locFullName;

    $charInfo->lockDays = 0;
    if (Limitations::getLims($char->getId(), Limitations::TYPE_LOCK_CHAR) != 0) {
      $secleft = Limitations::getTimeLeft($char->getId(), Limitations::TYPE_LOCK_CHAR);
      $tleft = Limitations::ctodhms($secleft);
      $charInfo->lockDays = max($tleft['day'], 1); // it should never be zero if the lock is in place
    }

    $charInfo->langShortName = LanguageConstants::$LANGUAGE[$char->getLanguage()]["lang_abr"];

    $charInfo->lastDate = $char->getLastDate();
    $charInfo->lastTime = $char->getLastTime();

    if ($listKnownAs) {
      $stm = $this->db->prepare("SELECT chars.name AS cname,charnaming.name AS name,
       players.firstname AS pfname,players.lastname AS plname,players.id AS pid 
        FROM charnaming,players,chars 
        WHERE observed = :charId AND charnaming.observer = chars.id AND charnaming.type = :entryType
          AND chars.player = players.id ORDER BY chars.id");
      $stm->bindInt("charId", $char->getId());
      $stm->bindInt("entryType", NamingConstants::TYPE_CHAR);
      $stm->execute();
      $charInfo->knownAs = $stm->fetchAll();
    }

    if ($listKnows) {
      $stm = $this->db->prepare("SELECT chars.name AS cname,charnaming.name AS name,players.firstname AS pfname,
        players.lastname AS plname,players.id AS pid FROM charnaming,players,chars
      WHERE observer = :charId AND charnaming.observed = chars.id AND charnaming.type = :entryType
        AND chars.player=players.id ORDER BY chars.id");
      $stm->bindInt("charId", $char->getId());
      $stm->bindInt("entryType", NamingConstants::TYPE_CHAR);
      $stm->execute();
      $charInfo->knows = $stm->fetchAll();
    }

    $charInfo->customDesc = $char->getCustomDesc();

    if ($travels) {
      $shipTypeIds = Location::getShipTypeArray();

      $stm = $this->db->prepareWithIntList("
      SELECT location, oln.name AS locationname, arrival, day, hour, vehicle, COALESCE(l.name, CONCAT(ot.unique_name, ' (type)')) AS vehiclename
      FROM travelhistory th
      LEFT OUTER JOIN oldlocnames oln ON th.location = oln.id
      LEFT OUTER JOIN locations l ON (th.vehicle > 0
        AND (th.day NOT BETWEEN 4892 AND 5164 OR l.area IN (:shipTypes)) -- fix for timeframe when data in db was malformed
        AND th.vehicle = l.id)
      LEFT OUTER JOIN objecttypes ot ON (th.vehicle > 0 AND th.vehicle = ot.id)
      WHERE person = :charId ORDER BY day, hour", [
        "shipTypes" => $shipTypeIds,
      ]);
      $stm->bindInt("charId", $char->getId());
      $stm->execute();

      $charInfo->travels = Pipe::from($stm->fetchAll())->map(function($travel) {
        if (!$travel->locationname) {
          $travel->locationname = "<i>Location #$travel->location</i>";
        }
        return $travel;
      })->toArray();
    }

    return $charInfo;
  }
}
