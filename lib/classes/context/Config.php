<?php

class Config
{
  private $assocArray;

  public function __construct($configFilePath)
  {
    $this->assocArray = json_decode(file_get_contents($configFilePath), true);
  }

  public function dbHost()
  {
    return $this->assocArray["database"]["host"];
  }

  public function dbUser()
  {
    return $this->assocArray["database"]["user"];
  }

  public function dbPassword()
  {
    return $this->assocArray["database"]["password"];
  }

  public function dbName()
  {
    return $this->assocArray["database"]["name"];
  }

  public function dbErrorLogFilePath()
  {
    return $this->assocArray["database"]["errorLogFile"];
  }

  public function errorLogFilePath()
  {
    return $this->assocArray["errorLogFile"];
  }

  public function environment()
  {
    return $this->assocArray["env"];
  }

  public function subtitle()
  {
    return $this->assocArray["subtitle"];
  }

  public function getTesterScriptsLogFile()
  {
    return $this->assocArray["testerScriptsLogFile"];
  }

  /**
   * Parameter used to redirect from the intro server to the main game.
   *
   * @return string url to redirect to when the request doesn't have $character parameter.
   * Empty string if no redirection should happen
   */
  public function getUrlToRedirectWhenNoCharacter()
  {
    return $this->assocArray["redirectWhenNoCharacter"]["url"];
  }

  public function integratedEnvironments() {
    return $this->assocArray["integratedEnvironments"];
  }

  public function domainUrl() {
    return $this->assocArray["domainUrl"];
  }

  public function devserverMode() {
    return (bool)$this->assocArray["devserverMode"];
  }
}
