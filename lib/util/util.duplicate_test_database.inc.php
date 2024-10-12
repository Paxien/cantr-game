<?php
// This script creates a replica of the main cantr production database for
// test purposes. It also fills in some structural data from the live server
// and create a bunch of objects. Currently the test server will contain only
// the island of Teragotha (Shai) and the characters will be there when you log
// on. Finally the script create a dump of the test database that you as a
// developer can use to set up your local cantr test database.

function run_query($query) {
  echo "Query: $query<BR>";
  $db = Db::get();
  return $db->query($query);
}

$env = Request::getInstance()->getEnvironment();

$sourceServer = $env->getDbNameFor("main");
$targetServer  = $env->getDbNameFor("test");

$dbSynchronizer = new DbSynchronizer($sourceServer, $targetServer);

$dbSynchronizer->perform();

// Copy contents from some tables that are too large to copy everything
run_query("INSERT INTO $targetServer.locations SELECT * FROM $sourceServer.locations WHERE x > 579 AND x < (579 + 84) AND y > 962 AND y < (962 + 104)");
run_query("INSERT INTO $targetServer.maps SELECT * FROM $sourceServer.maps WHERE file LIKE '%shai%'");

$loc = 636; //Shai
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (1,  $loc,  2,25,10000,2)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (2,  $loc,  2,26,10000,2)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (3,  $loc, 53, 0,  100,1)");
// Lock on Shai Headquarters
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting,specifics) VALUES (4, 20298, 12, 0,  100,3,'locked')");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (5,  $loc,185, 0,  215,1)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (6,  $loc,142, 0,  300,1)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (7,  $loc, 46, 0,  110,1)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (8,  $loc,156, 0, 2150,3)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (9,  $loc,230, 5,  300,1)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (10, $loc,230, 6,  110,1)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (11, $loc,230, 7,  150,1)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (12, $loc,  2, 8,10000,2)");
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (13, $loc,130, 0,   50,1)");
// Radio sender in Fort James in Shai
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting,specifics) VALUES (14, 1028,520, 0,  100,3,'100')");
// Radio receiver Federation City Hall in Shai
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting,specifics) VALUES (15, 1110,521, 0,  100,3,'100')");
// Radio receiver in The Sea Drake
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting,specifics) VALUES (16,15736,522, 0,  100,3,'')");
// A sextant on the ground in Shai
run_query("INSERT INTO $targetServer.objects (id,location,type,typeid,weight,setting)           VALUES (17, $loc,635, 0,   50,1)");

run_query("INSERT INTO $targetServer.animals (location,type,number,damage) VALUES ($loc,1,30,0)");

run_query("INSERT INTO $targetServer.animals (location,type,number,damage) VALUES ($loc,2,20,0)");

run_query("INSERT INTO $targetServer.diseases (person,disease,infector,date,specifics) VALUES (1,1,0,1,'')");

#to fix wrong chars spawning on TE.
foreach( $langcode as $code => $name ) {
    run_query("INSERT INTO $targetServer.spawninglocations VALUES ( $loc, $code )");
}

//create test player - cantr_test, pass: cantr_qwerty
run_query("INSERT INTO `$targetServer`.players VALUES (100, 'cantr_test', 'firstname', 'lastname', 'test@test.test', 'cantr_test',
  'cantr_test', 1987, 'England', 1, 'b31647ea9af4b008616411873e05db0b', 3170, 3701, 6, 0,
   '09/08/2012 12:37 89.67.123.34 (89-67-123-34.dynamic.chello.pl)', 766, 1875, 49984, 0, 0, 1, 1, 3, 0, 64543, 0, 1, 1,
   NULL, NULL, 8, 0, 0, NULL, 0, 0)");

$accessList = '';
for($a = 1; $a <= 46; $a++ ) $accessList .= "( 118841, $a ),";
$accessList = substr( $accessList, 0, strlen( $accessList ) -1 );

#giving test user full access
run_query("INSERT INTO $targetServer.access VALUES $accessList");
