<?php

$page = "server.messengers";
include "server.header.inc.php";

$db = Db::get();

$stm = $db->query("SELECT mb.object_id FROM messenger_birds mb WHERE NOT EXISTS (SELECT 1 FROM objects WHERE id = mb.object_id)");
$birdRowsToRemove = $stm->fetchScalars();

if (count($birdRowsToRemove)) {
  $stm = $db->prepareWithIntList("DELETE FROM messenger_birds WHERE object_id IN (:inexistentBirds)", [
    "inexistentBirds" => $birdRowsToRemove,
  ]);
  $stm->execute();
}

$stm = $db->query("SELECT object_id FROM messenger_birds WHERE goal_which_home IS NOT NULL");
$flyingMessengerBirdsIds = $stm->fetchScalars();

$messengerBirdObjects = CObject::bulkLoadByIds($flyingMessengerBirdsIds);

foreach ($messengerBirdObjects as $birdId => $birdObject) {
  try {
    $messengerBird = new MessengerBird($birdObject, $db);
    $fromX = $messengerBird->getX();
    $fromY = $messengerBird->getY();
    $goalWhichHome = $messengerBird->getGoalWhichHome();
    $homeId = $messengerBird->getHomeId($goalWhichHome);
    $homeRootId = $messengerBird->getHomeRootId($goalWhichHome);
    $homeRoot = Location::loadById($homeRootId);

    $messengerBird->continueFlyingToHome();

    $messengerBird->saveInDb();
    if ($messengerBird->getGoalWhichHome()) {
      echo $birdObject->getId() . " moving from ($fromX, $fromY) to " .
        "(" . $messengerBird->getX() . ", " . $messengerBird->getY() . ") in order to move to " .
        "the nest $homeId in $homeRootId (" . $homeRoot->getX() . ", " . $homeRoot->getY() . ")<br>\n";
    } else {
      echo $birdObject->getId() . " from ($fromX, $fromY) reaches the nest $homeId in $homeRootId " .
        "(" . $homeRoot->getX() . ", " . $homeRoot->getY() . ")<br>\n";
    }
  } catch (Exception $e) {
    echo "Exception thrown for bird " . $birdObject->getId() . ". Skipping<br>\n";
    Logger::getLogger(__FILE__)->warn("Error when processing bird {$birdObject->getId()}", $e);
  }
}
