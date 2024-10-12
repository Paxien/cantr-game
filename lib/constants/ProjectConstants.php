<?php

class ProjectConstants {
  //gathering raws, or proccess them to make new resources ( iron - from iron ore and coal etc.)
  const TYPE_GATHERING = 1;
  //this three use this same ID, not best idea ;)
  const TYPE_MANUFACTURING = 4;
  const TYPE_BUILDING = 4;
  const TYPE_OLD_REPAIRING = 4;
  const TYPE_BURYING = 5;
  const TYPE_IMPROVING_ROADS = 6;
  const TYPE_PICKING_LOCK = 7;
  const TYPE_DISASSEMBLING_OPEN_LOCK = 9;
  const TYPE_RESTING = 12;
  const TYPE_PRODUCING_COINS = 13;
  const TYPE_DISASSEMBLING = 14;

  const TYPE_ALTERING_SIGN = 17;
  //project for canceling manufacturing projects where works was done.
  const TYPE_TEAR_DOWN = 18;
  //reverse to DISASSEMBLING project
  const TYPE_FIXING_DAMAGED = 19;
  const TYPE_HEAL_NEAR_DEATH = 20;
  const TYPE_DESC_BUILDING_CHANGE = 21;
  const TYPE_TAMING_ANIMAL = 22;
  const TYPE_HARVESTING_ANIMAL = 23;
  const TYPE_ADOPTING_ANIMAL = 24;
  const TYPE_BUTCHERING_ANIMAL = 25;
  const TYPE_REPAIRING = 26;
  const TYPE_DESC_OBJECT_CHANGE = 27;
  const TYPE_DESTROYING_BUILDING = 28;
  const TYPE_FIXING_DESTROYED_BUILDING = 29;
  const TYPE_SADDLING_STEED = 30;
  const TYPE_UNSADDLING_STEED = 31;
  const TYPE_DISASSEMBLING_VEHICLE = 32;
  const TYPE_REPAIRING_ROAD = 33;
  const TYPE_DESTROYING_ROAD = 34;
  const TYPE_BOOSTING_VEHICLE = 35;
  const TYPE_ADOPTING_STEED = 36;

  public static $TYPES_REQUIRING_RAWTOOLS = array(ProjectConstants::TYPE_ALTERING_SIGN, ProjectConstants::TYPE_BUTCHERING_ANIMAL);

  ///////////////all limits (turn count after that people can cancel a project) are in "turns" unit, for sample two Cantr days == 2 * 8
  const LIMIT_GATHERING_OTHERS     = 160;   //20 * 8
  const LIMIT_MANUFACTURING_OTHERS = 160;   //20 * 8
  const LIMIT_PICKING_LOCKS_OTHERS = 160;   //20 * 8
  const LIMIT_DISASSEMBLING_LOCKS_OTHERS = 160; // 20 * 8
  const LIMIT_DISASSEMBLING_OTHERS = 160;   //20 * 8
  const LIMIT_IMPROVINGROAD_OTHERS = 160;   //20 * 8
  const LIMIT_ALTERING_SIGN_OTHERS = 40;    // 5 * 8
  const LIMIT_HEALING_NEAR_DEATH   = 32;    // 4 * 8
  const LIMIT_BURYING_OTHERS = 16;          // 2 * 8
  const LIMIT_DESC_BUILDING_CHANGE = 2;     // 2 hours
  const LIMIT_ANIMAL_OTHERS = 48;           // 6 * 8
  const LIMIT_REPAIRING_OTHERS = 40;        // 5 * 8
  const LIMIT_SADDLING_OTHERS = 48;         // 6 * 8
  const LIMIT_DISASSEMBLING_VEHICLE_OTHERS = 40;   // 5 * 8
  //in first two working hours disassembling can be cancel instantly
  const LIMIT_INSTANT_DISASSEMBLING = 2;
  const LIMIT_ROAD_REPAIRING_OTHERS = 160;
  const LIMIT_ROAD_DESTRUCTION_OTHERS = 160;

  // it's possible to cancel projects in vehicle much faster
  const LIMIT_VEHICLE_GATHERING_OTHERS     = 16;   //2 * 8
  const LIMIT_VEHICLE_MANUFACTURING_OTHERS = 16;   //2 * 8
  const LIMIT_VEHICLE_PICKING_LOCKS_OTHERS = 40;   //5 * 8
  const LIMIT_VEHICLE_DISASSEMBLING_LOCKS_OTHERS = 40; // 5 * 8
  const LIMIT_VEHICLE_DISASSEMBLING_OTHERS = 16;   //2 * 8
  const LIMIT_VEHICLE_IMPROVINGROAD_OTHERS = 16;   //2 * 8
  const LIMIT_VEHICLE_ALTERING_SIGN_OTHERS = 16;   // 2 * 8
  const LIMIT_VEHICLE_HEALING_NEAR_DEATH   = 32;    // 4 * 8
  const LIMIT_VEHICLE_BURYING_OTHERS       = 16;    // 2 * 8
  const LIMIT_VEHICLE_DESC_BUILDING_CHANGE = 2;     // 2 hours
  const LIMIT_VEHICLE_ANIMAL_OTHERS = 16;           // 2 * 8
  const LIMIT_VEHICLE_REPAIRING_OTHERS = 16;        // 2 * 8
  const LIMIT_VEHICLE_SADDLING_OTHERS = 16;         // 2 * 8

  // how many hours it takes to heal char in NDS
  const NEAR_DEATH_HEAL_PROJECT_DAYS = 1;

  // object description change
  const CHANGE_DESC_FRACTION_OF_MANU_TIME = 0.25; // what percent of manufacturing time should desc change project take
  const CHANGE_DESC_DEFAULT_DAYS = 1; // how many days if impossible to get

  // mass production
  const MASS_PRODUCTION_MAX = 8;

  // vehicle disassembling
  const VEHICLE_DISASEMBLING_DEFAULT_DAYS = 3;

  // server.projects constants
  const TURNS_PER_DAY = 96;

  const DECAY_PER_TURN = 1 / self::TURNS_PER_DAY;
  const DEFAULT_PROGRESS_PER_DAY = 800;
  const DEFAULT_PROGRESS_PER_TURN = self::DEFAULT_PROGRESS_PER_DAY / self::TURNS_PER_DAY;
  const PROJECT_SKILL_GAIN_PER_TURN = 32 / self::TURNS_PER_DAY; // 32 per day, 640 per year

  const TIREDNESS_FROM_WORKING_PER_TURN = 2400 / self::TURNS_PER_DAY;
  const TIREDNESS_RECOVER_PER_TURN = 4000 / self::TURNS_PER_DAY;
  const DRUNKENNESS_RECOVER_PER_TURN = 4000 / self::TURNS_PER_DAY;

  const GIVING_LOYAL_ANIMAL_SPEED = 10; // helping sb else in adoption of animals loyal to us multiplier

  // finish project constants
  const CREATED_IN_INVENTORY = 1;
  const CREATED_ON_GROUND = 2;

  const PROGRESS_MANUAL = 0;
  const PROGRESS_AUTOMATIC = 1;
  const PROGRESS_SEMIAUTOMATIC = 2;

  const DIGGING_SLOTS_NOT_USE = 0;
  const DIGGING_SLOTS_USE = 1;

  const PARTICIPANTS_NO_LIMIT = 0; // and > 0 would be max number of participants

}
