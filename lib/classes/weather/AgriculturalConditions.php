<?php

class AgriculturalConditions
{
  /** @var Weather */
  private $weather;

  public function __construct(Weather $weather)
  {
    $this->weather = $weather;
  }

  public static function getProgressMultiplier($harvestEfficiency)
  {
    switch ($harvestEfficiency) {
      case WeatherConstants::NO_HARVEST:
        return 0.25;
      case WeatherConstants::POOR_HARVEST:
        return 0.5;
      case WeatherConstants::NORMAL_HARVEST:
        return 1.0;
      case WeatherConstants::GOOD_HARVEST:
        return 1.25;
      case WeatherConstants::PERFECT_HARVEST:
        return 1.5;
      default:
        throw new IllegalStateException("$harvestEfficiency is not a valid harvest efficiency");
    }
  }

  /**
   * @return int enum-like constant representing harvest level: NO_HARVEST, POOR_HARVEST, NORMAL_HARVEST, GOOD_HARVEST, PERFECT_HARVEST
   */
  public function getHarvestEfficiency()
  {
    $insolation = $this->weather->getInsolation();
    $humidity = $this->weather->getHumidity();

    if (!WeatherMapping::$VEGETATION[$this->weather->getClimate()][$this->weather->getSeason()]) {
      return WeatherConstants::NO_HARVEST;
    }
    if ($insolation > WeatherConstants::ABOVE_AVERAGE_INSOLATION && $humidity > WeatherConstants::ABOVE_AVERAGE_HUMIDITY) {
      return WeatherConstants::PERFECT_HARVEST;
    } elseif ($insolation < WeatherConstants::BELOW_AVERAGE_INSOLATION && $humidity < WeatherConstants::BELOW_AVERAGE_HUMIDITY) {
      return WeatherConstants::NO_HARVEST;
    } elseif ($insolation < WeatherConstants::BELOW_AVERAGE_INSOLATION || $humidity < WeatherConstants::BELOW_AVERAGE_HUMIDITY) {
      return WeatherConstants::POOR_HARVEST;
    } elseif ($insolation > WeatherConstants::ABOVE_AVERAGE_INSOLATION || $humidity > WeatherConstants::ABOVE_AVERAGE_HUMIDITY) {
      return WeatherConstants::GOOD_HARVEST;
    }

    return WeatherConstants::NORMAL_HARVEST;
  }

  public function getDescriptiveHarvestEfficiency()
  {
    $insolation = $this->weather->getInsolation();
    $humidity = $this->weather->getHumidity();

    if (!WeatherMapping::$VEGETATION[$this->weather->getClimate()][$this->weather->getSeason()]) {
      return "<CANTR REPLACE NAME=harvest_efficiency_too_cold>";
    }

    if ($insolation > WeatherConstants::ABOVE_AVERAGE_INSOLATION && $humidity > WeatherConstants::ABOVE_AVERAGE_HUMIDITY) {
      return "<CANTR REPLACE NAME=harvest_efficiency_perfect>";
    } elseif ($insolation < WeatherConstants::BELOW_AVERAGE_INSOLATION && $humidity < WeatherConstants::BELOW_AVERAGE_HUMIDITY) {
      return "<CANTR REPLACE NAME=harvest_efficiency_no>";
    } elseif ($insolation < WeatherConstants::BELOW_AVERAGE_INSOLATION || $humidity < WeatherConstants::BELOW_AVERAGE_HUMIDITY) {
      $belowAverageTag = $insolation < WeatherConstants::BELOW_AVERAGE_INSOLATION ? "harvest_below_average_insolation" : "harvest_below_average_humidity";
      return "<CANTR REPLACE NAME=harvest_efficiency_poor VALUE_BELOW_AVERAGE=$belowAverageTag>";
    } elseif ($insolation > WeatherConstants::ABOVE_AVERAGE_INSOLATION || $humidity > WeatherConstants::ABOVE_AVERAGE_HUMIDITY) {
      $aboveAverageTag = $insolation < WeatherConstants::BELOW_AVERAGE_INSOLATION ? "harvest_above_average_insolation" : "harvest_above_average_humidity";
      return "<CANTR REPLACE NAME=harvest_efficiency_good VALUE_ABOVE_AVERAGE=$aboveAverageTag>";
    }
    return "<CANTR REPLACE NAME=harvest_efficiency_normal>";
  }
}