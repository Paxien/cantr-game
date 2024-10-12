<?php

$page = "server.weather";

include_once("server.header.inc.php");

$weatherMachine = new WeatherMachine();

$weatherMachine->run();

include_once("server.footer.inc.php");
