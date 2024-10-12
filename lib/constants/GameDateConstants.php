<?php

class GameDateConstants
{
  const SECS_PER_MIN = 60;
  const MINS_PER_HOUR = 36;
  const HOURS_PER_DAY = 8;
  const DAYS_PER_YEAR = 20;

  const SECS_PER_DAY = self::SECS_PER_MIN * self::MINS_PER_HOUR * self::HOURS_PER_DAY;
}
