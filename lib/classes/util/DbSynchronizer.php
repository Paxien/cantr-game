<?php

class DbSynchronizer
{
  private $from;
  private $to;

  private $temporaryDbName;
  /** @var Db */
  private $db;
  /** @var Logger */
  private $logger;

  private $structuralTables = [
    "access_types",
    "animal_domesticated_types",
    "animal_types",
    "applicationforms",
    "ceAccessTypes",
    "clothes_categories",
    "connections",
    "connecttypes",
    "corners",
    "councils",
    "events_types",
    "events_groups",
    "languages",
    "machines",
    "ids",
    "state_types",
    "objecttypes",
    "raws",
    "rawtools",
    "rawtypes",
    "regions",
    "texts",
    "objectcategories",
    "obj_properties",
    "turn",
    "uls_settings",
    "uls_referers",
    "weather_seasons",
    "weather_cells",
  ];

  public function __construct($from, $to, $temporaryDbName = null)
  {
    $this->from = $from;
    $this->to = $to;
    $this->temporaryDbName = $temporaryDbName;
    $this->db = Db::get();
    $this->logger = Logger::getLogger(__CLASS__);
  }

  public function perform()
  {
    $this->recreateDatabase();

    $this->copyStructure();

    $this->copyStructuralTablesContents();
  }

  private function recreateDatabase()
  {
    // Remove old test database and create a new one
    $this->db->query("DROP DATABASE IF EXISTS `$this->to`");
    $this->db->query("CREATE DATABASE `$this->to`");
  }

  private function copyStructure()
  {
    // Copy all tables structure from the $from database to $to
    $tableNamesStm = $this->db->query("SHOW TABLES FROM `$this->from`");

    foreach ($tableNamesStm->fetchScalars() as $name) {
      $this->db->query("CREATE TABLE `$this->to`.`" . $name . "` LIKE `$this->from`.`" . $name . "`");
    }
  }

  private function copyStructuralTablesContents()
  {
    foreach ($this->structuralTables as $tableName) {
      $this->db->query("ALTER TABLE `$this->to`.`" . $tableName . "` DISABLE KEYS");
      $this->db->query("INSERT INTO `$this->to`.`" . $tableName . "` SELECT * FROM `$this->from`.`" . $tableName . "`");
      $this->db->query("ALTER TABLE `$this->to`.`" . $tableName . "` ENABLE KEYS");
    }
  }

  public function createTemporaryDb()
  {
    if (!isset($this->temporaryDbName)) {
      return false;
    }
    $ref = $this->db->query("CREATE DATABASE `$this->temporaryDbName`");
    return $ref != null;
  }

  public function preserveTables(array $tableNames)
  {
    foreach ($tableNames as $name) {
      try {
        $this->db->query("CREATE TABLE `$this->temporaryDbName`.`$name` LIKE `$this->to`.`$name`");
        $this->db->query("INSERT INTO `$this->temporaryDbName`.`$name` SELECT * FROM `$this->to`.`$name`");
      } catch (PDOException $e) {
        $this->logger->warn("Table `$this->to`.`$name` doesn't exist, so not preserving the data");
      }
    }
  }

  public function loadTables(array $tableNames)
  {
    foreach ($tableNames as $name) {
      if ($this->tableExists($this->temporaryDbName, $name)) {
        $this->db->query("TRUNCATE `$this->to`.`$name`");
        $this->db->query("INSERT INTO `$this->to`.`$name` SELECT * FROM `$this->temporaryDbName`.`$name`");
      } else {
        $this->logger->warn("Table `$this->temporaryDbName`.`$name` doesn't exist, so not retrieving the data");
      }
    }
  }

  private function tableExists($dbName, $table) {
    $stm = $this->db->prepare("SHOW TABLES FROM `$dbName` LIKE " . $this->db->quote($table));
    return $stm->executeScalar();
  }

  /**
   * Synchronizes the contents of the two tables with the same name and structure.
   * After running the method the contents of table $tableName in $this->to database
   * are the same as of $tableName in $this->from.
   * Note: Names of columns are not escaped!
   * @param $tableName string table name to compare between
   * @param $primaryKeyColumns string[] names of columns being the primary key of this table
   */
  public function synchronizeDataInTable($tableName, $primaryKeyColumns)
  {
    $pkStr = implode(",", $this->quoteColumns($primaryKeyColumns));

    // Remove things removed from $this->from table
    $sql = "SELECT $pkStr FROM `$this->from`.`$tableName`";
    $ref = $this->db->query($sql);
    $allPks = $this->fetchListOfPrimaryKeyStrings($ref);
    $allPksStr = implode(",", $allPks);

    // I had to split it into two queries because when using subquery,
    // MySQL turns it into DEPENDENT SUBQUERY which kills performance
    $sql = "DELETE FROM `$this->to`.`$tableName`
    WHERE ($pkStr) NOT IN ($allPksStr)";
    $this->db->query($sql);

    // Add rows not existing in the $this->to table
    $sql = "INSERT IGNORE INTO `$this->to`.`$tableName`
      SELECT * FROM `$this->from`.`$tableName`";
    $this->db->query($sql);

    // Find differences between $this->from and $this->to tables, which means data was updated in $this->from
    // The columns in `merged` are duplicated only if any column with the same PK is different
    $sql = "SELECT $pkStr
    FROM (
      SELECT * FROM `$this->from`.`$tableName`
      UNION 
      SELECT * FROM `$this->to`.`$tableName`
    ) AS merged
  GROUP BY $pkStr HAVING COUNT(*) > 1";
    $stm = $this->db->query($sql);

    $idsToUpdate = $this->fetchListOfPrimaryKeyStrings($stm);

    if (!empty($idsToUpdate)) {
      $idsToUpdateStr = implode(", ", $idsToUpdate);
      // force update in INTRO_TABLE
      $sql = "REPLACE INTO `$this->to`.`$tableName`
        (SELECT * FROM `$this->from`.`$tableName` cct
        WHERE ($pkStr) IN ($idsToUpdateStr))";
      $this->db->query($sql);
    }
  }

  public function destroyTemporaryDb()
  {
    $this->db->query("DROP DATABASE `$this->temporaryDbName`");
  }

  private function quoteColumns($columns)
  {
    return array_map(function($column) {
      return "`$column`";
    }, $columns);
  }

  private function quoteValues($values)
  {
    return array_map(function($value) {
      return $this->db->quote($value);
    }, $values);
  }

  private function fetchListOfPrimaryKeyStrings(DbStatement $stm)
  {
    $idsToUpdate = [];
    foreach ($stm->fetchAll(PDO::FETCH_NUM) as $row) {
      $idStr = implode(", ", $this->quoteValues($row));
      $idsToUpdate[] = "($idStr)";
    }
    return $idsToUpdate;
  }

}
