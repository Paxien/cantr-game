<?php

class WeatherConstants
{
  /**
   * Weather types
   */
  // "clear" weather was removed on 23-03-2017
  const TYPE_CLOUDY = 2;
  const TYPE_SUNNY = 3;
  const TYPE_VERY_SUNNY = 4;
  const TYPE_DRIZZLE = 5;
  const TYPE_RAIN = 6;
  const TYPE_DOWNPOUR = 7;
  const TYPE_HAIL = 8;
  const TYPE_STORM = 9;
  const TYPE_LIGHT_SNOW = 10;
  const TYPE_SNOW = 11;
  const TYPE_BLIZZARD = 12;
  const TYPE_FOG = 13;
  const TYPE_TROPICAL_RAIN = 14;
  const TYPE_DESERT_RAIN = 15;

  /**
   * @var array mapping of weather type ids to names
   */
  public static $WEATHER_NAMES = [
    self::TYPE_CLOUDY => "cloudy",
    self::TYPE_SUNNY => "sunny",
    self::TYPE_VERY_SUNNY => "very_sunny",
    self::TYPE_DRIZZLE => "drizzle",
    self::TYPE_RAIN => "rain",
    self::TYPE_DOWNPOUR => "downpour",
    self::TYPE_HAIL => "hail",
    self::TYPE_STORM => "storm",
    self::TYPE_LIGHT_SNOW => "light_snow",
    self::TYPE_SNOW => "snow",
    self::TYPE_BLIZZARD => "blizzard",
    self::TYPE_FOG => "fog",
    self::TYPE_TROPICAL_RAIN => "tropical_rain",
    self::TYPE_DESERT_RAIN => "desert_rain",
  ];

  /**
   * CELL_SIZE * GRID_COLS and CELL_SIZE * GRID_ROWS should be 6000 (it's map size)
   * you can alter it, but then you have to run script in storage/init_weather.php
   */
  const CELL_SIZE = 50;
  const GRID_ROWS = 120;
  const GRID_COLS = 120;

  /**
   * min and max range for random part of weather coefficient (used later to specify weather type in cell)
   */
  const COEFFICIENT_RAND_MIN = -8;
  const COEFFICIENT_RAND_MAX = 8;

  /**
   * climates, you can add new, but you have to update mapping arrays in WeatherMapping then
   * and set them manually for some cells
   */
  const CLIMATE_TEMPERATE = 1; // basic
  const CLIMATE_TROPICAL = 2; // jungle
  const CLIMATE_DRY = 3; // desert
  const CLIMATE_STEPPE = 4; // tundra
  const CLIMATE_MOUNTAIN = 5; // mountains

  /**
   * seasons, it's impossible to add more
   */
  const SEASON_SPRING = 1;
  const SEASON_SUMMER = 2;
  const SEASON_AUTUMN = 3;
  const SEASON_WINTER = 4;

  public static $SEASON_NAMES = [
    self::SEASON_SPRING => "spring",
    self::SEASON_SUMMER => "summer",
    self::SEASON_AUTUMN => "autumn",
    self::SEASON_WINTER => "winter",
  ];

  /**
   * Default value to make it decrease automatically every day
   */
  const DAILY_INSOLATION_CHANGE = -7;
  const DAILY_HUMIDITY_CHANGE = -7;

  const MIN_INSOLATION = 0;
  const MAX_INSOLATION = 210;

  const MIN_HUMIDITY = 0;
  const MAX_HUMIDITY = 210;

  const BELOW_AVERAGE_INSOLATION = 20;
  const ABOVE_AVERAGE_INSOLATION = 190;
  const BELOW_AVERAGE_HUMIDITY = 15;
  const ABOVE_AVERAGE_HUMIDITY = 175;

  const NO_HARVEST = 0;
  const POOR_HARVEST = 1;
  const NORMAL_HARVEST = 2;
  const GOOD_HARVEST = 3;
  const PERFECT_HARVEST = 4;

  /**
   * how many cells "after" and "before" are taken into account when calculating wind speed in cell
   * affects square of (2N+1)x(2N+1) cells
   * 3 means that 7x7 area of cells is used
   */
  const WIND_PRESSURE_RANGE = 3;

  /**
   * How many pressure areas are there. To update it, you have to run script in storage/init_weather.php
   */
  const PRESSURE_AREAS = 1000;
}
