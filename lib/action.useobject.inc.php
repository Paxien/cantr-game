<?php
include_once("func.expireobject.inc.php");
include_once("func.projectsetup.inc.php");

// SANITIZE INPUT
$character = HTTPContext::getInteger('character');
$object_id = HTTPContext::getInteger('object_id');
$project = HTTPContext::getInteger('project');

use_object($character,$object_id,$project);

redirect("char.inventory");
