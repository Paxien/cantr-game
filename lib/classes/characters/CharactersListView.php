<?php

class CharactersListView
{
  /** @var Player */
  private $player;

  /** @var Db */
  private $db;

  /** @var bool */
  private $displayInfoOnEmpty = true;

  /** @var string */
  private $charIdDataKey = "charId";

  public function __construct(Player $player, Db $db)
  {
    $this->player = $player;
    $this->db = $db;
  }

  public function show()
  {
    $stm = $this->db->prepareWithIntList(
      "SELECT chars.id, chars.project,
        chars.name, chars.sex, chars.location,
        health.value AS health,
        hunger.value AS hunger,
        drunkenness.value AS drunkenness,
        projects.name AS project_name,
        newevents.new AS new_events,
        draggers.dragging_id FROM chars 
      LEFT JOIN states health ON health.person=chars.id
        AND health.type = " . StateConstants::HEALTH . "
      LEFT JOIN states hunger ON hunger.person=chars.id
        AND hunger.type = " . StateConstants::HUNGER . "
      LEFT JOIN states drunkenness ON drunkenness.person = chars.id
        AND drunkenness.type = " . StateConstants::DRUNKENNESS . "
      LEFT JOIN projects ON projects.id=chars.project
      LEFT JOIN newevents ON newevents.person = chars.id
        AND newevents.new = 0
      LEFT JOIN draggers ON draggers.dragger = chars.id
      WHERE player = :player AND status IN (:statuses)
      ORDER BY language, id",
      [
        "statuses" => [
          CharacterConstants::CHAR_PENDING,
          CharacterConstants::CHAR_ACTIVE,
        ]
      ]);
    $stm->bindInt("player", $this->player->getId());
    $stm->execute();

    $chars = [];
    foreach ($stm->fetchAll() as $char_info) {
      $char_info->new_events = $char_info->new_events !== null;

      $char_loc = new char_location($char_info->id, $this->db);
      $short = $this->getStateIdentifier($char_loc);

      $char_info->buttonname = $short;
      $char_info->project_name = TagBuilder::forText($char_info->project_name)
        ->observedBy($char_info->id)->allowHtml(false)->db($this->db)->build()->interpret();
      $char_info->loc_name = TagBuilder::forText($char_loc->name)
        ->observedBy($char_info->id)->allowHtml(false)->db($this->db)->twice()->build()->interpret();
      $char_info->near_death_state = CharacterHandler::getNearDeathState($char_info->id);
      $char_info->progress = $this->getWorkAndTravelState($char_loc, $char_info);
      $char_info->link = $this->getLinkToCharacter($char_info);
      $chars[] = $char_info;
    }

    $smarty = new CantrSmarty();
    $smarty->assign("chars", $chars);

    $smarty->assign("CONSTANTS", [
      "DRUNK_STATE_MIN" => [
        CharacterConstants::DESC_DRUNK_1_MIN,
        CharacterConstants::DESC_DRUNK_2_MIN,
        CharacterConstants::DESC_DRUNK_3_MIN,
        CharacterConstants::DESC_DRUNK_4_MIN,
        CharacterConstants::DESC_DRUNK_5_MIN,
      ],
      "PASSED_OUT_MIN" => CharacterConstants::PASSOUT_LIMIT,
    ]);
    $smarty->assign("charIdDataKey", $this->getCharIdDataKey());

    ob_start();
    $smarty->displayLang("template.characters.list.tpl",
      LanguageConstants::$LANGUAGE[$this->player->getLanguage()]["lang_abr"]);
    return ob_get_clean();
  }

  protected function getLinkToCharacter($char_info)
  {
    return "index.php?page=char.events&character={$char_info->id}&noformat=1";
  }

  /**
   * @param $char_loc
   * @return bool true when
   */
  private function isBoat($char_loc)
  {
    $boats = Location::getShipTypeArray();
    return in_array($char_loc->typeid, $boats);
  }

  /**
   * @param $char_loc
   * @return string
   */
  private function getStateIdentifier(char_location $char_loc)
  {

    $short = "small_char_happy";
    if ($char_loc->isflying) {
      $short = "fly";
    }

    if ($char_loc->istravelling && $char_loc->isvehicle) {
      $short = "veh";
    }

    if (!$char_loc->istravelling && $char_loc->isvehicle) {
      $short = "arr";
    }

    if ($char_loc->issailing && $char_loc->isvehicle) {
      $short = "sea";
    }

    if ($char_loc->isbuilding) {
      $short = "ins";
    }

    if ($char_loc->istravelling && !$char_loc->isvehicle) {
      $short = "tra";
    }

    if ($char_loc->islocation && !$char_loc->isbuilding) {
      $short = "out";
    }

    if (!$char_loc->issailing && $char_loc->isvehicle && $this->isBoat($char_loc)) {
      $short = "dock";
    }

    return $short;
  }

  /**
   * @param $char_loc
   * @param $char_info
   * @return string
   */
  protected function getWorkAndTravelState($char_loc, $char_info)
  {
    $progr_str = "";
    if ($char_loc->istravelling) {
      try {
        $travel = Travel::loadByParticipant(Character::loadById($char_info->id, $this->db), $this->db);
        $percentDone = TextFormat::getPercentFromFraction($travel->getFractionDone());
        $progr_str .= " (<b>T</b> $percentDone %)<br>";
      } catch (InvalidArgumentException $e) {
        Logger::getLogger("page.player")->warn("char $char_info->id travelling, but travel doesn't exist");
      }
    }

    if ($char_info->project) {
      try {
        $project = Project::loadById($char_info->project, $this->db);
        if ($project->getType() == ProjectConstants::TYPE_RESTING) {
          $progr_str .= " (R)";
        } else {
          $percentDone = TextFormat::getPercentFromFraction($project->getFractionDone(), 1);
          $progr_str .= " (<b>P</b> $percentDone %)";
        }
      } catch (InvalidArgumentException $e) {
        Logger::getLogger("page.player")->warn("char $char_info->id working on project $char_info->project which doesn't exist");
      }
    }

    if ($char_info->dragging_id) {
      try {
        $dragging = Dragging::loadById($char_info->dragging_id);
        $percentDone = TextFormat::getPercentFromFraction($dragging->getFractionDone());
        $progr_str .= " (<b>D</b> $percentDone %)";
      } catch (InvalidArgumentException $e) {
        Logger::getLogger("page.player")->warn("char $char_info->id taking part in dragging $char_info->dragging_id, but dragging doesnt exist");
      }
    }
    return $progr_str;
  }

  public function setDisplayInfoOnEmpty($displayInfoOnEmpty)
  {
    $this->displayInfoOnEmpty = boolval($displayInfoOnEmpty);
  }

  /**
   * @return string
   */
  public function getCharIdDataKey()
  {
    return $this->charIdDataKey;
  }

  /**
   * @param string $charIdDataKey
   */
  public function setCharIdDataKey($charIdDataKey)
  {
    $this->charIdDataKey = strval($charIdDataKey);
  }
}