<?php

echo "Running creator of a docker-specific Cantr config...\n";

$ROOT_DIR = "../";
$CONFIG_PATH = "config/config.json";
$DEFAULT_CONFIG_PATH = "config/config.json.default";

if (file_exists($ROOT_DIR . $CONFIG_PATH)) {
  echo "Config file already exists in $CONFIG_PATH. Delete it manually if you want to generate a new one.\n";
} else {
  echo "Setting up database credentials in config to docker-specific defaults.\n";

  $configJson = json_decode(file_get_contents($ROOT_DIR . $DEFAULT_CONFIG_PATH), true);
  $configJson["env"] = "test";
  $configJson["domainUrl"] = "http://localhost:8083";
  $configJson["database"]["host"] = "lamp";
  $configJson["database"]["user"] = "root";
  $configJson["database"]["password"] = "";
  $configJson["database"]["name"] = "cantr_test";

  $configJson["integratedEnvironments"] = new stdClass();

  $configAsString = json_encode($configJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  file_put_contents($ROOT_DIR . $CONFIG_PATH . ".temp", $configAsString);
  echo "New config file has been created in $CONFIG_PATH.temp\n";
}
