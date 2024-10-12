<?php

class MachineProject
{
  private $id;
  /** @var ObjectType|null */
  private $machineType;

  /** @var string */
  private $requirements;

  /** @var int */
  private $resultRawTypeId;

  /** @var int */
  private $resultAmount;

  /** @var bool */
  private $isFixedTime;

  /** @var string */
  private $name;

  /** @var int */
  private $maxParticipants;

  /** @var int */
  private $skill;

  /** @var int */
  private $progressMethod;

  private function __construct()
  {}

  public static function loadById($id)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM machines WHERE id = :id");
    $stm->bindInt("id", $id);
    $stm->execute();
    if ($fetchObj = $stm->fetchObject()) {
      return self::loadFromFetchObject($fetchObj);
    }
    throw new InvalidArgumentException("machine project for id " . $id . " does not exist");
  }

  /**
   * @param ObjectType $machine
   * @return MachineProject[]
   */
  public static function loadProjectsForMachineType(ObjectType $machine)
  {
    $db = Db::get();
    $stm = $db->prepare("SELECT * FROM machines WHERE type = :type");
    $stm->bindInt("type", $machine->getId());
    $stm->execute();
    $machineProjects = [];
    foreach ($stm->fetchAll() as $machineProjectInfo) {
      $machineProjects[$machineProjectInfo->id] = self::loadFromFetchObject($machineProjectInfo);
    }
    return $machineProjects;
  }

  /**
   * @param stdClass $machineInfo
   * @return MachineProject
   */
  public static function loadFromFetchObject(stdClass $machineInfo)
  {
    $machineProject = new self();
    $machineProject->id = $machineInfo->id;
    if ($machineInfo->type != null) {
      $machineProject->machineType = ObjectType::loadById($machineInfo->type);
    }

    list($machineProject->resultRawTypeId, $machineProject->resultAmount) = explode(":", $machineInfo->result);

    $machineProject->isFixedTime = !$machineInfo->multiply;
    $machineProject->name = $machineInfo->name;
    $machineProject->maxParticipants = $machineInfo->max_participants;
    $machineProject->skill = $machineInfo->skill;
    if (in_array($machineInfo->automatic, [ProjectConstants::PROGRESS_MANUAL,
      ProjectConstants::PROGRESS_AUTOMATIC, ProjectConstants::PROGRESS_SEMIAUTOMATIC])) {
      $machineProject->progressMethod = $machineInfo->automatic;
    }

    $machineProject->requirements = $machineInfo->requirements;

    return $machineProject;
  }

  /**
   * @return int ProjectConstants::PROGRESS_MANUAL|ProjectConstants::PROGRESS_AUTOMATIC|ProjectConstants::PROGRESS_SEMIAUTOMATIC
   */
  public function getProgressMethod()
  {
    return $this->progressMethod;
  }

  public function getId()
  {
    return $this->id;
  }

  /**
   * @return ObjectType|null type of machine on which project can be started
   */
  public function getMachineType()
  {
    return $this->machineType;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  public function getRequirementsString()
  {
    return $this->requirements;
  }

  public function getRequiredRaws()
  {
    $splitRequirements = Parser::rulesToArray($this->requirements);
    if (array_key_exists("raws", $splitRequirements)) {
      return Parser::rulesToArray($splitRequirements["raws"], ",>");
    }
    return [];
  }

  /**
   * @return array of tool names (form used in requirements)
   */
  public function getRequiredTools()
  {
    $splitRequirements = Parser::rulesToArray($this->requirements);
    if (array_key_exists("tools", $splitRequirements)) {
      return explode(",", $splitRequirements["tools"]);
    }
    return [];
  }

  public function getResultRawTypeId()
  {
    return $this->resultRawTypeId;
  }

  /**
   * @return int
   */
  public function getResultAmount()
  {
    return $this->resultAmount;
  }

  /**
   * @return boolean
   */
  public function isIsFixedTime()
  {
    return $this->isFixedTime;
  }

  /**
   * @return int
   */
  public function getMaxParticipants()
  {
    return $this->maxParticipants;
  }

  /**
   * @return int
   */
  public function getSkill()
  {
    return $this->skill;
  }
}