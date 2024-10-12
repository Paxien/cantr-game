<?php

class SpawnPointGenerator
{
  private $characters;

  private $db;
  private $logger;

  const MIN_CHARS_IN_LOCATION_TO_ALLOW_SPAWNING = 2;

  const MINIMUM_ATTRACTIVENESS = 0.1;
  const DAYS_FOR_MAXIMUM_ATTRACTIVENESS = 1;
  const DAYS_FOR_MINIMUM_ATTRACTIVENESS = 7;
  const DAILY_DECREASE = (1 - self::MINIMUM_ATTRACTIVENESS) /
  (self::DAYS_FOR_MINIMUM_ATTRACTIVENESS - self::DAYS_FOR_MAXIMUM_ATTRACTIVENESS);

  public function __construct(array $existingCharacters, Db $db)
  {
    $this->characters = $existingCharacters;
    $this->db = $db;

    $this->logger = Logger::getLogger(__CLASS__);
  }

  public function getRandomSpawnPoint($language, $preferPopulousPlaces = false)
  {
    $stm = $this->db->prepare("SELECT use_density_spawning AS uds FROM languages WHERE id = :language LIMIT 1");
    $stm->bindInt("language", $language);
    $useDensitySpawning = $stm->executeScalar();

    $spawnPoint = 0;
    if ($useDensitySpawning) {
      $spawnPoint = $this->generateNewSpawnPoint($language, $preferPopulousPlaces);
    }

    // If, for whatever reason, finding a spawning location failed, use the old-fashioned method
    // which uses predefined spawnpoints
    if (!$spawnPoint) {
      $this->logger->info("Unable to generate a new spawnpoint for language $language, so the old algorithm will be used");
      $spawnPoint = $this->generateOldSpawnPoint($language);
    }

    // Return the just found spawning location, generateOldSpawnPoint always returns a result
    return $spawnPoint;
  }

  public function attractivenessFromActivity($lastActivity, $today)
  {
    if (!$lastActivity) {
      return self::MINIMUM_ATTRACTIVENESS;
    }
    $daysAgo = $today - $lastActivity;
    $daysOfDecrease = $daysAgo - self::DAYS_FOR_MAXIMUM_ATTRACTIVENESS;
    return min(1, max(self::MINIMUM_ATTRACTIVENESS, 1 - $daysOfDecrease * self::DAILY_DECREASE));
  }

