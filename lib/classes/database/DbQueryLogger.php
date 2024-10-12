<?php

class DbQueryLogger
{
  /**
   * @param $handle CObject that has method errorInfo (PDO, PDOStatement or wrapper of any of these)
   */
  public function __construct($handle)
  {
    $this->handle = $handle;
  }

  public function logError($query, $stack, Exception $exception)
  {
    $char = $GLOBALS['character'];
    $date = date("D M d H:i:s Y");

    $stackmess = '';
    if (isset($stack[0])) {
      $stackmess .= $stack[0]['file'] . ', line ' . $stack[0]['line'];
    }
    $env = Request::getInstance()->getEnvironment();
    error_log("[$date] [$stackmess] QUERY error : '$query', character: $char, exception: '$exception'.\r\n",
      3, $env->absoluteOrRelativeToRootPath($env->getConfig()->dbErrorLogFilePath()));
  }
}
