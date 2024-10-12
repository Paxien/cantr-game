<?php

class Measure
{
  /**
   * measure distance between points a(x,y) and b(x,y)
   */
  public static function distance($aX, $aY, $bX, $bY)
  {
    return self::vectorLength($aX - $bX, $aY - $bY);
  }

  public static function vectorLength($xDiff, $yDiff)
  {
    return sqrt(pow($xDiff, 2) + pow($yDiff, 2));
  }

  public static function vectorDirection($xDiff, $yDiff)
  {
    return atan2($yDiff, $xDiff);
  }

  public static function between($value, array $range)
  {
    return max($range[0], min($range[1], $value));
  }

  public static function direction($aX, $aY, $bX, $bY)
  {
    import_lib("func.getdirection.inc.php");
    return getdirection($aX, $aY, $bX, $bY);
  }
}
