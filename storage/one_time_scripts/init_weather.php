<?php

/**
 * Script used to remove old weather data and create new one
 * Creates default location of seasons, climates, pressure areas and cells, based on WeatherConstants
 */
error_reporting(E_ALL);
$page = "init_weather";
include("../../lib/stddef.inc.php");

$dbh = new database(); // old db driver, the script would need to be migrated
$dbh->open();

mt_srand(36212);

function randomPointOnMap()
{
  return (object) array(
    "x" => mt_rand(0, MapConstants::MAP_WIDTH - 1),
    "y" => mt_rand(0, MapConstants::MAP_HEIGHT - 1)
  );
}

function randomWithoutMiddle()
{
  $v = mt_rand(-10, 30);
  if ($v < 10) { // [-10, 30] -> [-30, -10) \sum [10, 30]
    $v -= 20;
  }
  return $v;
}

function randomPressureAreaSpeed()
{
  return (object) array(
    "x" => randomWithoutMiddle(),
    "y" => randomWithoutMiddle(),
  );
}

/**
 * PRESSURE AREAS
 * They affect weather coefficient by +3 or -3
 * Speed is in range [-30, -10) \sum [10, 30] to not allow too slow ones
 */

do_query("TRUNCATE weather_pressure_areas");

$pressureAreas = array();
for ($i = 1; $i <= WeatherConstants::PRESSURE_AREAS; $i++) {
  $pos = randomPointOnMap();
  $v = randomPressureAreaSpeed();
  $influence = (($i % 2) ? 2 : -2);

  $pressureAreas[] = "($pos->x, $pos->y, $v->x, $v->y, $influence, 1)";
}
do_query("INSERT INTO weather_pressure_areas (x, y, v_x, v_y, influence, mobile)
  VALUES ". implode(", ", $pressureAreas));

/**
 * CELLS
 */

do_query("TRUNCATE weather_cells");

$colsPerSeason = (WeatherConstants::GRID_COLS/4);
for ($col = 0; $col < WeatherConstants::GRID_COLS; $col++) {
  $season = 4 - floor($col/$colsPerSeason);
  $rowData = array();
  for ($row = 0; $row < WeatherConstants::GRID_ROWS; $row++) {
    $rowData[] = "($col, $row, 0, ". WeatherConstants::CLIMATE_TEMPERATE .", $season)";
  }
  
  do_query("INSERT INTO weather_cells (col, row, coefficient, climate, season) VALUES ". implode(", ", $rowData));
}

do_query("TRUNCATE weather_seasons");

$rightCol = WeatherConstants::GRID_COLS - 1;
for ($season = 1; $season <= 4; $season++) {
  do_query("INSERT INTO weather_seasons (id, rightmost_column, deviation) VALUES ($season, $rightCol, 0)");
  $rightCol -= $colsPerSeason;
}

/**
 * tropical climate for jungle regions
 * - tropical climate is in all cells which have at least 1 jungle location
 */

$ref = do_query("SELECT DISTINCT
  FLOOR(x/". WeatherConstants::CELL_SIZE .") AS col, FLOOR(y/". WeatherConstants::CELL_SIZE .") AS row
  FROM locations l WHERE l.type = 1 AND l.area = 12");

$tropicalCells = array();
foreach (fetch_all($ref) as $pos) {
  $tropicalCells[] = " OR (col = $pos->col AND row = $pos->row)";
}

/**
 * Dry climate for deserts
 * - dry climate is in all locations which have at least 1 desert location which
 *     has road to any desert or jungle region or doesn't have access to sea
 *
 * These rules for desert are required to exclude artifacts - some big beaches
 *   have area type "desert", just because there's much sand around :D It wouldn't make sense to create
 *   isolated tropical climate for some coasts
 */

$ref = do_query("SELECT DISTINCT
  FLOOR(x/". WeatherConstants::CELL_SIZE .") AS col, FLOOR(y/". WeatherConstants::CELL_SIZE .") AS row
  FROM locations l WHERE type = ". LocationConstants::TYPE_OUTSIDE ." AND l.area = " . ObjectConstants::TYPE_TERRAIN_DESERT . "
  AND (
    (
      ((SELECT c1.start_area FROM connections c1 WHERE c1.end = l.id LIMIT 1) = " . ObjectConstants::TYPE_TERRAIN_DESERT . ")
        OR
      ((SELECT c2.end_area FROM connections c2 WHERE c2.start = l.id LIMIT 1) = " . ObjectConstants::TYPE_TERRAIN_DESERT . ")
    ) OR (
      l.borders_sea = 0
    )
  )"
);

$dryCells = array();
foreach (fetch_all($ref) as $pos) {
  $dryCells[] = " OR (col = $pos->col AND row = $pos->row)";
}


/**
 * steppe climate for cells which have a swamp or tundra location and no non-tundra and non-swamp locations
 */

$ref = do_query("SELECT DISTINCT
  FLOOR(l1.x / ". WeatherConstants::CELL_SIZE .") AS col, FLOOR(l1.y / ". WeatherConstants::CELL_SIZE .") AS row
  FROM locations l1 WHERE l1.type = ". LocationConstants::TYPE_OUTSIDE ." AND l1.area IN (5, 15)
  AND NOT EXISTS
  (SELECT l2.id FROM locations l2 WHERE
    type = ". LocationConstants::TYPE_OUTSIDE ." AND area NOT IN (5, 15) AND
      l2.x >= FLOOR(l1.x / ". WeatherConstants::CELL_SIZE .") * ". WeatherConstants::CELL_SIZE ."
      AND l2.x < FLOOR(l1.x / ". WeatherConstants::CELL_SIZE ." + 1) * ". WeatherConstants::CELL_SIZE ."
    AND
      l2.y >= FLOOR(l1.y / ". WeatherConstants::CELL_SIZE .") * ". WeatherConstants::CELL_SIZE ."
      AND l2.y < FLOOR(l1.y / ". WeatherConstants::CELL_SIZE ." + 1) * ". WeatherConstants::CELL_SIZE ."
  )
");

$steppeCells = array();
foreach (fetch_all($ref) as $pos) {
  $steppeCells[] = " OR (col = $pos->col AND row = $pos->row)";
}

/**
 * mountain climate for cells which have a mountain location and no non-mountain locations
 */

$ref = do_query("SELECT DISTINCT
  FLOOR(l1.x / ". WeatherConstants::CELL_SIZE .") AS col, FLOOR(l1.y / ". WeatherConstants::CELL_SIZE .") AS row
  FROM locations l1 WHERE l1.type = ". LocationConstants::TYPE_OUTSIDE ." AND l1.area = 2
  AND NOT EXISTS
  (SELECT l2.id FROM locations l2 WHERE
    type = ". LocationConstants::TYPE_OUTSIDE ." AND area != 2 AND
      l2.x >= FLOOR(l1.x / ". WeatherConstants::CELL_SIZE .") * ". WeatherConstants::CELL_SIZE ."
      AND l2.x < FLOOR(l1.x / ". WeatherConstants::CELL_SIZE ." + 1) * ". WeatherConstants::CELL_SIZE ."
    AND
      l2.y >= FLOOR(l1.y / ". WeatherConstants::CELL_SIZE .") * ". WeatherConstants::CELL_SIZE ."
      AND l2.y < FLOOR(l1.y / ". WeatherConstants::CELL_SIZE ." + 1) * ". WeatherConstants::CELL_SIZE ."
  )
");

$mountainCells = array();
foreach (fetch_all($ref) as $pos) {
  $mountainCells[] = " OR (col = $pos->col AND row = $pos->row)";
}

do_query("UPDATE weather_cells SET climate = ". WeatherConstants::CLIMATE_MOUNTAIN ."
  WHERE 1=0 ". implode("", $mountainCells));

do_query("UPDATE weather_cells SET climate = ". WeatherConstants::CLIMATE_STEPPE ."
  WHERE 1=0 ". implode("", $steppeCells));

do_query("UPDATE weather_cells SET climate = ". WeatherConstants::CLIMATE_DRY ."
  WHERE 1=0 ". implode("", $dryCells));

do_query("UPDATE weather_cells SET climate = ". WeatherConstants::CLIMATE_TROPICAL ."
  WHERE 1=0 ". implode("", $tropicalCells));

/**
 * Manual improvements
 */

do_query("UPDATE weather_cells SET climate = ". WeatherConstants::CLIMATE_TEMPERATE ." WHERE
  (col = 6 AND row = 26) OR (col = 10 AND row = 29)"); // remove steppe climate from lonely swamps on Cantr Island

do_query("UPDATE weather_cells SET climate = ". WeatherConstants::CLIMATE_TEMPERATE ." WHERE
  (col = 16 AND row = 39)"); // small Bulgarian town on Rus-Pok


