<?php

class Building extends Location
{
  /** @var string */
  private $typeRules;

  public function __construct($mysqlRow, Db $db)
  {
    parent::__construct($mysqlRow, $db);
    $this->typeRules = $this->getObjectType()->getRules();
  }

  public function canLookOnParent()
  {
    $objectsToSeeOutside = CObject::locatedIn($this->getId())->hasProperty("EnableSeeingOutside")->findAll();
    foreach ($objectsToSeeOutside as $objectToSeeOutside) {
      return $objectToSeeOutside->getSpecifics() == "open";
    }
    return false;
  }

  public function isDestroyable()
  {
    $rules = Parser::rulesToArray($this->typeRules);
    return isset($rules['destroyable']);
  }

  /**
   * Starts a building destruction project (and saves it in db) or throws exception if project already exists
   * @param Character $char
   * @return Project when project already exists
   * @throws Exception
   * @throws ProjectAlreadyExistsException when project already exists
   */
  public function beginDestructionProject(Character $char)
  {
    $rules = Parser::rulesToArray($this->typeRules);
    $destructionRules = Parser::rulesToArray($rules['destroyable'], ",>");

    // no project in THE SAME location
    $alreadyBeingDestroyed = Project::locatedIn($char->getLocation())
      ->types([ProjectConstants::TYPE_DESTROYING_BUILDING, ProjectConstants::TYPE_FIXING_DESTROYED_BUILDING])
      ->subtype($this->getId())->exists();
    if ($alreadyBeingDestroyed > 0) {
      throw new ProjectAlreadyExistsException("destruction of building " . $this->getId() . " already exists");
    }

    $uniqueName = $this->getTypeUniqueName();
    $projectName = "<CANTR REPLACE NAME=project_destroying_building> " .
      "<CANTR REPLACE NAME=item_" . $uniqueName . "_b> \"" . $this->getName() . "\"";

    if (isset($destructionRules['tools'])) {
      $tools = explode("/", $destructionRules['tools']);
      $reqNeeded = "tools:" . implode(",", $tools);
    } else {
      $reqNeeded = "";
    }

    // event for actor
    Event::createPersonalEvent(323, "TYPE=" . $this->getTypeUniqueName()
      . " NAME=" . urlencode($this->getName()), $char->getId());
    // event for people outside
    Event::createPublicEvent(324, "ACTOR=" . $char->getId() . " TYPE=" . $this->getTypeUniqueName()
      . " NAME=" . urlencode($this->getName()), $char->getId(), Event::RANGE_NEAR_LOCATIONS, array($char->getId()));
    // event for people directly inside
    Event::createEventInLocation(325, "", $this->getId(), Event::RANGE_SAME_LOCATION);

    $sublocDestructionDays = 0;
    // destruction time of all sublocations (only buildings) affects destruction time

    $isBuildingPredicate = function(Location $loc) {
      return $loc->getType() == LocationConstants::TYPE_BUILDING;
    };

    $sublocs = $this->getSublocationsRecursive($isBuildingPredicate);
    foreach ($sublocs as $sublocId) {
      // event for people in sublocations
      Event::createEventInLocation(326, "", $sublocId, Event::RANGE_SAME_LOCATION);

      $loc = Location::loadById($sublocId);
      if ($loc->isDestroyable()) {
        $rules = Parser::rulesToArray($loc->getTypeRules());
        $sublocDestructionRules = Parser::rulesToArray($rules['destroyable'], ",>");
        $sublocDestructionDays += $sublocDestructionRules['days'];
      }
    }

    // magic formula for days needed
    $daysNeeded = $destructionRules['days'] + pow($sublocDestructionDays, 3 / 4);

    $turnsNeeded = $daysNeeded * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;

    $generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
    $typeSub = new ProjectType(ProjectConstants::TYPE_DESTROYING_BUILDING, $this->getId(), 0,
      ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT,
      ProjectConstants::DIGGING_SLOTS_NOT_USE);
    $requirementSub = new ProjectRequirement($turnsNeeded, $reqNeeded);
    $outputSub = new ProjectOutput(0, "");

    $project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
    $project->saveInDb();
    return $project;
  }

  public function destroy()
  {
    $rules = Parser::rulesToArray($this->typeRules);
    $destructionRules = Parser::rulesToArray($rules['destroyable'], ",>");
    $ruinsTypeName = $destructionRules['into'];

    $stm = $this->db->prepare("SELECT id FROM objecttypes WHERE unique_name = :uniqueName");
    $stm->bindStr("uniqueName", $ruinsTypeName);
    $ruinsType = $stm->executeScalar();

      // remove fixed objects
    $fixedObjects = CObject::locatedIn($this)->setting(ObjectConstants::SETTING_FIXED)->findAll();
    $wipedObjects = new Statistic("wiped_in_ruins", Db::get());
    // remove all fixed objects and throw container contents on the ground
    foreach ($fixedObjects as $obj) {
      $wipedObjects->store($obj->getUniqueName(), 0, 1);
      $obj->remove();
      $obj->saveInDb();
    }

    // remove projects
    $projects = Project::locatedIn($this->getId())->findIds();
    foreach ($projects as $projectId) {
      $projectToCancel = ProjectCanceler::FromId($projectId, ProjectCanceler::NO_ACTOR, Db::get());
      $projectToCancel->returnUsedResources(1.0);
      $projectToCancel->deleteThisProject();
    }

    $this->setType(LocationConstants::TYPE_VEHICLE);
    $this->setArea($ruinsType);
    $this->saveInDb();
  }

  public function getTypeRules()
  {
    return $this->typeRules;
  }
}
