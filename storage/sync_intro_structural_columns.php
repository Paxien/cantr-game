<?php

$page = "sync_intro_structural_columns";
include("lib/stddef.inc.php");

$MAIN_TABLE = "cantr_cantr";
$INTRO_TABLE = "cantr_intro";

$dbSynchronizer = new DbSynchronizer($MAIN_TABLE, $INTRO_TABLE);

$dbSynchronizer->synchronizeDataInTable("texts", ["name", "language"]);
$dbSynchronizer->synchronizeDataInTable("objecttypes", ["id"]);
$dbSynchronizer->synchronizeDataInTable("obj_properties", ["objecttype_id", "property_type"]);
$dbSynchronizer->synchronizeDataInTable("rawtypes", ["id"]);
$dbSynchronizer->synchronizeDataInTable("rawtools", ["id"]);
$dbSynchronizer->synchronizeDataInTable("objectcategories", ["id"]);
$dbSynchronizer->synchronizeDataInTable("clothes_categories", ["id"]);
$dbSynchronizer->synchronizeDataInTable("machines", ["id"]);
$dbSynchronizer->synchronizeDataInTable("animal_types", ["id"]);
$dbSynchronizer->synchronizeDataInTable("animal_domesticated_types", ["id"]);
