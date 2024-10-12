<?php

// *** NUMBER OF PLAYERS
$db = Db::get();

$stm = $db->prepare("SELECT COUNT(*) AS number_of_players FROM players");
$numberOfPlayers = $stm->executeScalar();

echo "<TR><TD># of players</TD><TD>$numberOfPlayers</TD></TR>";


// *** NUMBER OF CHARACTERS
$stm = $db->prepare("SELECT COUNT(*) FROM chars WHERE status <= " . CharacterConstants::CHAR_ACTIVE);
$numberOfCharacters = $stm->executeScalar();

echo "<TR><TD># of characters</TD><TD>$numberOfCharacters</TD></TR>";


// *** NUMBER OF WALKING CHARACTERS
$stm = $db->prepare("SELECT COUNT(*) FROM travels WHERE type = 0");
$numberOfWalkingCharacters = $stm->executeScalar();

echo "<TR><TD># of walking characters</TD><TD>$numberOfWalkingCharacters </TD></TR>";


// *** NUMBER OF BIKING CHARACTERS
$stm = $db->prepare("SELECT COUNT(*) FROM travels
  WHERE type IN (SELECT id FROM objecttypes
    WHERE unique_name IN ('bike', 'tandem_bike', 'rickshaw', 'tricycle', 'bike_cart')
  )");
$numberOfBikingCharacters = $stm->executeScalar();

echo "<TR><TD># of biking characters</TD><TD>$numberOfBikingCharacters</TD></TR>";

// *** NUMBER OF MOTOR-VEHICLE-DRIVING CHARACTERS
$stm = $db->prepare("SELECT COUNT(*) FROM travels
  WHERE type IN (SELECT id FROM objecttypes WHERE rules LIKE '%engine%')");
$numberOfCarDrivingCharacters = $stm->executeScalar();

echo "<TR><TD># of car-driving characters</TD><TD>$numberOfCarDrivingCharacters</TD></TR>";

// *** NUMBER OF SAILING CHARACTERS
$stm = $db->prepare("SELECT COUNT(*) AS number_of_sailing_characters FROM chars WHERE location IN (SELECT vessel FROM sailing)");
$numberOfSailingCharacters = $stm->executeScalar();

echo "<TR><TD># of sailing characters</TD><TD>$numberOfSailingCharacters</TD></TR>";


// *** AMOUNT OF STEEL

$stm = $db->prepare("SELECT SUM(weight) FROM objects WHERE type=" . ObjectConstants::TYPE_RAW . " AND typeid = :rawType");

$steelRawType = CObject::getRawIdFromName("steel");
$stm->bindInt("rawType", $steelRawType);
$amountOfSteel = $stm->executeScalar();

echo "<TR><TD>amount of steel</TD><TD>$amountOfSteel</TD></TR>";

$aluminaRawType = CObject::getRawIdFromName("alumina");
$stm->bindInt("rawType", $aluminaRawType);
$amountOfAlumina = $stm->executeScalar();

echo "<TR><TD>amount of alumina</TD><TD>$amountOfAlumina</TD></TR>";


// *** AMOUNT OF ALUMINIUM

$aluminiumRawType = CObject::getRawIdFromName("aluminium");
$stm->bindInt("rawType", $aluminaRawType);
$amountOfAluminium = $stm->executeScalar();

echo "<TR><TD>amount of aluminium</TD><TD>$amountOfAluminium</TD></TR>";


// *** NUMBER OF IRON SHIELDS

$stm = $db->prepare("SELECT COUNT(*) FROM objects WHERE type = (SELECT id FROM objecttypes WHERE name='iron shield')");
$numberOfIronShields = $stm->executeScalar();

echo "<TR><TD># of iron shields</TD><TD>$numberOfIronShields</TD></TR>";


// *** NUMBER OF DRILLS
$stm = $db->prepare("SELECT * FROM objects WHERE type IN (SELECT id FROM objecttypes WHERE name LIKE '%drill')");
$numberOfDrills = $stm->executeScalar();

echo "<TR><TD># of drills</TD><TD>$numberOfDrills</TD></TR>";


// *** NUMBER OF SMELTING FURNACES

$stm = $db->prepare("SELECT COUNT(*) FROM objects WHERE type = (SELECT id FROM objecttypes WHERE name='smelting furnace')");
$numberOfSmeltingFurnaces = $stm->executeScalar();

echo "<TR><TD># of smelting furnaces</TD><TD>$numberOfSmeltingFurnaces</TD></TR>";


// *** NUMBER OF BIKES

$stm = $db->prepare("SELECT COUNT(*) FROM locations WHERE type = " . LocationConstants::TYPE_VEHICLE . "
  AND area IN (SELECT id FROM objecttypes
    WHERE unique_name IN ('bike', 'tandem_bike', 'rickshaw', 'tricycle', 'bike_cart')
  )");
$numberOfBikes = $stm->executeScalar();

echo "<TR><TD>># of bikes</TD><TD>$numberOfBikes</TD></TR>";


// *** NUMBER OF MOTOR VEHICLES
$stm = $db->prepare("SELECT COUNT(*) FROM locations WHERE area IN (SELECT id FROM objecttypes WHERE rules LIKE '%engine%')");
$numberOfMotorVehicles = $stm->executeScalar();

echo "<TR><TD># of motor vehicles</TD><TD>$numberOfMotorVehicles</TD></TR>";

// *** NUMBER OF BOATS
$stm = $db->prepare("SELECT COUNT(*) FROM locations WHERE area IN (SELECT id FROM objecttypes WHERE rules LIKE '%dock%')");
$numberOfBoats = $stm->executeScalar();

echo "<TR><TD># of boats</TD><TD>$numberOfBoats</TD></TR>";
