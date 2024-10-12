<?php


/**
 * Stores data about game environment (its type and other environments)
 * Instantiation through Request class
 * Requires stddef.inc.php to work correctly
 */
class Environment
{
  /** @var Config */
  private $config;
  /**
   * @var string
   */
  private $pathToConfig;

  public function __construct($pathToConfig = null)
  {
    if ($pathToConfig === null) {
      $pathToConfig = $this->getRootPath() . "/config/config.json";
    }
    $this->pathToConfig = $pathToConfig;
  }

  /**
   * Returns current environment. Similar to variable _ENV from lib/stddef.inc.php
   */
  public function is($envName)
  {
    switch ($envName) {
      case "www":
      case "main":
        return ($this->getName() == "www");
      case "test":
        return ($this->getName() == "test");
      case "intro":
        return ($this->getName() == "intro");
      default:
        return false;
    }
  }

  /**
   * @return string www/test/intro
   */
  public function getName()
  {
    return $this->getConfig()->environment();
  }

  public function getFullName()
  {
    switch ($this->getName()) {
      case "www":
        return "[LIVE]";
      case "test":
        return "[TEST]";
      case "intro":
        return "[INTRO]";
      default:
        return "[???]";
    }
  }

  public function introExists()
  {
    return $this->isEnvironmentIntegrated("intro");
  }

  public function getIntroSubdomainAddress()
  {
    return $this->getConfig()->integratedEnvironments()["intro"]["url"];
  }

  public function getParentDomainAddress()
  {
    return $this->getConfig()->integratedEnvironments()["main"]["url"];
  }

  private function isEnvironmentIntegrated($envName)
  {
    return array_key_exists($envName, $this->getConfig()->integratedEnvironments());
  }

  /**
   * Returns root directory of current environment.
   * @return string root dir path without trailing "/"
   */
  public function getRootPath()
  {
    return _ROOT_LOC;
  }

  public function absoluteOrRelativeToRootPath($path)
  {
    if (StringUtil::startsWith($path, "/")) { // absolute
      return $path;
    }
    return _ROOT_LOC . "/" . $path;
  }

  public function getDbNameFor($envName)
  {
    $config = $this->getConfig();
    $environment = $config->environment();
    if ($environment === "www") { // temp until it's changed to "main" in main server's config
      $environment = "main";
    }
    if ($envName === "www") {
      $envName = "main";
    }
    if ($environment === $envName) {
      return $config->dbName();
    }
    if (!$this->isEnvironmentIntegrated($envName)) {
      throw new InvalidArgumentException("environment '$envName' doesn't exist");
    }
    $integratedEnvironment = $config->integratedEnvironments()[$envName];
    return $integratedEnvironment["databaseName"];
  }

  public function getConfig()
  {
    if ($this->config === null) {
      $this->config = new Config($this->pathToConfig);
    }
    return $this->config;
  }
}
