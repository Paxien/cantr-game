<?php

$db = Db::get();
$db->query("UPDATE projects SET turnsleft = 1, reqleft = '', reqneeded = ''");
