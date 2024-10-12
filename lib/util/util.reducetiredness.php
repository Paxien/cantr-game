<?php

$db = Db::get();
$db->query("UPDATE states SET value = 0 WHERE type = 10");
