<?php

/**
 * Creates tags for human-readable description of weather in a cell specified in constructor
 */
class WeatherView
{
  /** @var Weather */
  private $subject;

  public function __construct(Weather $weather)
  {
    $this->subject = $weather;
  }

  public function getText()
  {
    return "<CANTR REPLACE NAME=weather_type_" . $this->subject->getWeatherType() .
      ">. <CANTR REPLACE NAME=text_season_its> <CANTR REPLACE NAME=season_" . $this->subject->getSeason() . ">.<br>
      <CANTR REPLACE NAME=wind_" . $this->getDescriptiveWindSpeed() .
      " DIRECTION=" . $this->getDescriptiveWindDirection() . ">";
  }

  public function getDescriptiveWindDirection()
  {
    import_lib("func.getdirection.inc.php");
    $oppositeDirection = ($this->subject->getWindDirection() + 360) % 360;
    return getdirectionrawname($oppositeDirection);
  }

  public function getDescriptiveWindSpeed()
  {
    $speed = $this->subject->getWindSpeed();
    if ($speed >= 0.9) {
      return "gale";
    } elseif ($speed >= 0.75) {
      return "very_fast";
    } elseif ($speed >= 0.6) {
      return "fast";
    } elseif ($speed >= 0.4) {
      return "moderate";
    } elseif ($speed >= 0.2) {
      return "light";
    } elseif ($speed > 0) {
      return "very_weak";
    } else {
      return "no";
    }
  }

  public static function getSeasonName($seasonId)
  {
    switch ($seasonId) {
      case 1:
        return "spring";
      case 2:
        return "summer";
      case 3:
        return "autumn";
      case 4:
        return "winter";
    }
  }
}
