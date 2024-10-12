<?php

require_once(dirname(__DIR__) . '/lib/stddef.inc.php');
require_once(dirname(__DIR__) . '/lib/header.functions.inc.php');

if (_ENV !== 'test') {
  exit('Tests can be run only in test environment');
}

Request::getInstance()
  ->setEnvironmentForTesting(new Environment(_ROOT_LOC . "/tests/config-test.json"));
