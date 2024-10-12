<?php

require_once("../lib/stddef.inc.php");

$config = Request::getInstance()->getEnvironment()->getConfig();

echo "Dropping cantr_int_test\n";
$command = "mysql --user={$config->dbUser()} --password='{$config->dbPassword()}' "
  . "-h {$config->dbHost()} --skip-column-names --batch "
  . "-e 'DROP DATABASE IF EXISTS cantr_int_test; CREATE DATABASE cantr_int_test'";
shell_exec($command);

echo "Creating dump of cantr_test\n";
$command = "mysqldump --user={$config->dbUser()} --password='{$config->dbPassword()}' "
  . "-h {$config->dbHost()} --no-data cantr_test > cantr_int_test_dump.sql";
shell_exec($command);

echo "Creating cantr_int_test from dump\n";
$command = "mysql --user={$config->dbUser()} --password='{$config->dbPassword()}' "
  . "-h {$config->dbHost()} cantr_int_test < cantr_int_test_dump.sql";
shell_exec($command);

unlink("cantr_int_test_dump.sql");

echo "Inserting needed data to the table\n";
$db = DbFactory::getInstance()->getDb("cantr_int_test", $config);
$db->query("INSERT INTO turn (number, part, day, hour, minute, second) VALUES (6000, 1, 6000, 1, 2, 3)");

echo "Done\n";