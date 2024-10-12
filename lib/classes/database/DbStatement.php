<?php

/**
 * Class DbStatement
 * @method  bool bindColumn (mixed $column, mixed &$param, int $type, int $maxlen, mixed $driverdata)
 * @method  bool bindParam (mixed $parameter, mixed &$variable, int $data_type = PDO::PARAM_STR, int $length, mixed $driver_options)
 * @method  bool bindValue (mixed $parameter, mixed $value, int $data_type = PDO::PARAM_STR)
 * @method  bool closeCursor ()
 * @method  int columnCount ()
 * @method  void debugDumpParams ()
 * @method  string errorCode ()
 * @method  array errorInfo ()
 * @method  mixed fetch (int $fetch_style = -1, int $cursor_orientation = PDO::FETCH_ORI_NEXT, int $cursor_offset = 0)
 * @method  array fetchAll (int $fetch_style = null, mixed $fetch_argument = null, array $ctor_args = [])
 * @method  mixed fetchColumn (int $column_number = 0)
 * @method  mixed fetchObject (string $class_name = "stdClass", array $ctor_args = [])
 * @method  mixed getAttribute (int $attribute)
 * @method  array getColumnMeta (int $column)
 * @method  bool nextRowset ()
 * @method  int rowCount ()
 * @method  bool setAttribute (int $attribute, mixed $value)
 * @method  bool setFetchMode (int $mode)
 */
class DbStatement
{
  private $statement;
  private $queryLogger;
  private $logger;

  public function get()
  {
    return $this->statement;
  }

  public function __construct(PDOStatement $statement)
  {
    $this->statement = $statement;
    $this->queryLogger = new DbQueryLogger($this->statement);
    $this->logger = Logger::getLogger(__CLASS__);
  }

  public function execute()
  {
    list ($usec, $sec) = explode(" ", microtime());
    $startTime = $usec + $sec % 10000000;

    try {
      $args = func_get_args();
      call_user_func_array([$this->statement, "execute"], $args);
      // end elapsed time check - should be in finally
      list ($usec, $sec) = explode(" ", microtime());
      $time = $usec + $sec % 10000000 - $startTime;
    } catch (Exception $e) {
      // end elapsed time check - should be in finally
      list ($usec, $sec) = explode(" ", microtime());
      $time = $usec + $sec % 10000000 - $startTime;

      $stack = debug_backtrace();
      $this->queryLogger->logError($this->statement->queryString, $stack, $e);
      throw $e;
    }
    $GLOBALS['sqltime'] += $time;
    $GLOBALS['sqlcount']++;
  }

  // these binding methods should be used instead of bindValue

  public function bindStr($name, $value, $nullable = false)
  {
    if ($nullable && $value === null) {
      $this->statement->bindValue($name, null, PDO::PARAM_NULL);
    } else {
      if (!is_string($value)) {
        $this->logger->warn("Param $name=" . var_export($value, true) . " is not a string");
      }
      $this->statement->bindValue($name, $value, PDO::PARAM_STR);
    }
  }

  public function bindInt($name, $value, $nullable = false)
  {
    if ($nullable && $value === null) {
      $this->statement->bindValue($name, null, PDO::PARAM_NULL);
    } else {
      if (!Validation::isInt($value)) {
        $this->logger->warn("Param $name=" . var_export($value, true) . " is not an int");
      }
      $this->statement->bindValue($name, intval($value), PDO::PARAM_INT);
    }
  }

  public function bindFloat($name, $value, $nullable = false)
  {
    if ($nullable && $value === null) {
      $this->statement->bindValue($name, null, PDO::PARAM_NULL);
    } else {
      if (!is_float($value) && !Validation::isInt($value)) {
        $this->logger->warn("Param $name=" . var_export($value, true) . " is not a float");
      }
      $this->statement->bindValue($name, floatval($value), PDO::PARAM_INT); // sic!
    }
  }

  public function bindBool($name, $value, $nullable = false)
  {
    if ($nullable && $value === null) {
      $this->statement->bindValue($name, null, PDO::PARAM_NULL);
    } else {
      if (!is_bool($value)) {
        $this->logger->warn("Param $name=" . var_export($value, true) . " is not a boolean");
      }
      $this->statement->bindValue($name, !!$value, PDO::PARAM_BOOL);
    }
  }

  public function bindNull($name)
  {
    $this->statement->bindValue($name, null, PDO::PARAM_NULL);
  }

  /*
   * Binding list of arguments is possible only before statement is prepared, use Db::prepareWithIntList()
   */

  // execute shortcuts

  /**
   * Executes the statement and immediately returns first column of first value of the result set.
   * It's recommended to use it only when you'd like to fetch only one column of query which yields exactly one result row.
   * @param array $args array for strings for placeholder arguments in prepared query. Deprecated, use methods bindXXX() instead.
   * @return mixed first column of first fetched row. Null if no result.
   */
  public function executeScalar($args = null)
  {
    if ($args != null) {
      $this->execute($args);
    } else {
      $this->execute();
    }
    $row = $this->fetch(PDO::FETCH_NUM);
    if ($row === false) {
      return null;
    }
    return $row[0];
  }

  public function fetchScalars()
  {
    return $this->fetchAll(PDO::FETCH_COLUMN, 0);
  }

  public function __call($method, $args)
  {
    return call_user_func_array([$this->statement, $method], $args);
  }

  public function exists()
  {
    return count($this->fetchAll()) > 0;
  }
}
