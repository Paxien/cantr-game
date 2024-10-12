<?php

/**
 * Maps cell-specific values into weather type, weather type to agricultural conditions change
 * Controls weather notifiction events
 */
class WeatherMapping
{
  /**
   * Used to map coefficient, season and climate of a cell into weather type
   */
  public static $BY_CLIMATE_AND_SEASON;
  /**
   * Used to get humidity and insolation affection of certain weather type
   */
  public static $AGRICULTURAL_CONDITIONS;
  /**
   * Used to decide if vegetation happens in a specific season for the climate
   */
  public static $VEGETATION;
  /**
   * Used to map weather type into number of weather notification event
   * If weather type is not a key of the array then weather isn't notified
   */
  public static $WEATHER_NOTIFICATION;

  public static function staticInit()
  {
    // the only one season for tropical climate
    $WEATHER_TROPICAL = array(
      -3 => WeatherConstants::TYPE_TROPICAL_RAIN,
      4 => WeatherConstants::TYPE_SUNNY,
      99 => WeatherConstants::TYPE_VERY_SUNNY,
    );

    // the only season for dry climate
    $WEATHER_DRY = array(
      -9 => WeatherConstants::TYPE_DESERT_RAIN,
      3 => WeatherConstants::TYPE_SUNNY,
      99 => WeatherConstants::TYPE_VERY_SUNNY,
    );

    // the two seasons for steppe climate
    $WEATHER_STEPPE_WARM = array(
      -6 => WeatherConstants::TYPE_RAIN,
      -4 => WeatherConstants::TYPE_DRIZZLE,
      0 => WeatherConstants::TYPE_FOG,
      2 => WeatherConstants::TYPE_CLOUDY,
      6 => WeatherConstants::TYPE_SUNNY,
      99 => WeatherConstants::TYPE_VERY_SUNNY,
    );

    $WEATHER_STEPPE_COLD = array(
      -7 => WeatherConstants::TYPE_BLIZZARD,
      -2 => WeatherConstants::TYPE_SNOW,
      2 => WeatherConstants::TYPE_LIGHT_SNOW,
      3 => WeatherConstants::TYPE_FOG,
      5 => WeatherConstants::TYPE_CLOUDY,
      9 => WeatherConstants::TYPE_SUNNY,
      99 => WeatherConstants::TYPE_VERY_SUNNY,
    );

    // the only two seasons for mountain climate
    $WEATHER_MOUNTAIN_WARM = array(
      -7 => WeatherConstants::TYPE_LIGHT_SNOW,
      -5 => WeatherConstants::TYPE_RAIN,
      -3 => WeatherConstants::TYPE_DRIZZLE,
      0 => WeatherConstants::TYPE_FOG,
      3 => WeatherConstants::TYPE_CLOUDY,
      7 => WeatherConstants::TYPE_SUNNY,
      99 => WeatherConstants::TYPE_VERY_SUNNY,
    );

    $WEATHER_MOUNTAIN_COLD = array(
      -7 => WeatherConstants::TYPE_BLIZZARD,
      -2 => WeatherConstants::TYPE_SNOW,
      2 => WeatherConstants::TYPE_LIGHT_SNOW,
      3 => WeatherConstants::TYPE_FOG,
      5 => WeatherConstants::TYPE_CLOUDY,
      7 => WeatherConstants::TYPE_SUNNY,
      99 => WeatherConstants::TYPE_VERY_SUNNY,
    );

    // four seasons for temperate climate

    $WEATHER_TEMPERATE_SPRING = array(
      -9 => WeatherConstants::TYPE_STORM,
      -7 => WeatherConstants::TYPE_DOWNPOUR,
      -5 => WeatherConstants::TYPE_RAIN,
      -3 => WeatherConstants::TYPE_DRIZZLE,
      -1 => WeatherConstants::TYPE_FOG,
      0 => WeatherConstants::TYPE_CLOUDY,
      4 => WeatherConstants::TYPE_SUNNY,
      99 => WeatherConstants::TYPE_VERY_SUNNY,
    );

    $WEATHER_TEMPERATE_SUMMER = array(
      -10 => WeatherConstants::TYPE_HAIL,
      -8 => WeatherConstants::TYPE_STORM,
      -7 => WeatherConstants::TYPE_DOWNPOUR,
      -6 => WeatherConstants::TYPE_RAIN,
      -5 => WeatherConstants::TYPE_DRIZZLE,
      -1 => WeatherConstants::TYPE_CLOUDY,
      4 => WeatherConstants::TYPE_SUNNY,
      99 => WeatherConstants::TYPE_VERY_SUNNY,
    );

    $WEATHER_TEMPERATE_AUTUMN = array(
      -7 => WeatherConstants::TYPE_DOWNPOUR,
      -5 => WeatherConstants::TYPE_RAIN,
      -4 => WeatherConstants::TYPE_DRIZZLE,
      -1 => WeatherConstants::TYPE_FOG,
      3 => WeatherConstants::TYPE_CLOUDY,
      6 => WeatherConstants::TYPE_SUNNY,
      99 => WeatherConstants::TYPE_VERY_SUNNY,
    );

    $WEATHER_TEMPERATE_WINTER = array(
      -8 => WeatherConstants::TYPE_BLIZZARD,
      -3 => WeatherConstants::TYPE_SNOW,
      2 => WeatherConstants::TYPE_LIGHT_SNOW,
      4 => WeatherConstants::TYPE_CLOUDY,
      99 => WeatherConstants::TYPE_SUNNY,
    );

    self::$BY_CLIMATE_AND_SEASON = array(
      WeatherConstants::CLIMATE_TEMPERATE => array(
        WeatherConstants::SEASON_SPRING => $WEATHER_TEMPERATE_SPRING,
        WeatherConstants::SEASON_SUMMER => $WEATHER_TEMPERATE_SUMMER,
        WeatherConstants::SEASON_AUTUMN => $WEATHER_TEMPERATE_AUTUMN,
        WeatherConstants::SEASON_WINTER => $WEATHER_TEMPERATE_WINTER,
      ),
      WeatherConstants::CLIMATE_TROPICAL => array( // only 1 distinct season
        WeatherConstants::SEASON_SPRING => $WEATHER_TROPICAL,
        WeatherConstants::SEASON_SUMMER => $WEATHER_TROPICAL,
        WeatherConstants::SEASON_AUTUMN => $WEATHER_TROPICAL,
        WeatherConstants::SEASON_WINTER => $WEATHER_TROPICAL,
      ),
      WeatherConstants::CLIMATE_DRY => array( // only 1 distinct season
        WeatherConstants::SEASON_SPRING => $WEATHER_DRY,
        WeatherConstants::SEASON_SUMMER => $WEATHER_DRY,
        WeatherConstants::SEASON_AUTUMN => $WEATHER_DRY,
        WeatherConstants::SEASON_WINTER => $WEATHER_DRY,
      ),
      WeatherConstants::CLIMATE_STEPPE => array( // only 2 distinct seasons, cold (3/4 of year) and warm
        WeatherConstants::SEASON_SPRING => $WEATHER_STEPPE_COLD,
        WeatherConstants::SEASON_SUMMER => $WEATHER_STEPPE_WARM,
        WeatherConstants::SEASON_AUTUMN => $WEATHER_STEPPE_COLD,
        WeatherConstants::SEASON_WINTER => $WEATHER_STEPPE_COLD,
      ),
      WeatherConstants::CLIMATE_MOUNTAIN => array( // only 2 distinct seasons, cold (3/4 of year) and warm
        WeatherConstants::SEASON_SPRING => $WEATHER_MOUNTAIN_COLD,
        WeatherConstants::SEASON_SUMMER => $WEATHER_MOUNTAIN_WARM,
        WeatherConstants::SEASON_AUTUMN => $WEATHER_MOUNTAIN_COLD,
        WeatherConstants::SEASON_WINTER => $WEATHER_MOUNTAIN_COLD,
      ),
    );

    self::$VEGETATION = array(
      WeatherConstants::CLIMATE_TEMPERATE => array(
        WeatherConstants::SEASON_SPRING => true,
        WeatherConstants::SEASON_SUMMER => true,
        WeatherConstants::SEASON_AUTUMN => true,
        WeatherConstants::SEASON_WINTER => false,
      ),
      WeatherConstants::CLIMATE_TROPICAL => array( // only 1 distinct season
        WeatherConstants::SEASON_SPRING => true,
        WeatherConstants::SEASON_SUMMER => true,
        WeatherConstants::SEASON_AUTUMN => true,
        WeatherConstants::SEASON_WINTER => true,
      ),
      WeatherConstants::CLIMATE_DRY => array( // only 1 distinct season
        WeatherConstants::SEASON_SPRING => true,
        WeatherConstants::SEASON_SUMMER => true,
        WeatherConstants::SEASON_AUTUMN => true,
        WeatherConstants::SEASON_WINTER => true,
      ),
      WeatherConstants::CLIMATE_STEPPE => array( // only 2 distinct seasons, cold (3/4 of year) and warm
        WeatherConstants::SEASON_SPRING => false,
        WeatherConstants::SEASON_SUMMER => true,
        WeatherConstants::SEASON_AUTUMN => false,
        WeatherConstants::SEASON_WINTER => false,
      ),
      WeatherConstants::CLIMATE_MOUNTAIN => array( // only 2 distinct seasons, cold (3/4 of year) and warm
        WeatherConstants::SEASON_SPRING => false,
        WeatherConstants::SEASON_SUMMER => true,
        WeatherConstants::SEASON_AUTUMN => false,
        WeatherConstants::SEASON_WINTER => false,
      ),
    );

    self::$AGRICULTURAL_CONDITIONS = [
      WeatherConstants::TYPE_CLOUDY =>
        ["insolation" => 3, "humidity" => 0],
      WeatherConstants::TYPE_SUNNY =>
        ["insolation" => 15, "humidity" => -2],
      WeatherConstants::TYPE_VERY_SUNNY =>
        ["insolation" => 20, "humidity" => -5],
      WeatherConstants::TYPE_DRIZZLE =>
        ["insolation" => 0, "humidity" => 15],
      WeatherConstants::TYPE_RAIN =>
        ["insolation" => 0, "humidity" => 25],
      WeatherConstants::TYPE_DOWNPOUR =>
        ["insolation" => 0, "humidity" => 45],
      WeatherConstants::TYPE_HAIL =>
        ["insolation" => -75, "humidity" => 0],
      WeatherConstants::TYPE_STORM =>
        ["insolation" => 0, "humidity" => 45],
      WeatherConstants::TYPE_LIGHT_SNOW =>
        ["insolation" => 0, "humidity" => 0],
      WeatherConstants::TYPE_SNOW =>
        ["insolation" => 0, "humidity" => 0],
      WeatherConstants::TYPE_BLIZZARD =>
        ["insolation" => 0, "humidity" => 0],
      WeatherConstants::TYPE_FOG =>
        ["insolation" => 5, "humidity" => 0],
      WeatherConstants::TYPE_TROPICAL_RAIN =>
        ["insolation" => 0, "humidity" => 40],
      WeatherConstants::TYPE_DESERT_RAIN =>
        ["insolation" => 0, "humidity" => 200],
    ];

    self::$WEATHER_NOTIFICATION = array(
      WeatherConstants::TYPE_RAIN => 330,
      WeatherConstants::TYPE_DOWNPOUR => 331,
      WeatherConstants::TYPE_HAIL => 332,
      WeatherConstants::TYPE_STORM => 333,
      WeatherConstants::TYPE_LIGHT_SNOW => 334,
      WeatherConstants::TYPE_SNOW => 335,
      WeatherConstants::TYPE_BLIZZARD => 336,
      WeatherConstants::TYPE_FOG => 337,
      WeatherConstants::TYPE_TROPICAL_RAIN => 338,
      WeatherConstants::TYPE_DESERT_RAIN => 345,
    );
  }
}

WeatherMapping::staticInit();
