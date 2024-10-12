<?php

/**
 * Factory for wrapper of PDO database handle. Requires file <b>stddef.inc.php</b> already included.
 * Singleton pattern
 */
class DbFactory
{
  private static $instance;

  /** @var Db[] */
  private $db = [];

  private function __construct()
  {
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Opens a connection for a specified database credentials
   * or returns an existing one if one for the specified name already exists
   * @param $dbName string database to connect to
   * @param $config Config configuration for all database connection details excecpt database name
   * @return Db
   */
  public function getDb($dbName, Config $config)
  {
    if (!isset($this->db[$dbName])) {
      $pdo = new PDO('mysql:host=' . $config->dbHost() . ';dbname=' . $dbName . ";charset=utf8",
        $config->dbUser(), $config->dbPassword());

      $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $this->db[$dbName] = new Db($pdo);
    }
    return $this->db[$dbName];
  }
}
