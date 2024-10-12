<?php

/**
 * Stores request-specific data, like request type, http/https etc.
 * also has info about player who caused the request
 * Can be called ONLY AFTER session-specific actions are finished (checking cookie time, player id etc)
 * Singleton pattern
 */
class Request
{
  private static $instance;

  private $isHttps;
  private $environment;
  private $domainAddress;

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct()
  {
    $this->isHttps = !empty($_SERVER['HTTPS']);
    $this->domainAddress = ($this->isHttps() ? "https://" : "http://") . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
  }

  public function isHttps()
  {
    return $this->isHttps;
  }

  /**
   * @deprecated use Config::domainUrl instead
   */
  public function getDomainAddress()
  {
    return $this->domainAddress;
  }

  public function getSessionId()
  {
    $sessionId = Session::getSessionFromCookie();
    return $sessionId;
  }

  public function isPlayerSpecified()
  {
    return ($this->getPlayerId() != 0);
  }

  /**
   * @return Player
   * @throws InvalidArgumentException if no existing player is logged in
   */
  public function getPlayer()
  {
    return Player::loadById($this->getPlayerId());
  }

  public function getPlayerId()
  {
    $playerId = intval($GLOBALS['player']);
    return $playerId;
  }

  public function getEnvironment()
  {
    if ($this->environment === null) {
      $this->environment = new Environment();
    }
    return $this->environment;
  }

  public function isAjax()
  {
    return !empty($GLOBALS['$ajaxRequest']);
  }

  public function setEnvironmentForTesting($environment) {
    $this->environment = $environment;
  }
}
