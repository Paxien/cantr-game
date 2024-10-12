<?php

class MapUtil
{
  public static function getNormalizedX($x)
  {
    return fmod((MapConstants::MAP_WIDTH + $x), MapConstants::MAP_WIDTH);
  }

  public static function getNormalizedY($y)
  {
    return fmod((MapConstants::MAP_HEIGHT + $y), MapConstants::MAP_HEIGHT);
  }

  /**
   * Returns distance tag WITHOUT "dist_air_" prefix
   * @param  int $distance distance in px
   * @return string descriptive tag without "dist_air_" prefix
   */
  public static function getDistanceTagName($distance)
  {
    import_lib("func.getdirection.inc.php");
    return getdistancerawname($distance);
  }

  /**
   * @param $degree int degrees
   * @return string name of tag for the direction
   */
  public static function getDirectionTagName($degree)
  {
    import_lib("func.getdirection.inc.php");
    return getdirectionrawname($degree);
  }

  /**
   * @param $degree int degrees
   * @return string full descriptive tag for the direction
   */
  public static function getDirectionTag($degree)
  {
    import_lib("func.getdirection.inc.php");
    return getdirectionname($degree);
  }
}
