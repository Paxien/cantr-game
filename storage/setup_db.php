<?php

require_once("../lib/stddef.inc.php");

$config = Request::getInstance()->getEnvironment()->getConfig();

$command = "mysql --user={$config->dbUser()} --password='{$config->dbPassword()}' "
  . "-h {$config->dbHost()} --skip-column-names --batch -e 'SHOW DATABASES LIKE \"{$config->dbName()}\"' | wc -l";
$existingDatabases = shell_exec($command);

if ($existingDatabases == 0) {
  echo "No database with name {$config->dbName()}. Creating one using initial_db.sql\n";
  $command = "mysql --user={$config->dbUser()} --password='{$config->dbPassword()}' "
    . "-h {$config->dbHost()} -e 'CREATE DATABASE {$config->dbName()}'";
  $output = shell_exec($command);
  echo "$output\n";

  $command = "mysql --user={$config->dbUser()} --password='{$config->dbPassword()}' "
    . "-h {$config->dbHost()} -D {$config->dbName()} < initial_db.sql";
  $output = shell_exec($command);
  echo "$output\n";
} else {
  echo "Database {$config->dbName()} exists, so only trying to perform migration\n";
}

$db = Db::get();
$migrationManager = new MigrationManager($db);

$migratingFrom = $migrationManager->getCurrentDbVersion();
$migratingTo = $migrationManager->getVersionOfLastMigration();
if ($migratingFrom === $migratingTo) {
  echo "Database is already in version $migratingTo, no migration is performed\n";
} else {
  echo "Performing migration from version $migratingFrom to $migratingTo\n";

  $migrationManager->performMigration();

  echo "Migrated to version " . $migrationManager->getCurrentDbVersion() . "\n";
}
