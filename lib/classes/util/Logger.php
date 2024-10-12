<?php

/**
 * Simple logger
 */
class Logger
{

  const OFF = 6;  // Off: no logging at all
  const ERROR = 5;  // Error: error conditions
  const WARN = 4;  // Warning: warning conditions
  const NOTICE = 3;  // Notice: normal but significant condition
  const INFO = 2;  // Informational: informational messages
  const DEBUG = 1;  // Debug: debug messages

  const FORMAT_TEXT = 1;
  const FORMAT_XML = 2;

  const DEFAULT_SEVERITY = self::NOTICE;
  const DEFAULT_FORMAT = self::FORMAT_TEXT;

  private $className; // class name for which logger is created or
  private $severity; // one of predefined constants
  private $format;

  private static $instances = [];

  private function __construct($className, $severity)
  {
    $this->className = $className;
    $this->severity = $severity;
  }

  public static function getLogger($className, $severity = self::DEFAULT_SEVERITY, $format = self::DEFAULT_FORMAT)
  {
    if (isset(self::$instances[$className]) && isset(self::$instances[$className][$severity])) {
      return self::$instances[$className][$severity];
    }
    $logger = new Logger($className, $severity);
    if (!isset(self::$instances[$className])) {
      self::$instances[$className] = [];
    }
    $logger->format = $format;

    self::$instances[$className][$severity] = $logger;
    return $logger;
  }


  public function error($message, $args = null)
  {
    $this->log(self::ERROR, $message, $args);
  }

  public function warn($message, $args = null)
  {
    $this->log(self::WARN, $message, $args);
  }

  public function notice($message, $args = null)
  {
    $this->log(self::NOTICE, $message, $args);
  }

  public function info($message, $args = null)
  {
    $this->log(self::INFO, $message, $args);
  }

  public function debug($message, $args = null)
  {
    $this->log(self::DEBUG, $message, $args);
  }

  public function log($level, $message, $args)
  {
    if ($level < $this->severity) {
      return;
    }

    if (!$args) {
      $stack = debug_backtrace();
      $stackMessage = [];
      for ($i = 1; $i <= 3; $i++) {
        if (isset($stack[$i])) {
          $stackMessage[] = $stack[$i]['file'] . ':' . $stack[$i]['line'];
        }
      }
      $args = implode(" in ", $stackMessage);
    }

    $formatedMessage = $this->formatMessage($this->getLevelName($level), $message, $args);

    error_log($formatedMessage);
  }

  private function formatMessage($levelName, $message, $args)
  {
    if ($this->format == self::FORMAT_TEXT) {
      return $this->formatTextMessage($levelName, $message, $args);
    } elseif ($this->format == self::FORMAT_XML) {
      return $this->formatXmlMessage($levelName, $message, $args);
    }
  }

  private function formatTextMessage($levelName, $message, $args)
  {
    $entry = "LOGGER $levelName [$this->className]: ";
    $entry .= "$message\n ";
    if ($args !== null) {
      if ($args instanceof Exception) {
        $entry .= $args . "\n\n";
      } else {
        $entry .= var_export($args, true) . "\n\n";
      }
    }
    return $entry;
  }

  private function formatXmlMessage($levelName, $message, $args)
  {
    $currentDate = date("D M j G:i:s Y");

    $entry = "<ENTRY LEVEL=\"$levelName\">";

    $entry .= " $currentDate, $this->className\n";
    $entry .= "$message \n";
    if ($args !== null) {
      if ($args instanceof Exception) {
        $entry .= "<CODE> " . $args . " </CODE>\n";
      } else {
        $entry .= "<CODE> " . var_export($args, true) . " </CODE>\n";
      }
    }
    $entry .= "</ENTRY>\n";
    return $entry;
  }

  private function getLevelName($level)
  {
    switch ($level) {
      case self::ERROR:
        return "ERROR";
      case self::WARN;
        return "WARN";
      case self::NOTICE:
        return "NOTICE";
      case self::INFO:
        return "INFO";
      case self::DEBUG:
        return "DEBUG";
      default:
        return "UNKNOWN";
    }
  }
}
