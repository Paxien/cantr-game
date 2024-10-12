<?php
require_once ("func.projectsetup.inc.php");

$smarty = new CantrSmarty;


$v_link_used = $player != $char->getPlayer();

if ($v_link_used) {
  $playerInfo = Request::getInstance()->getPlayer();
  if (!$playerInfo->hasAccessTo(AccessConstants::CONTROL_OTHER_CHARACTERS)) {
    CError::throwRedirectTag("player", "error_you_are_not_allowed_to_view_events_of_other_players");
  }
}

if ($player == $char->getPlayer()) {
  $char->updateLastDateAndTime(GameDate::NOW());
  $char->saveInDb();
}

$db = Db::get();

$charInfo = new CharacterInfoView($char);
$charInfo->show();

function ownProjectsUp($array, $charID) {
  $out = array();
  foreach($array as $row)
    if ($row['initiator'] == $charID)
      $out[] = $row;

  foreach($array as $row)
    if ($row['initiator'] != $charID)
      $out[] = $row;

  return $out;
}


if ($char->getLocation() != 0) {
  $location = Location::loadById($char->getLocation());

  $smarty->assign("locationType", $location->getType());

  if ($location->isOutside()) {

    $smarty->assign ("TOTAL", $location->getDiggingSlots());
    $smarty->assign ("USED", $location->getAllUsedDiggingSlots());
  }

  $display_active_only = isset ($_POST['active_search']);
  $smarty->assign ("activechecked", $display_active_only);

  $stm = $db->prepare("
    SELECT p.id, p.name, p.type, p.turnsleft, p.reqleft, p.reqneeded, p.turnsneeded, initiator, p.location, init_day, init_turn, 
      COUNT(ch.id) AS workers, SUM(ch.id = :charId) AS participates, COUNT(ch2.id) AS initiatorhere, p.automatic as automatic 
    FROM projects p LEFT OUTER JOIN chars ch ON p.id = ch.project
      LEFT OUTER JOIN chars ch2 ON ch2.id = p.initiator 
           AND ch2.status = :active AND ch2.location = p.location
    WHERE p.location = :locationId
    GROUP BY p.id, p.name, p.type, p.turnsleft, p.reqleft, p.reqneeded, p.turnsneeded, initiator, p.location, init_day, init_turn, p.automatic
    ORDER BY p.name");
  $stm->bindInt("charId", $char->getId());
  $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
  $stm->bindInt("locationId", $char->getLocation());
  $stm->execute();


  $projects = $stm->fetchAll();

  // tool & objectid requirements
  $objectIds = array();
  $toolsNeeded = array();
  foreach ($projects as $project) {
    $requirements = Parser::rulesToArray($project->reqneeded);
    if ($requirements['objectid']) {
      $objectIds = array_merge($objectIds, explode(",", $requirements['objectid']));
    }
    if ($requirements['tools']) {
      $toolsNeeded = array_merge($toolsNeeded, explode(",", $requirements['tools']));
    }
  }
  $objectIds = array_values(array_unique($objectIds));
  $toolsNeeded = array_values(array_unique($toolsNeeded));

  $objectsInLocation = ObjectHandler::getObjectArrayInLocation($objectIds, $char->getLocation());
  $toolsInInventory = ObjectHandler::getObjectArrayByNameInInventory($toolsNeeded, $char->getId());

  $sprojects = array();
  foreach ($projects as $project_info) {
    $display_project = $project_info->workers || !$display_active_only;

    $sproject = array();
    $sproject['id'] = $project_info->id;
    $sproject['name'] = $project_info->name;
    $sproject['color'] = $project_info->workers ? 1 : 0;
    $sproject['joinable'] = $project_info->automatic != 1;

    $hasAllReq = projectHasReq( $project_info->reqleft );

    $requirements = Parser::rulesToArray($project_info->reqleft);

    $project = Project::loadById($project_info->id);
    $projectProgressProblems = $project->validateProgress($char);
    $sproject['hasneededstuff'] = empty($projectProgressProblems);

    if( $hasAllReq  ) {
      $sproject['percents'] = ( $project_info->turnsneeded == 0) ? 0 : TextFormat::getPercentFromFraction( ( 1 - ( $project_info->turnsleft / $project_info->turnsneeded ) ) );
    }

    if ($project_info->participates)
      $sproject['color'] = 2;

    if ($display_project) {

      $sproject['day'] = $project_info->init_day;
      $sproject['hour'] = $project_info->init_turn;
      if ($project_info->initiatorhere) {
        $sproject['initiator'] = $project_info->initiator;
      }

      $sprojects [] = $sproject;
    }
  }

  $sprojects = ownProjectsUp($sprojects, $character);

  $smarty->assign ("projects", $sprojects);
}

$smarty->displayLang ("page.projects.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();