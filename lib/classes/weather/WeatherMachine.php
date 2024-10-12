<?php


/**
 * Responsible for processing season and weather change in all locations of the world
 * World is divided into cells by 120x120 grid (so each is 50x50 pixels).
 * Each season takes 30 cols of the map. It moves slowly by 1 or 2 cols (to IG north) every processing iteration.
 * Number of cols passed every day is random, but it's capped by getting deviation from earlier iterations,
 * which reduces standard deviation. Expected value = 1.5, it's IMPOSSIBLE for season to take <10 or >30 days
 * Weather is mainly based on random number generator. It gives rand in range [-8,8], which is called coefficient.
 * Coefficient is later affected by coefficients of pressure areas in the same of 8 adjacent cells (3x3 area)
 * XXX
 * XCX
 * XXX
 * C - center cell, X - cells whose pressure areas can affect center cell
 * Coefficient with climate and season are used to determine weather type in every cell (see class Weather)
 *
 * Wind is fully deterministic. It's based on sum of speeds of all pressure areas in 7x7 cells area
 * Then it's mapped by a special function which guarantees almost uniform distribution of winds across the world
 * so relative wind speed is value in range [0, 1].
 * Every pressure area has own _x and _y speed. It's speed which is used to update pressure areas location,
 * unless pressure area is set as immobile (mobile=0) which was done to allow making stationary ocean currents
 */
class WeatherMachine
{
  private $seasons = []; // key - season id, value - leftmost column in grid with this season
  private $cells = []; // cells specified by [col][row]
  private $pressureAreas = []; // list of pressure areas in cell [col][row]
  private $speedDistribution = [];
  private $cumulativeDistribution = [];
  /** @var Db */
  private $db;

  /**
   * loads data from the database
   */
  public function __construct()
  {
    $this->db = Db::get();
    /**
     * seasons data
     */

    $stm = $this->db->query("SELECT * FROM weather_seasons");
    foreach ($stm->fetchAll() as $season) {
      $this->seasons[$season->id] = (object)[
        "rightmost_column" => $season->rightmost_column,
        "deviation" => $season->deviation,
      ];
    }

    $stm = $this->db->query("SELECT * FROM weather_cells");
    $cells = $stm->fetchAll();

    /**
     * cells data
     */

    for ($x = 0; $x < WeatherConstants::GRID_COLS; $x++) {
      $this->cells[$x] = [];
    }

    foreach ($cells as $cell) {
      if ($cell->col < WeatherConstants::GRID_COLS && $cell->row < WeatherConstants::GRID_ROWS) {
        $this->cells[$cell->col][$cell->row] = $cell;
      }
    }

    /**
     * pressure areas data
     */

    $stm = $this->db->query("SELECT * FROM weather_pressure_areas");
    $pressureAreas = $stm->fetchAll();

    for ($x = 0; $x < WeatherConstants::GRID_COLS; $x++) {
      $this->pressureAreas[$x] = [];
      for ($y = 0; $y < WeatherConstants::GRID_ROWS; $y++) {
        $this->pressureAreas[$x][$y] = [];
      }
    }

    foreach ($pressureAreas as $area) {
      $col = floor($area->x / WeatherConstants::CELL_SIZE);
      $row = floor($area->y / WeatherConstants::CELL_SIZE);
      $this->pressureAreas[$col][$row][] = $area;
    }
  }

