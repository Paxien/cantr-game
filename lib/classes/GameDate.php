<?php

/**
 * Immutable class for storing Cantr time or interval
 */
class GameDate
{
  const ETERNITY_DAYS = 100000;
  
  private static $nowInstance = null;
  private static $isCacheable = true;
  
  private $cantrTimestamp;

  private function __construct() {}

  // predefined "constants"
  public static function NOW()
  {
    $db = Db::get();
    if (!self::$isCacheable || (self::$nowInstance === null)) {
      $stm = $db->query("SELECT * FROM turn");
      $turn = $stm->fetchObject();
      self::$nowInstance = self::fromDate($turn->day, $turn->hour, $turn->minute, $turn->second);
    }
    return self::$nowInstance;
  }

  public static function ETERNITY()
  {
    return self::fromDate(self::ETERNITY_DAYS, 0, 0, 0);
  }

  public static function fromTimestamp($timestamp)
  {
    $gameDate = new GameDate();
    $gameDate->cantrTimestamp = $timestamp;
    
    return $gameDate;
  }

  public static function fromDate($day, $hour, $min, $sec)
  {
    $gameDate = new GameDate();
    $gameDate->cantrTimestamp = (($day * GameDateConstants::HOURS_PER_DAY + $hour) *
        GameDateConstants::MINS_PER_HOUR + $min) *
          GameDateConstants::SECS_PER_MIN + $sec;

    return $gameDate;
  }

  /**
   * A date in format 10 * day + hour stored in a single int
   */
  public static function fromIntInDbFormat($date)
  {
    $date = intval($date);
    return self::fromDate(floor($date/10), ($date % 10), 0, 0);
  }

  public function getTimestamp()
  {
    return $this->cantrTimestamp;
  }

  public function getArray()
  {
    $ts = $this->cantrTimestamp;
    $date = array();
    $date['second'] = $ts % GameDateConstants::SECS_PER_MIN;
    $ts = floor($ts / GameDateConstants::SECS_PER_MIN);
    $date['minute'] = $ts % GameDateConstants::MINS_PER_HOUR;
    $ts = floor($ts / GameDateConstants::MINS_PER_HOUR);
    $date['hour'] = $ts % GameDateConstants::HOURS_PER_DAY;
    $ts = floor($ts / GameDateConstants::HOURS_PER_DAY);
    $date['day'] = $ts;
    return $date;
  }

  public function getObject()
  {
    $dateArray = $this->getArray();
    $dateObj = new StdClass();
    $dateObj->day = $dateArray['day'];
    $dateObj->hour = $dateArray['hour'];
    $dateObj->minute = $dateArray['minute'];
    $dateObj->second = $dateArray['second'];
    return $dateObj;
  }

  public function getIntInDbFormat()
  {
    $date = $this->getArray();
    return $date['day'] * 10 + $date['hour'];
  }

  public function plus(GameDate $other)
  {
    return GameDate::fromTimestamp($this->getTimestamp() + $other->getTimestamp());
  }

  public function minus(GameDate $other)
  {
    return GameDate::fromTimestamp($this->getTimestamp() - $other->getTimestamp());
  }
  
  public function getDay()
  {
    $turn = $this->getArray();
    return $turn['day'];
  }
  
  public function getHour()
  {
    $turn = $this->getArray();
    return $turn['hour'];
  }
  
  public function getMinute()
  {
    $turn = $this->getArray();
    return $turn['minute'];
  }
  
  public function getSecond()
  {
    $turn = $this->getArray();
    return $turn['second'];
  }

  /**
   * @return string formatted date and time, e.g. "4444-4"
   */
  public function formatDayWithHour() {
    return $this->getDay() . "-" . $this->getHour();
  }

  /**
   * @return string formatted date and time, e.g. "4444-4.12"
   */
  public function formatDayWithHourAndMinute() {
    return $this->formatDayWithHour() . "." . $this->getMinute();
  }

  public static function setCacheable($isCacheable)
  {
    self::$isCacheable = !!$isCacheable;
  }
}
