<?php

class AnimalConstants
{

  const OBJECTTYPE_ANIMAL_ID = 39;

  const DEFAULT_TAME_SUCCESS_CHANCE = 50;

  const OBJECTTYPE_ANIMAL_ENCLOSURE_ID = 1238;

  const MAX_FULLNESS = 10000;
  const HUNGER_THRESHOLD = 4000; // [0-10000] below that animal is considered as hungry
  const INITIAL_FULLNESS = 4000; /* [0-10000] level of fullness just after taming */
  const INITIAL_LOYALTY = 2000;
  const EATING_FULLNESS_CHANGE = 500;
  const FOOD_TO_DUNG_RATIO = 0.25;
  const DUNG_PRODUCED_ID = 294;

  const DAILY_FEATHERS_TO_HUNT_RATIO = 0.05;

  const PROJECT_HARVESTING_DAYS = 1;

  const FODDER_HAY_ID = 57;
  const FODDER_VEGETABLES_ID = 409;
  const FODDER_MEAT_ID = 408;

  const ANIMAL_EATS_HAY = 1;
  const ANIMAL_EATS_VEGETABLES = 2;
  const ANIMAL_EATS_MEAT = 4;

  const PARROT_REPEAT_WORD_CHANCE = 0.05;
  const PARROT_REPEAT_DOUBLE_CHANCE = 0.33;

  // animal steeds
  const STEED_SADDLING_DAYS = 1;
  const STEED_UNSADDLING_DAYS = 1;

  const MAX_WILD_SPECIES_IN_LOCATION = 8;
}