  /**
   * Makes all the calculations concerning weather, wind, seasons and saves them in db
   */
  public function run()
  {

    /**
     * Seasons change
     */

    // Expected value = 1.5; so season takes about 1 year
    $random = mt_rand(1001, 1999) / 1000;
    // deviation is used to reduce number of season length anomalies (reduces standard deviation)
    $deviation = $this->seasons[WeatherConstants::SEASON_SPRING]->deviation;

    $changeSpeed = round($random - $deviation / 10); // usually 1 or 2, slightly affected by deviation based on history
    $newDeviation = $deviation + ($changeSpeed - 1.5);

    foreach ($this->seasons as $season => $seasonData) {
      $colsAffected = range($seasonData->rightmost_column + 1, $seasonData->rightmost_column + $changeSpeed);
      foreach ($colsAffected as $col) {
        $realCol = $this->inGrid($col);
        for ($row = 0; $row < WeatherConstants::GRID_ROWS; $row++) {
          $this->cells[$realCol][$row]->season = $this->seasonAfter($this->cells[$realCol][$row]->season);
        }
        echo "changing season of $realCol to $season\n";
      }

      Event::createEventInRectangle(329, "SEASON=" . $season,
        $this->inGrid($this->seasons[$season]->rightmost_column + 1) * WeatherConstants::CELL_SIZE, 0,
        ($changeSpeed * WeatherConstants::CELL_SIZE) - 1, MapConstants::MAP_HEIGHT);

      $this->seasons[$season]->rightmost_column = $this->inGrid($this->seasons[$season]->rightmost_column + $changeSpeed);
      $this->seasons[$season]->deviation = $newDeviation;
    }

    /**
     * Cell-specific processing
     */

    $weatherChangeEvents = [];

    foreach ($this->cells as $col => $rows) {

      /**
       * Wind preprocessing
       */

      $pressureRange = WeatherConstants::WIND_PRESSURE_RANGE;

      $wind = ["x" => 0, "y" => 0];
      foreach (range($col - $pressureRange, $col + $pressureRange) as $arCol) {
        foreach (range(0 - $pressureRange, 0 + $pressureRange) as $arRow) {

          foreach ($this->pressureAreas[$this->inGrid($arCol)][$this->inGrid($arRow)] as $area) {
            $wind["x"] += $area->v_x;
            $wind["y"] += $area->v_y;
          }
        }
      }

      foreach ($rows as $row => $cell) {

        /**
         * Weather lottery
         */

        $coefficient = mt_rand(WeatherConstants::COEFFICIENT_RAND_MIN, WeatherConstants::COEFFICIENT_RAND_MAX);

        /*
         * Affecting weather in cell by pressure areas being in proximity
         */

        foreach (range($col - 1, $col + 1) as $arCol) {
          foreach (range($row - 1, $row + 1) as $arRow) {
            foreach ($this->pressureAreas[$this->inGrid($arCol)][$this->inGrid($arRow)] as $area) {
              $coefficient += $area->influence;
            }
          }
        }

        $this->cells[$col][$row]->coefficient = $coefficient;

        // agricultural conditions based on cell weather
        $cellWeather = new Weather((object)["climate" => $cell->climate,
          "season" => $cell->season, "coefficient" => $coefficient,
          "relative_wind_x" => 0, "relative_wind_y" => 0, "col" => $col, "row" => $row]
        );
        $agricultural = WeatherMapping::$AGRICULTURAL_CONDITIONS[$cellWeather->getWeatherType()];

        $dailyInsolationChange = $cell->insolation >= WeatherConstants::BELOW_AVERAGE_INSOLATION ? WeatherConstants::DAILY_INSOLATION_CHANGE : floor(WeatherConstants::DAILY_INSOLATION_CHANGE / 2);
        $dailyHumidityChange = $cell->humidity >= WeatherConstants::BELOW_AVERAGE_HUMIDITY ? WeatherConstants::DAILY_HUMIDITY_CHANGE : floor(WeatherConstants::DAILY_HUMIDITY_CHANGE / 2);

        $this->cells[$col][$row]->insolation = min(max(WeatherConstants::MIN_INSOLATION, $cell->insolation +
          $dailyInsolationChange + $agricultural["insolation"]), WeatherConstants::MAX_INSOLATION);
        $this->cells[$col][$row]->humidity = min(max(WeatherConstants::MIN_HUMIDITY, $cell->humidity +
          $dailyHumidityChange + $agricultural["humidity"]), WeatherConstants::MAX_HUMIDITY);

        /**
         * Weather change event
         */

        $weatherType = $cellWeather->getWeatherType();
        if (!array_key_exists($weatherType, $weatherChangeEvents)) {
          $weatherChangeEvents[$weatherType] = [];
        }
        $weatherChangeEvents[$weatherType][] = [
          "x" => $col * WeatherConstants::CELL_SIZE, "y" => $row * WeatherConstants::CELL_SIZE,
          "width" => WeatherConstants::CELL_SIZE - 1, "height" => WeatherConstants::CELL_SIZE - 1,
        ];

        /**
         * Wind
         */

        $windSpeed = intval(round($this->norm($wind["x"], $wind["y"])));
        if (array_key_exists($windSpeed, $this->speedDistribution)) {
          $this->speedDistribution[$windSpeed] += 1;
        } else {
          $this->speedDistribution[$windSpeed] = 1;
        }

        $this->cells[$col][$row]->wind_x = $wind["x"];
        $this->cells[$col][$row]->wind_y = $wind["y"];

        /**  -------
         *   xxxxxxx
         *   xxxxxxx
         *   xxxCxxx
         *   xxxNxxx
         *   xxxxxxx
         *   xxxxxxx
         *   +++++++
         *
         * Imagine the picture above shows cells whose pressure areas affect center ("C") cell.
         * When we have done computing, we must calculate the same for cell in the same col but next row ("N")
         * To make it faster, old result is stored. Then we remove influence of pressure areas
         * which are located in cells with "-" sign and we add influence of pressure areas
         * which are located in cells with "+" sign. Our area is "crawling" row by row which is faster.
         */

        $previousRow = $this->inGrid($row - $pressureRange);
        $nextRow = $this->inGrid($row + $pressureRange + 1);

        // get wind in next square - remove row at the end and add new one at the beginning of the square
        foreach (range($col - $pressureRange, $col + $pressureRange) as $areaCol) {
          foreach ($this->pressureAreas[$this->inGrid($areaCol)][$previousRow] as $area) {
            $wind["x"] -= $area->v_x;
            $wind["y"] -= $area->v_y;
          }
          foreach ($this->pressureAreas[$this->inGrid($areaCol)][$nextRow] as $area) {
            $wind["x"] += $area->v_x;
            $wind["y"] += $area->v_y;
          }
        }
      }
    }

    /**
     * Mapping absolute wind speed to relative one, to get linear distribution around the cantrworld
     */

    ksort($this->speedDistribution);
    $cellsCount = WeatherConstants::GRID_COLS * WeatherConstants::GRID_ROWS;
    $sum = 0;
    foreach ($this->speedDistribution as $speed => $count) {
      $sum += $count;
      $this->cumulativeDistribution[$speed] = $sum;
    }

    foreach ($this->cells as $col => $rows) {
      foreach ($rows as $row => $cell) {
        $relSpeed = $this->cumulativeDistribution[$speed] / $cellsCount;
        $speed = intval(round($this->norm($cell->wind_x, $cell->wind_y)));
        $xToNorm = ($speed > 0) ? ($cell->wind_x / $speed) : 0;
        $yToNorm = ($speed > 0) ? ($cell->wind_y / $speed) : 0;

        $this->cells[$col][$row]->relative_wind_x = $relSpeed * $xToNorm;
        $this->cells[$col][$row]->relative_wind_y = $relSpeed * $yToNorm;
      }
    }

    /**
     * Weather change notification
     */

    foreach ($weatherChangeEvents as $weatherType => $affectedRectangles) {
      if (array_key_exists($weatherType, WeatherMapping::$WEATHER_NOTIFICATION)) {
        Event::createEventInRectangles(WeatherMapping::$WEATHER_NOTIFICATION[$weatherType],
          "", $affectedRectangles);
      }
    }

    /**
     * Save data
     */
    $stm = $this->db->prepare("UPDATE weather_seasons SET rightmost_column = :rightmostColumn,
        deviation = :deviation WHERE id = :id");
    foreach ($this->seasons as $season => $seasonData) {
      $stm->bindInt("rightmostColumn", $seasonData->rightmost_column);
      $stm->bindFloat("deviation", $seasonData->deviation);
      $stm->bindInt("id", $season);
      $stm->execute();
    }

    foreach ($this->cells as $col => $rows) {
      $toUpdate = [];
      foreach ($rows as $row => $cell) {
        $toUpdate[] = "($col, $row, $cell->season, $cell->coefficient,
          $cell->relative_wind_x, $cell->relative_wind_y, $cell->insolation, $cell->humidity)";
      }
      if (count($toUpdate) > 0) {
        $toUpdateStr = implode(", ", $toUpdate);
        $this->db->query("INSERT IGNORE INTO weather_cells
          (col, row, season, coefficient, relative_wind_x, relative_wind_y, insolation, humidity) VALUES
          $toUpdateStr ON DUPLICATE KEY UPDATE season = VALUES(season), coefficient = VALUES(coefficient),
            relative_wind_x = VALUES(relative_wind_x), relative_wind_y = VALUES(relative_wind_y),
              insolation = VALUES(insolation), humidity = VALUES(humidity)"); // TODO query not escaped
      }
    }

    /**
     * updating wind position, it can be easily done in one query, it doesn't really matter that
     * "old" wind location is used for calculating weather coefficient and wind speed
     */
    $stm = $this->db->prepare("UPDATE weather_pressure_areas SET
      x = (:width1 + x + v_x) % :width2, y = (:height1 + y + v_y) % :height2
        WHERE mobile = 1");
    $stm->bindInt("width1", MapConstants::MAP_WIDTH);
    $stm->bindInt("width2", MapConstants::MAP_WIDTH);
    $stm->bindInt("height1", MapConstants::MAP_HEIGHT);
    $stm->bindInt("height2", MapConstants::MAP_HEIGHT);
    $stm->execute();
  }


  private function seasonAfter($season)
  {
    switch ($season) {
      case WeatherConstants::SEASON_SPRING:
        return WeatherConstants::SEASON_SUMMER;
      case WeatherConstants::SEASON_SUMMER:
        return WeatherConstants::SEASON_AUTUMN;
      case WeatherConstants::SEASON_AUTUMN:
        return WeatherConstants::SEASON_WINTER;
      case WeatherConstants::SEASON_WINTER:
        return WeatherConstants::SEASON_SPRING;
    }
  }

  /**
   * It assumes that map is a square
   */
  private function inGrid($val)
  {
    return ($val + WeatherConstants::GRID_COLS) % WeatherConstants::GRID_COLS;
  }

  private function onMap($val)
  {
    return ($val + MapConstants::MAP_WIDTH) % MapConstants::MAP_WIDTH;
  }

  private function norm($x, $y)
  {
    return sqrt(pow($x, 2) + pow($y, 2));
  }
}
