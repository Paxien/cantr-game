<?php

class PlayerConstants
{
  const PENDING = 0;
  const APPROVED = 1;
  const ACTIVE = 2; /* Reactivated accounts are ACTIVE, new ones are APPROVED */
  const LOCKED = 3; /* Thus assumption can be and is made that < LOCKED is active; >= LOCKED is not */
  const REMOVED = 4;
  const UNSUBSCRIBED = 5;
  const IDLEDOUT = 6;
  const REFUSED = 7;

  public static $STATUS_NAMES = [
    self::PENDING => "Pending",
    self::APPROVED => "Active",
    self::ACTIVE => "Active",
    self::LOCKED => "Locked",
    self::REMOVED => "Removed",
    self::UNSUBSCRIBED => "Unsubscribed",
    self::IDLEDOUT => "Idled Out",
    self::REFUSED => "Refused",
  ];

  const UNSUB_LOCK_DAYS = 1;
  const UNSUB_ALLOW_DAYS = 3; // allow - unsub = interval when you can unsub account

  const SURVEY_MIN_AGE = 14; // for how long should player be in the game to see the surveys

  const NO_CHAR_SLOTS_LEFT = -1; // error value

  const TURN_REPORT_AUTOSEND_DELAY = 3;

  const REFUSAL_CUSTOM = 0;
  const REFUSAL_DOUBLE_ACCOUNT = 1;
  const REFUSAL_DOUBLE_APPLICATION = 2;
  const REFUSAL_PROXY = 3;
  const REFUSAL_REACTIVATED_OTHER = 5;

  public static $REFUSAL_REASONS = [
    self::REFUSAL_CUSTOM => "CUSTOM",
    self::REFUSAL_DOUBLE_ACCOUNT => "DOUBLE_ACCOUNT",
    self::REFUSAL_DOUBLE_APPLICATION => "DOUBLE_APPLICATION",
    self::REFUSAL_PROXY => "PROXY",
    self::REFUSAL_REACTIVATED_OTHER => "REACTIVATED_OTHER",
  ];
}
