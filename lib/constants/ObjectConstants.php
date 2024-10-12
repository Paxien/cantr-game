<?php

class ObjectConstants {
  
  const TYPE_NOTE = 1;
  const TYPE_RAW = 2;
  const TYPE_DEAD_BODY = 7;
  const TYPE_BUILDING_LOCK = 12;
  const TYPE_INNER_LOCK = 13;
  const TYPE_KEY = 30;
  const TYPE_ENVELOPE = 37;
  const TYPE_NOTICEBOARD = 100;
  const TYPE_VEHICLE_LOCK = 138;
  const TYPE_CROWBAR = 185;
  
  const TYPE_WHEELBARROW = 202;
  const TYPE_IMPROVED_WHEELBARROW = 203;

  const TYPE_COIN_PRESS = 211;
  const TYPE_SEAL = 212;
  const TYPE_LIGHTHOUSE = 109;
  const TYPE_SEXTANT = 635;
  const TYPE_SIGNAL_FIRE = 657;

  const TYPE_KEYRING = 1270;

  const TYPE_SADDLE = 1388;

  const TYPE_RAILWAY_STATION = 1439;
  const TYPE_RAILWAY_BARRICADE = 1440;
  const TYPE_TRAIN_CONTROLS = 1441;

  const TYPE_SMALL_SHIPWRECK = 1524;
  const TYPE_SHIPWRECK = 1525;

  const TYPE_TERRAIN_DESERT = 1731;

  const OBJCAT_HARBOURS = 4;
  const OBJCAT_TEMPORARILY_UNMANUFACTURABLE = 5;
  const OBJCAT_ENGINES = 8;
  const OBJCAT_INSTRUMENTS = 22;
  const OBJCAT_CLOTHES = 26;
  const OBJCAT_SHIPS = 34;
  const OBJCAT_DOMESTICATED_ANIMALS = 61;
  const OBJCAT_TRAINS = 65;
  const OBJCAT_TERRAIN_AREAS = 92;

  const SETTING_PORTABLE = 1; // normal
  const SETTING_QUANTITY = 2; // stackable (coins & raws)
  const SETTING_FIXED = 3; // no taking/dragging
  const SETTING_HEAVY = 4; // no taking

  const WEIGHT_COIN = 10;

  // objects visibility in other locations
  const VISIBLE_FAR_AWAY = 1; // visible from close prox but also from far away, i.e. on other ships on sea
  const VISIBLE_CLOSE = 2; // visible from close proximity, i.e. in car in the same location

  const MAX_RINGS_WORN = 10;
  
  const MAX_OBJECTS_SHOWN = 1000;

  const MAX_OBJECTS_IN_CONTAINER_SHOWN = 6;

  // arrays can't be constant so a static variable
  public static $TYPES_COINS = array(578,418,414,577,416,417,413,415,580,579,576,808);
  public static $TYPES_LOCKPICKING = array(self::TYPE_CROWBAR);
  public static $NON_USABLE_TYPES = array(ObjectConstants::TYPE_NOTE, ObjectConstants::TYPE_RAW, ObjectConstants::TYPE_ENVELOPE);
  public static $NON_USABLE_CATEGORIES = array(ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS);
}
