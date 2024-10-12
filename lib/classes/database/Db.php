<?php

/**
 * Class Db
 *
 * List of magic methods to make code completion work in IDEs:
 * @method bool beginTransaction (  );
 * @method bool commit (  );
 * @method mixed errorCode (  );
 * @method array errorInfo (  );
 * @method mixed getAttribute ( int $attribute );
 * @method static array getAvailableDrivers (  );
 * @method bool inTransaction (  );
 * @method string lastInsertId ( string $name = NULL  );
 * @method string quote ( string $string , int $parameter_type = PDO::PARAM_STR  );
 * @method bool rollBack (  );
 * @method bool setAttribute ( int $attribute , mixed $value );
 */
class Db
{
  private $handle;
  private $queryLogger;

  /**
   * Returns an instance of Db based on global configuration
   * @param $envName string environment name or null when default one
   * @return Db
   */
  public static function get($envName = null)
  {
    $env = Request::getInstance()->getEnvironment();
    $config = $env->getConfig();
    if ($envName === null) {
      $envName = $config->environment();
    }
    $dbName = $env->getDbNameFor($envName);
    return DbFactory::getInstance()->getDb($dbName, $config);
  }

  public function __construct(PDO $handle)
  {
    $this->handle = $handle;
    $this->queryLogger = new DbQueryLogger($this->handle);
  }

  public function query()
  {
    list ($usec, $sec) = explode(" ", microtime());
    $startTime = $usec + $sec % 10000000;
    try {
      $args = func_get_args();
      $res = call_user_func_array([$this->handle, "query"], $args);
      // end elapsed time check - should be in finally
      list ($usec, $sec) = explode(" ", microtime());
      $time = $usec + $sec % 10000000 - $startTime;
    } catch (Exception $e) {
      // end elapsed time check - should be in finally
      list ($usec, $sec) = explode(" ", microtime());
      $time = $usec + $sec % 10000000 - $startTime;

      $stack = debug_backtrace();
      $this->queryLogger->logError($args[0], $stack, $e);
      throw $e;
    }
    $GLOBALS['sqltime'] += $time;
    $GLOBALS['sqlcount']++;
    return new DbStatement($res);
  }

  public function exec()
  {
    list ($usec, $sec) = explode(" ", microtime());
    $startTime = $usec + $sec % 10000000;

    try {
      $args = func_get_args();
      $res = call_user_func_array([$this->handle, "exec"], $args);
      // end elapsed time check - should be in finally
      list ($usec, $sec) = explode(" ", microtime());
      $time = $usec + $sec % 10000000 - $startTime;
    } catch (Exception $e) {
      // end elapsed time check - should be in finally
      list ($usec, $sec) = explode(" ", microtime());
      $time = $usec + $sec % 10000000 - $startTime;

      $stack = debug_backtrace();
      $this->queryLogger->logError($args[0], $stack, $e);
      throw $e;
    }
    $GLOBALS['sqltime'] += $time;
    $GLOBALS['sqlcount']++;
    return $res;
  }

  public function prepare()
  {
    list ($usec, $sec) = explode(" ", microtime());
    $startTime = $usec + $sec % 10000000;

    try {
      $args = func_get_args();
      $res = call_user_func_array([$this->handle, "prepare"], $args);
      // end elapsed time check - should be in finally
      list ($usec, $sec) = explode(" ", microtime());
      $time = $usec + $sec % 10000000 - $startTime;
    } catch (Exception $e) {
      // end elapsed time check - should be in finally
      list ($usec, $sec) = explode(" ", microtime());
      $time = $usec + $sec % 10000000 - $startTime;

      $stack = debug_backtrace();
      $this->queryLogger->logError($args[0], $stack, $e);
      throw $e;
    }
    $GLOBALS['sqltime'] += $time;
    $GLOBALS['sqlcount']++;
    return new DbStatement($res);
  }

  public function prepareWithIntList($statement, $arrays, $driverOptions = [])
  {
    foreach ($arrays as $placeholder => $intList) {
      $intList = Pipe::from($intList)->map(function($element) {
        return intval($element);
      })->toArray();

      if (!StringUtil::startsWith($placeholder, ":")) {
        $placeholder = ":" . $placeholder;
      }

      $intList = implode(",", $intList);
      $statement = preg_replace("/($placeholder)([^a-zA-Z]|$)/", $intList . '$2', $statement, 1); // interpolates the int list
    }

    return $this->prepare($statement, $driverOptions);
  }

  public function prepareWithList($statement, $arrays, $driverOptions = [])
  {
    foreach ($arrays as $placeholder => $strList) {
      $strList = Pipe::from($strList)->map(function($element) {
        return $this->quote($element);
      })->toArray();

      if (!StringUtil::startsWith($placeholder, ":")) {
        $placeholder = ":" . $placeholder;
      }

      $strList = implode(",", $strList);
      $statement = preg_replace("/($placeholder)([^a-zA-Z]|$)/", $strList . '$2', $statement, 1); // interpolates the string list
    }

    return $this->prepare($statement, $driverOptions);
  }

  public function __call($method, $args)
  {
    return call_user_func_array([$this->handle, $method], $args);
  }

}
