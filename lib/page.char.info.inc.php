<?php

class CharacterInfoView
{
  /** @var Character */
  private $char;

  public function __construct(Character $char)
  {
    $this->char = $char;
  }

  public function show()
  {
    $char_info_loc = new char_location($this->char->getId());
    $location_name = $char_info_loc->name;
    $location_desc = $this->char->getLocation() == 0 ? "" : " - [<CANTR LOCDESC ID=" . $this->char->getLocation() . ">]";


    /* ***************** CHARACTER INFO ********************* */

    $smarty = new CantrSmarty();
    $smarty->assign("character", $this->char->getId());
    $smarty->assign("age", $this->char->getAgeInYears());
    $smarty->assign("charname", $this->char->getName());
    $smarty->assign("charload", $this->char->getInventoryWeight());
    $smarty->assign("charlocation", $this->char->getLocation());
    $smarty->assign("charlocationtype", $char_info_loc->location_info->type);
    $smarty->assign("charlocationname", $location_name);
    $smarty->assign("locationdesc", $location_desc);
    $locationDescription = Descriptions::getDescription($this->char->getLocation(), Descriptions::TYPE_BUILDING);
    $smarty->assign("hasLocationDescription", !empty($locationDescription));

    $isOutside = $char_info_loc->issailing || $char_info_loc->istravelling
      || $char_info_loc->location_info->type == LocationConstants::TYPE_OUTSIDE;

    $canSeeWeather = $isOutside;
    if (!$isOutside) {
      $regionLoc = Location::loadById($char_info_loc->region);
      $canSeeWeather = $regionLoc->isOutside() && ($char_info_loc->location_info->type == LocationConstants::TYPE_VEHICLE
          || Window::hasOpenWindow($char_info_loc->id));
    }

    if ($canSeeWeather) {
      $charPos = $this->char->getPos();
      $weather = Weather::loadByPos($charPos["x"], $charPos["y"]);
      $smarty->assign("weatherType", $weather->getWeatherType());
      $smarty->assign("weatherName", $weather->getWeatherName());
      $smarty->assign("season", $weather->getSeason());
      $smarty->assign("seasonName", $weather->getSeasonName());
    }

    $smarty->assign("istravelling", $this->char->isTravelling());
    if ($this->char->isTravelling()) {
      try {
        $travel = Travel::loadByParticipant($this->char);
        $smarty->assign("travelprogress", TextFormat::getPercentFromFraction($travel->getFractionDone()));
      } catch (InvalidArgumentException $e) {
        Logger::getLogger("char.info")->error("travel for character " . $this->char->getId() . " doesn't exist");
      }
    }

    $smarty->assign("projectid", $this->char->getProject());

    if ($this->char->getProject() > 0) {
      try {
        $project = Project::loadById($this->char->getProject());
        $smarty->assign("percentcomplete", TextFormat::getPercentFromFraction($project->getFractionDone(), 1)); // print as ".1f"

        $tag = new tag($project->getName(), false);
        $tag = new Tag($tag->interpret(), false);
        $smarty->assign("projectname", htmlspecialchars($tag->interpret()));

        $problems = $project->validateProgress($this->char);
        $problems = Pipe::from($problems)->map(function($problem) {
          return TagBuilder::forTag($problem)->build()->interpret();
        })->toArray();
        $smarty->assign("projectProblems", $problems);
      } catch (InvalidArgumentException $e) {
        Logger::getLogger("char.info")->error("character " . $this->char->getId() . " working on non-existing project " . $this->char->getProject());
      }
    }

    try {
      $dragging = Dragging::loadByDragger($this->char->getId());
      $draggingView = new DraggingView($dragging, $this->char);
      $smarty->assign("dragging", $draggingView->getNameTag());
      $smarty->assign("draggingPercent", TextFormat::getPercentFromFraction($dragging->getFractionDone()));
    } catch (InvalidArgumentException $e) {
    }

    // death from old age
    if (Limitations::getLims($this->char->getId(), Limitations::TYPE_OLD_AGE_DEATH_LOCK) > 0) {
      $ctimeLeft = Limitations::getTimeLeft($this->char->getId(), Limitations::TYPE_OLD_AGE_DEATH_LOCK);
      $oldAgeDeathDate = GameDate::NOW()->plus(GameDate::fromTimestamp($ctimeLeft));
      $smarty->assign("estimatedDeathDate", $oldAgeDeathDate->getObject());
    } else {
      $canDie = Limitations::getLims($this->char->getId(), Limitations::TYPE_OLD_AGE_DEATH_ALLOW) > 0;
      $smarty->assign("canDieOfOldAge", $canDie);
    }

    $smarty->assign("is_near_death", $this->char->isNearDeath());

    $mainPanelButtons = [
      "char.events" => "button_events",
      "char.inventory" => "button_inventory",
      "char.description" => "button_location",
      "char.buildings" => "button_buildings",
      "char.people" => "button_people",
      "char.objects" => "button_objects",
      "char.projects" => "button_activity",
    ];

    $mainPanelButtons = Pipe::from($mainPanelButtons)->map(function($tag) {
      return "<CANTR REPLACE NAME=" . $tag . ">";
    })->toArray();


    $tag = new ReplaceTag();
    $translatedPanels = $tag->interpretQueue($mainPanelButtons);
    foreach ($mainPanelButtons as &$buttonText) {
      $buttonText = $translatedPanels[$buttonText];
    }

    $smarty->assign("mainPanel", $mainPanelButtons);

    $smarty->displayLang("page.char.info.tpl", $GLOBALS['lang_abr']);
  }
}