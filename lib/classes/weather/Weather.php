<?php

/**
 * Holds information about weather in a cell specified in constructor (or static factory methods)
 */
class Weather
{
  private $col;
  private $row;

  private $coefficient;
  private $season;
  private $climate;

  private $relative_wind_x;
  private $relative_wind_y;
  private $insolation;
  private $humidity;

  public static function loadByLocation($location)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT x, y FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $location);
    $stm->execute();
    list($x, $y) = $stm->fetch(PDO::FETCH_NUM);
    return self::loadByPos($x, $y);
  }

  public static function loadByPos($x, $y)
  {
    return self::loadByCell(floor($x / WeatherConstants::CELL_SIZE), floor($y / WeatherConstants::CELL_SIZE));
  }

  public static function loadByCell($col, $row)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM weather_cells WHERE col = :col AND row = :row");
    $stm->bindInt("col", $col);
    $stm->bindInt("row", $row);
    $stm->execute();
    if ($cell = $stm->fetchObject()) {
      return new Weather($cell);
    }
    throw new InvalidArgumentException("cell [$col, $row] doesn't exist!");
  }

  public function __construct($fetch)
  {
    $this->col = $fetch->col;
    $this->row = $fetch->row;
    $this->coefficient = $fetch->coefficient;
    $this->season = $fetch->season;
    $this->climate = $fetch->climate;

    $this->relative_wind_x = $fetch->relative_wind_x;
    $this->relative_wind_y = $fetch->relative_wind_y;

    $this->insolation = $fetch->insolation;
    $this->humidity = $fetch->humidity;
  }

  public function getWeatherType()
  {
    $weatherByCoefficient = WeatherMapping::$BY_CLIMATE_AND_SEASON[$this->climate][$this->season];
    // we get array with keys (max possible coefficient value to get a weather type) and values (weather type)
    foreach ($weatherByCoefficient as $rightEnd => $weatherType) {
      if ($this->coefficient <= $rightEnd) {
        return $weatherType;
      }
    }
    return end($weatherByCoefficient); // can happen rarely, but it's possible it wasn't trapped in foreach
  }

  public function getWeatherName()
  {
    return WeatherConstants::$WEATHER_NAMES[$this->getWeatherType()];
  }

  public function getSeason()
  {
    return $this->season;
  }

  public function getSeasonName()
  {
    return WeatherConstants::$SEASON_NAMES[$this->getSeason()];
  }

  /**
   * Value in range [0,1] which is uniformly distributed across the world
   */
  public function getWindSpeed()
  {
    return sqrt(pow($this->relative_wind_x, 2) + pow($this->relative_wind_y, 2));
  }

  /**
   * [0, 360] degrees
   */
  public function getWindDirection()
  {
    return Measure::direction(0, 0, $this->relative_wind_x, $this->relative_wind_y);
  }

  public function getAgriculturalConditions()
  {
    return new AgriculturalConditions($this);
  }

  public function getInsolation()
  {
    return $this->insolation;
  }

  public function getHumidity()
  {
    return $this->humidity;
  }

  public function getClimate()
  {
    return $this->climate;
  }
}
