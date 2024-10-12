<?php

class CharacterConstants {
  const CHAR_PENDING = 0;
  const CHAR_ACTIVE = 1;
  const CHAR_DECEASED = 2;
  const CHAR_BEING_BURIED = 3;
  const CHAR_BURIED = 4;

  const CHAR_DEATH_UNKNOWN = 0;
  const CHAR_DEATH_VIOLENCE = 1;
  const CHAR_DEATH_PD = 2;
  const CHAR_DEATH_UNSUB = 3;
  const CHAR_DEATH_ANIMAL = 4;
  const CHAR_DEATH_EXPIRED = 5;
  const CHAR_DEATH_STARVED = 6;

  const BODY_WEIGHT = 60000;
  const INVENTORY_WEIGHT_MAX = 15000;

  const DEAD_CLOSE_SLOT_DAYS = 40; // Char slot closed for this number of days
  const DEAD_CLOSE_SLOT_AGE = 100; // Max age of char to apply slot closing

  const NEAR_DEATH_DAYS = 3;
  const NEAR_DEATH_HEALED_HEALTH = 2000; // how much health character has after being cured [0-10000]
  const NEAR_DEATH_CANNOT_EAT = 5; // for how many days after leaving NDS character cannot eat (to forbid healing)
  
  const NEAR_DEATH_NOT_HEALED = 1;
  const NEAR_DEATH_HEALED = 2;

  // hunger is [0, 10000], it's minimum value needed to display info on chardesc and people pages
  const DESC_HUNGER_1_MIN = 5000;
  const DESC_HUNGER_2_MIN = 7500;
  const DESC_HUNGER_3_MIN = 9000;

  const DESC_DRUNK_1_MIN = 2000; // tipsy
  const DESC_DRUNK_2_MIN = 3500; // half-drunk
  const DESC_DRUNK_3_MIN = 5000; // drunk
  const DESC_DRUNK_4_MIN = 6000; // very drunk
  const DESC_DRUNK_5_MIN = 7000; // falling-down drunk

  const DRUNKEN_SPEECH_MIN = self::DESC_DRUNK_3_MIN;

  const PASSOUT_LIMIT = 7500; // [0,10000] level of drunkenness needed to pass out.

  const SEX_MALE = 1;
  const SEX_FEMALE = 2;

  const OLD_AGE_DEATH_MIN_YEARS = 30; // how old should character be to be able to voluntarily die of old age

  const OLD_AGE_DEATH_LOCK_DAYS = 1;
  const OLD_AGE_DEATH_ALLOW_DAYS = 3;

  const OVERHEAR_BASE_CHANCE_PERCENT = 5;
  const OVERHEAR_DRUNKENNESS_ADDITION = 25;
}
