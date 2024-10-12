<?php

// SANITIZE INPUT
$le = HTTPContext::getInteger('le');



$eventList = new EventListView($char, true);

$events = $eventList->interpret($le);
$newestEventId = $eventList->getNewestEventId();

foreach ($events as &$event) {
 $event = EncodeURIs($event);
}

$db = Db::get();
$stm = $db->prepare("UPDATE newevents SET new = 1 WHERE person = :charId");
$stm->bindInt("charId", $char->getId());
$stm->execute();

echo json_encode([
  "events" => $events,
  "newestEventId" => $newestEventId,
]);
