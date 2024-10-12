<?php

class RawsGathering
{
  private $char;
  private $rawtype;
  private $amount;
  private $repeats;

  public $error;
  /** @var Db */
  private $db;

  public function __construct(Character $char, $rawtype, $amount, $repeats, Db $db)
  {
    $this->char = $char;
    $this->rawtype = intval($rawtype);
    $this->amount = intval($amount);
    $this->repeats = $repeats;
    $this->db = $db;
  }

  public function validate()
  {

    $accepted = true;

    $location = $this->char->getLocation();

    if ($this->char->isDragging()) {
      $this->error = "<CANTR REPLACE NAME=error_no_dig_while_drag>";
      $accepted = false;
    }

    $stm = $this->db->prepare("SELECT COUNT(*) FROM raws WHERE location = :locationId AND type = :type");
    $stm->bindInt("locationId", $location);
    $stm->bindInt("type", $this->rawtype);
    $rawsInLoc = $stm->executeScalar();
    if ($rawsInLoc == 0) {
      $this->error = "<CANTR REPLACE NAME=error_no_dig_here>";
      $accepted = false;
    }

    if ($location == 0) {
      $this->error = "<CANTR REPLACE NAME=error_not_while_travel>";
      $accepted = false;
    }

    $stm = $this->db->prepare("SELECT * FROM rawtypes WHERE id = :id");
    $stm->bindInt("id", $this->rawtype);
    $stm->execute();
    $rawtype_info = $stm->fetchObject();

    $max_amount_to_dig_for = floor(80 * $rawtype_info->perday);

    if (!is_numeric($this->amount)) {

      $this->error = "<CANTR REPLACE NAME=error_use_project_invalid_value MAX=$max_amount_to_dig_for>";
      $accepted = false;
    }

    $action = $rawtype_info->action;

    if ($this->amount <= 0) {
      $this->error = "You cannot $action zero grams or less.";
      $accepted = false;
    }

    if ($this->amount > $max_amount_to_dig_for) {
      $this->error = "You cannot $action more than {$max_amount_to_dig_for}g at once.";
      $accepted = false;
    }
    if (!is_numeric($this->repeats)) {
      $this->error = "<CANTR REPLACE NAME=error_limit_100_repeats>";
      $accepted = false;
    }

    return $accepted;
  }

  public function dig()
  {
    $location = $this->char->getLocation();

    $stm = $this->db->prepare("SELECT * FROM rawtypes WHERE id = :id");
    $stm->bindInt("id", $this->rawtype);
    $stm->execute();
    $rawtype_info = $stm->fetchObject();

    $action = "<CANTR REPLACE NAME=project_gather_digging>";
    if ($rawtype_info->action == 'collect') { $action = "<CANTR REPLACE NAME=project_gather_collecting>"; }
    if ($rawtype_info->action == 'pump') { $action = "<CANTR REPLACE NAME=project_gather_pumping>"; }
    if ($rawtype_info->action == 'farm') { $action = "<CANTR REPLACE NAME=project_gather_farming>"; }
    if ($rawtype_info->action == 'catch') { $action = "<CANTR REPLACE NAME=project_gather_catching>"; }
    if ($rawtype_info->action == 'pick') { $action = "<CANTR REPLACE NAME=project_gather_picking>"; }

    $turnsleft = $this->amount / $rawtype_info->perday * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

    if ($turnsleft == 0) { $turnsleft = 1; }

    if ($this->repeats > 100) { $this->repeats = 100; }
    if ($this->repeats < 0)   { $this->repeats = 0; }

    $reqNeeded = "";
    if ($rawtype_info->agricultural) {
      $reqNeeded = 'agricultural:1';
    }
    $rawName = str_replace(" ", "_", $rawtype_info->name);

    $projectName = "$action <CANTR REPLACE NAME=raw_{$rawName}>";
    $generalSub = new ProjectGeneral($projectName, $this->char->getId(), $location);
    $typeSub = new ProjectType(ProjectConstants::TYPE_GATHERING, $this->rawtype, $rawtype_info->skill,
      ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT,
      ProjectConstants::DIGGING_SLOTS_USE);
    $requirementSub = new ProjectRequirement($turnsleft, $reqNeeded);
    $outputSub = new ProjectOutput(0, "$this->rawtype:$this->amount", $this->repeats);
    $project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
    $project->saveInDb();
    $id = $project->getId();

    $stm = $this->db->prepare("SELECT project FROM chars WHERE id = :charId");
    $stm->bindInt("charId", $this->char->getId());
    $charProject = $stm->executeScalar();

    if ($charProject == 0) {

      $usedSlots = Location::getUsedDiggingSlots($location);
      $maxSlots = Location::getMaxDiggingSlots($location);

      if ($usedSlots < $maxSlots) {
        $stm = $this->db->prepare("UPDATE chars SET project = :projectId WHERE id = :charId LIMIT 1");
        $stm->bindInt("projectId", $id);
        $stm->bindInt("charId", $this->char->getId());
        $stm->execute();
      }
    }
  }
}
