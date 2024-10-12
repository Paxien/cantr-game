<?php 

class ConnectionConstants
{
  // inland
  const TYPE_PATH = 1;
  const TYPE_SAND_ROAD = 2;
  const TYPE_PAVED_ROAD = 7;
  const TYPE_HIGHWAY = 10;
  const TYPE_EXPRESSWAY = 11;
  const TYPE_RAILROAD = 12;

  // water
  const TYPE_LAKE = 3;
  const TYPE_SEA = 5;

  // road destruction
  const DESTRUCTION_TO_IMPROVEMENT_TIME = 0.5;
  const DESTRUCTION_TO_IMPROVEMENT_RET_RAWS = 0.5;

  // road repair
  const REPAIR_TO_IMPROVEMENT_TIME = 0.25;
  const REPAIR_TO_IMPROVEMENT_COST = 0.25;
}
