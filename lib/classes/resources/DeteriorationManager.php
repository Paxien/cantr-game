<?php

class DeteriorationManager
{
  /** @var Db */
  private $db;
  /** @var Logger */
  private $logger;

  public function __construct(Db $db)
  {
    $this->db = $db;
    $this->logger = Logger::getLogger(__CLASS__);
  }

  /**
   * @return array of maps with keys: [rawName, rawId, amount, secondsProcessing] useful for generating report
   */
  public function taintAllResources()
  {
    $taintStats = new Statistic("taint", Db::get());
    $stm = $this->db->query("SELECT id, name, taint_target_weight FROM rawtypes WHERE taint_target_weight != 0");
    $report = [];
    foreach ($stm->fetchAll() as $raw) {
      $start = microtime(true);

      $amountTainted = $this->taintResource($raw->id, $raw->taint_target_weight);
      $end = microtime(true);
      $taintStats->update($raw->name, 0, $amountTainted);
      $report[$raw->name] = (object)[
        "rawName" => $raw->name,
        "rawId" => $raw->id,
        "amount" => $amountTainted,
        "secondsProcessing" => ($end - $start),
      ];
    }
    return $report;
  }

  public function taintResource($rawType, $taintTargetWeight)
  {
    $rawsBefore = $this->getGlobalWeightOfAllResourcePiles($rawType);

    import_lib("func.getrandom.inc.php");
    $randomForRounding = random_percent();
    // taint of raws on the ground or in the inventory - all in one query
    $stm = $this->db->prepare("UPDATE objects SET weight = weight -   
      IF(
        :randomForRounding < amount_to_taint(weight, :targetWeight, :maxTaintFraction) % 1, 
        CEIL(amount_to_taint(weight, :targetWeight, :maxTaintFraction)), 
        FLOOR(amount_to_taint(weight, :targetWeight, :maxTaintFraction))
      )
      WHERE type = :objectType AND typeid = :rawType AND (location > 0 OR person > 0)");
    $stm->bindFloat("maxTaintFraction", DeteriorationConstants::MAX_TAINT);
    $stm->bindFloat("randomForRounding", $randomForRounding);
    $stm->bindInt("targetWeight", $taintTargetWeight);
    $stm->bindInt("objectType", ObjectConstants::TYPE_RAW);
    $stm->bindInt("rawType", $rawType);
    $stm->execute();

    try {
      $this->db->beginTransaction();
      // taint of raws in the storages - looped over and updated one in a time
      $weightsOfPilesBefore = $this->getWeightsForPilesOfResourceInStorages($rawType);
      foreach (array_keys($weightsOfPilesBefore) as $pileId) {
        $stm = $this->db->prepare("UPDATE objects SET weight = weight -
      IF(
        :randomForRounding < amount_to_taint(weight, :targetWeight, :maxTaintFraction) % 1, 
        CEIL(amount_to_taint(weight, :targetWeight, :maxTaintFraction)), 
        FLOOR(amount_to_taint(weight, :targetWeight, :maxTaintFraction))
      )
      WHERE id = :id");
        $stm->bindFloat("maxTaintFraction", DeteriorationConstants::MAX_TAINT);
        $stm->bindFloat("randomForRounding", $randomForRounding);
        $stm->bindInt("targetWeight", $taintTargetWeight);
        $stm->bindInt("id", $pileId);
        $stm->execute();
      }
      $weightsOfPilesAfter = $this->getWeightsForPilesOfResourceInStorages($rawType);
      foreach (array_keys($weightsOfPilesAfter) as $pileId) {
        $weightTainted = $weightsOfPilesBefore[$pileId] - $weightsOfPilesAfter[$pileId];
        $stm = $this->db->prepare("SELECT attached FROM objects WHERE id = :objectId");
        $stm->bindInt("objectId", $pileId);
        $storageId = $stm->executeScalar();
        do {
          $stm = $this->db->prepare("UPDATE objects SET weight = weight - :tainted WHERE id = :objectId");
          $stm->bindInt("objectId", $storageId);
          $stm->bindInt("tainted", $weightTainted);
          $stm->execute();
          $stm = $this->db->prepare("SELECT attached FROM objects WHERE id = :objectId");
          $stm->bindInt("objectId", $storageId);
          $storageId = $stm->executeScalar();
        } while ($storageId > 0);
      }

      $rawsAfter = $this->getGlobalWeightOfAllResourcePiles($rawType);
      $this->db->commit();
    } catch (Exception $e) {
      $this->logger->error("It was impossible to execute taint script of $rawType inside of storages", $e);
    }

    return $rawsBefore - $rawsAfter;
  }

  /**
   * @param $rawType
   * @return int weight of all resources of the specific type
   */
  public function getGlobalWeightOfAllResourcePiles($rawType)
  {
    $stm = $this->db->prepare("SELECT SUM(weight) FROM objects
      WHERE type = :objectType AND typeid = :rawType
        AND expired_date = 0");
    $stm->bindInt("objectType", ObjectConstants::TYPE_RAW);
    $stm->bindInt("rawType", $rawType);
    return $stm->executeScalar();
  }

  /**
   * @param $rawType
   * @return array where key is objectId of the raw pile and value is weight of the pile
   */
  public function getWeightsForPilesOfResourceInStorages($rawType)
  {
    $stm = $this->db->prepare("SELECT id, weight
      FROM objects WHERE type = :objectType AND typeid = :rawType AND attached > 0");
    $stm->bindInt("objectType", ObjectConstants::TYPE_RAW);
    $stm->bindInt("rawType", $rawType);
    $stm->execute();
    $weightsOfRawPiles = [];
    foreach ($stm->fetchAll() as $raw) {
      $weightsOfRawPiles[$raw->id] = $raw->weight;
    }
    return $weightsOfRawPiles;
  }

  /**
   * Unlike taint induced daily by DeteriorationManager::taintAllResources, which method is deterministic, rounding to the nearest int value.
   * @param $originalWeight
   * @param $rawName
   * @param $numberOfDays
   * @param Db $db
   * @return int weight of raw that should taint
   */
  public static function accumulatedAmountToTaint($originalWeight, $rawName, $numberOfDays, Db $db)
  {
    if (!Validation::isNonNegativeInt($numberOfDays)) {
      throw new InvalidArgumentException("numberOfDays=$numberOfDays for raw=$rawName, should always be a non-negative int");
    }
    $stm = $db->prepare("SELECT taint_target_weight FROM rawtypes WHERE name = :name");
    $stm->bindStr("name", $rawName);
    $taintTargetWeight = $stm->executeScalar();
    $weight = $originalWeight;
    for ($i = 0; $i < $numberOfDays; $i++) {
      $weight -= self::amountToTaint($weight, $taintTargetWeight);
    }
    return round($originalWeight - $weight);
  }

  /**
   * If changing this method, remember to update SQL function `amount_to_taint` accordingly (see class MigrationManager)
   * @param $weight
   * @param $targetWeight
   * @return mixed
   */
  public static function amountToTaint($weight, $targetWeight)
  {
    if ($targetWeight == 0) {
      return 0;
    }
    return min(sqrt($weight / ($targetWeight / 35)) / 2000, DeteriorationConstants::MAX_TAINT) * $weight;
  }
}