  public function getAttractivenessOfRootLocations($language)
  {
    $stm = $this->db->prepare("SELECT eo.observer, MAX(e.day) AS last_day FROM events e 
      INNER JOIN events_obs eo ON e.id = eo.event
      WHERE e.type IN (:eventType1, :eventType2)
      GROUP BY eo.observer");

    $stm->bindInt("eventType1", 5); // speaking
    $stm->bindInt("eventType2", 370); // drunkenly speaking
    $stm->execute();

    $lastActivityByCharacter = [];
    foreach ($stm->fetchAll() as $row) {
      $lastActivityByCharacter[$row->observer] = $row->last_day;
    }

    $stm = $this->db->prepare("
      SELECT
        c.location,
        c.id
      FROM
        chars c
        LEFT JOIN settings_chars ch_settings ON ch_settings.person = c.id
          AND ch_settings.type = :charSettingType
          AND ch_settings.data = 1
        INNER JOIN locations l ON c.location = l.id
      WHERE c.location > 0
        AND status = :status
        AND language = :language
        AND ch_settings.id IS NULL
      GROUP BY c.id, l.type");

    $stm->bindInt("charSettingType", CharacterSettings::OPT_OUT_FROM_SPAWNING_SYSTEM);
    $stm->bindInt("status", CharacterConstants::CHAR_ACTIVE);
    $stm->bindInt("language", $language);
    $stm->execute();

    $locations = [];
    foreach ($stm->fetchAll() as $character) {
      $locations[$character->location][] = $lastActivityByCharacter[$character->id] ?: 0;
    }

    $today = GameDate::NOW()->getDay();
    $attractivenessOfLocation = [];
    foreach ($locations as $locationId => $activityInLocation) {
      $sum = 0;
      foreach ($activityInLocation as $lastActivity) {
        $sum += $this->attractivenessFromActivity($lastActivity, $today);
      }
      $attractivenessOfLocation[$locationId] = $sum;
    }

    $attractivenessInRoot = [];
    $batchRootLocationFinder = new BatchRootLocationFinder($this->db);
    foreach ($attractivenessOfLocation as $locationId => $attractiveness) {
      $rootId = $batchRootLocationFinder->getRoot($locationId);

      try {
        if (Location::loadById($rootId)->isOutside()) {
          $attractivenessInRoot[$rootId] += $attractiveness;
        }
      } catch (InvalidArgumentException $e) {
        $this->logger->error("A potential spawnpoint with ID=$rootId does not exist");
      }
    }

    return $attractivenessInRoot;
  }

  private function generateNewSpawnPoint($language, $preferPopulousPlaces)
  {

    // Find the total attractiveness of the locations in that language

    $attractivenessOfLocations = $this->getAttractivenessOfRootLocations($language);
    if ($preferPopulousPlaces) {
      foreach ($attractivenessOfLocations as &$attractiveness) {
        $attractiveness = pow($attractiveness, 2);
      }
    }

    $sumOfAttractiveness = array_sum($attractivenessOfLocations);

    if ($sumOfAttractiveness == 0) {
      return 0; // no chars to use density spawning
    }

    // 120 tries to find a loc where other player's chars are distant

    $selectedLocationId = 0;
    for ($attempt = 0; $attempt < 120 && !$selectedLocationId; $attempt++) {

      // Find a random number between 1 and the just found total
      $rand = (mt_rand() / mt_getrandmax()) * $sumOfAttractiveness;

      // Find a random location using this number; locations with more people will have a higher chance to be
      // chosen; root locations are taken, so spawning location can only be of type 1.
      $sum = 0;
      foreach ($attractivenessOfLocations as $locationId => $attractiveness) {
        $sum += $attractiveness;
        if ($sum >= $rand) {
          $selectedLocationId = $locationId;
          break;
        }
      }
      if ($selectedLocationId === 0) {
        continue;
      }
      try {
        $loc = Location::loadById($selectedLocationId);
        if ($this->isAnyCharacterOfSamePlayerTooClose($loc, $attempt)) {
          $selectedLocationId = 0;
        } elseif (!$this->thereAreAtLeastTwoCharactersInLocation($loc, $language)) {
          $selectedLocationId = 0;
          $this->logger->info("Refusing to create a character in location $selectedLocationId (lang: $language),
              because there are less than " . self::MIN_CHARS_IN_LOCATION_TO_ALLOW_SPAWNING . " characters");
        }
      } catch (InvalidArgumentException $e) {
        $this->logger->warn("Error when generating spawnpoint, suspected spawnpoint was $selectedLocationId", $e);
        $selectedLocationId = 0;
      }
    }
    return $selectedLocationId;
  }

  private function generateOldSpawnPoint($language)
  {
    $stm = $this->db->prepare("SELECT id FROM spawninglocations WHERE language = :language");
    $stm->bindInt("language", $language);
    $stm->execute();
    $startLocs = $stm->fetchScalars();

    if (count($startLocs) == 1) {
      return $startLocs[0];
    }

    $locId = 0;
    for ($attempt = 0; $attempt <= 120 && !$locId; $attempt++) {
      $locId = $startLocs[array_rand($startLocs)];

      try {
        $loc = Location::loadById($locId);
        if ($this->isAnyCharacterOfSamePlayerTooClose($loc, $attempt)) {
          $locId = 0;
        }
      } catch (InvalidArgumentException $e) {
        $this->logger->warn("Error when generating spawnpoint, suspected spawnpoint was $locId", $e);
        $locId = 0;
      }
    }
    return $locId;
  }

  private function thereAreAtLeastTwoCharactersInLocation(Location $loc, $language)
  {
    $allLocationsFromRoot = $loc->getSublocationsRecursive();
    $allLocationsFromRoot[] = $loc->getId();

    $stm = $this->db->prepareWithIntList("SELECT COUNT(*) FROM chars c
      LEFT JOIN settings_chars chs 
        ON chs.person = c.id 
        AND chs.type = :charSettingType
        AND chs.data = 1
      WHERE status = :active
        AND language = :language
        AND chs.id IS NULL
        AND location IN (:locationIds)",
      ["locationIds" => $allLocationsFromRoot]);
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->bindInt("charSettingType", CharacterSettings::OPT_OUT_FROM_SPAWNING_SYSTEM);
    $stm->bindInt("language", $language);
    $stm->execute();

    $numberOfChars = $stm->fetchColumn();
    return $numberOfChars >= self::MIN_CHARS_IN_LOCATION_TO_ALLOW_SPAWNING;
  }

  /**
   * @param $loc Location location to which distance is calculated
   * @param $attempt int number of attempt, higher attempt means it's easier to find a spawnpoint
   * @return bool true if it's too close to allow spawning
   */
  private function isAnyCharacterOfSamePlayerTooClose(Location $loc, $attempt)
  {
    foreach ($this->characters as $char) {
      $pos = $char->getPos();

      $distance = Measure::distance($pos["x"], $pos["y"], $loc->getX(), $loc->getY());
      if ($distance < 120 - $attempt) {
        return true;
      }
    }
    return false;
  }
}
