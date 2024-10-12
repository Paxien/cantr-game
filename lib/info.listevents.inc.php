<?php

// this script is resource-intensive
ini_set("memory_limit", "512M");
set_time_limit(600);


// SANITIZE INPUT
$src_observer = HTTPContext::getString('src_observer', null);
$src_day = HTTPContext::getString('src_day', null);
$src_hour = HTTPContext::getString('src_hour', null);
$observer_id = HTTPContext::getInteger('observer_id');
$first = HTTPContext::getInteger('first', null);

$src_par1 = $_REQUEST['src_par1'];
$src_par2 = $_REQUEST['src_par2'];

$plr = Request::getInstance()->getPlayer();
if (!$plr->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {
  CError::throwRedirect("player", "You are not authorized to read the players list");
}

  $param = "1=1";
  if ($src_par1) {
    $param .= " AND parameters LIKE '%" . urlencode($src_par1) . "%'";
    if ($src_par2) {
      $param .= " AND parameters LIKE '%" . urlencode($src_par2) . "%'";
    }
  }

  if ($src_day) {
    $param .= " AND day=" . intval($src_day);
  }
  if ($src_hour) {
    $param .= " AND hour=" . intval($src_hour);
  }

$db = Db::get();
if (!isset($first)) {

  $first = 0;
  if (!$observer_id) {

    $report = "PD member " . $plr->getFullName() . " (id=" . $plr->getId() . ") searching for events fitting '$param'.";
  } else {
    try {
      $observedChar = Character::loadById($observer_id);
      $observedPlayer = Player::loadById($observedChar->getPlayer());

      $report = "PD member " . $plr->getFullName() . " (id=" . $plr->getId() . ") searching for events of character $observer_id (of player " . $observedPlayer->getId() . " " . $observedPlayer->getFullName() . ").";
    } catch (InvalidArgumentException $e) {
      $report = "PD member " . $plr->getFullName() . " (id=" . $plr->getId() . ") searching for events of character $observer_id (of player " . ($observedChar ? $observedChar->getPlayer() : "???") . " (THAT DOESN'T EXIST)).";
    }
  }

  Report::saveInDb("eventsearch", $report);
}


if ($observer_id) {
  $stm = $db->prepare("SELECT events.id AS id,events.type AS type,events.parameters AS parameters,
      events.day AS day,events.hour AS hour, events.minute, events_obs.observer as observer
    FROM events,events_obs WHERE events_obs.observer = :observer AND events_obs.event = events.id ORDER BY events.id DESC");
  $stm->bindInt("observer", $observer_id);
  $stm->execute();
} else {
  $stm = $db->query("SELECT events.*, events_obs.observer as observer FROM events, events_obs
    WHERE $param AND events_obs.event = events.id GROUP BY events.id");
}

show_title("LIST EVENTS");

echo "<br />";


function translateArray($events, Character $observer, Player $plr)
{
  $SEPARATOR = "SEPARATOR-" . mt_rand();
  $events = implode($SEPARATOR, $events);

  $tag = new Tag();
  $tag->html = false;
  $tag->language = $plr->getLanguage();
  $tag->character = $observer->getId();
  $tag->content = $events;
  $tag->admin = true;
  $events = $tag->interpret();

  // split again interpreted text
  return explode($SEPARATOR, $events);
}


$EVENTS_MEMORY_LIMIT = 50000;
if ($stm->rowCount() > $EVENTS_MEMORY_LIMIT) {
  echo "Found " . $stm->rowCount() . " events, which is more than upper limit of this tool.<br>";
} else {

  $anyObserverId = 1;
  $events = [];
  $tagsQueue = [];
  foreach ($stm->fetchAll() as $event_info) {
    $charName = "<a href=\"index.php?page=listevents&observer_id={$event_info->observer}\"><CANTR CHARNAME ID=" . $event_info->observer . "></a>";
    $eventTag = "<CANTR REPLACE NAME=event_$event_info->type $event_info->parameters>";
    $events[] = [
      "text" => $eventTag,
      "prefix" => "$event_info->day-$event_info->hour:$event_info->minute: [$charName]",
    ];
    $tagsQueue[] = $eventTag;

    $anyObserverId = $event_info->observer;
  }

  $anyObserver = Character::loadById($anyObserverId);

  $replaceTag = new ReplaceTag();
  $replaceTag->character = $anyObserver->getId();
  $replaceTag->language = $plr->getLanguage();
  $replaceTag->admin = true;
  $interpretedEvents = $replaceTag->interpretQueue($tagsQueue);

  foreach ($events as &$event) {

    $eventText = $interpretedEvents[$event["text"]];
    $event = $event["prefix"] . $eventText;
  }
  unset($interpretedEvents);
  $events = translateArray($events, $anyObserver, $plr);

  foreach ($events as $event) {
    echo $event . "<br><br>";
  }
}

echo "<center><table><tr><td align=\"center\">";
echo "<br /><a href=\"index.php?page=pendingplayers\">Manage database of pending player</a>";
echo "<br />Go <a href=\"index.php?page=listplayers\">back to player info</a>";
echo "<br />Go <a href=\"index.php?page=player\">back to your player page</a></td></tr>";
echo "</table></center>";
