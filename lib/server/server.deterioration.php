<?php

$page = "server.deterioration";
include "server.header.inc.php";
include_once "func.expireobject.inc.php";

// deteriorate objects
print "Deteriorating\n";

$db = Db::get();
$globalConfig = new GlobalConfig($db);
$stm = $db->query("SELECT id, deter_rate_turn FROM objecttypes WHERE deter_rate_turn > 0 ORDER BY id");
foreach ($stm->fetchAll() as $row) {
  $stm = $db->prepare("UPDATE objects SET deterioration = deterioration + :deterChange
  WHERE type = :type  AND expired_date = 0
  AND NOT (type = 7 AND attached != 0)");
  $deterioration = $row->deter_rate_turn * $globalConfig->getDeteriorationRatio();
  $stm->bindInt("deterChange", $deterioration);
  $stm->bindInt("type", $row->id);
  $stm->execute();
  printf("Deteriorated objects of type %4d: %6d\n", $row->id, $stm->rowCount());
}

// deteriorate raws on ground
print "Food tainting:\n";

// statistics how much resources will rot
$rotStats = new Statistic("rot", Db::get());
$stm = $db->query("SELECT rt.name, SUM(o.weight * rt.tainting / 100) AS weight
    FROM objects o
    INNER JOIN locations l ON l.id = o.location AND l.type = 1
    INNER JOIN rawtypes rt ON rt.id = o.typeid AND rt.tainting > 0
    WHERE o.location > 0 AND o.type = 2 GROUP BY rt.id");
foreach ($stm->fetchAll() as $raw) {
  $rotStats->update($raw->name, 0, round($raw->weight));
}

$stm = $db->query("SELECT id, tainting FROM rawtypes WHERE tainting > 0");
foreach ($stm->fetchAll() as $rawType) {
  $multiplier = 1 - max(0, min(1, ($rawType->tainting / 100) * $globalConfig->getDeteriorationRatio()));
  $stm = $db->prepare("UPDATE objects o
      INNER JOIN locations l ON l.id = o.location AND l.type = 1
      SET o.weight = o.weight * :multiplier
      WHERE o.location > 0 AND o.type = 2 AND o.typeid = :rawType");
  $stm->bindFloat("multiplier", $multiplier);
  $stm->bindInt("rawType", $rawType->id);
  $stm->execute();
}

expire_multiple_objects("type = 2 AND weight = 0");

//fix all negative deterioration figures (old repair projects)
$db->query("UPDATE objects SET deterioration = 0 WHERE deterioration < 0");

//alter weight of dead bodies on deterioration
$stm = $db->prepare("UPDATE objects SET weight = GREATEST(0, ROUND(:bodyWeight * (1 - (deterioration / 10000)))) WHERE type = 7 AND expired_date = 0");
$stm->bindInt("bodyWeight", CharacterConstants::BODY_WEIGHT);
$stm->execute();

// road deterioration
$stm = $db->prepare("UPDATE connections c INNER JOIN connecttypes ct ON ct.id = c.type
  SET c.deterioration = c.deterioration + ct.deter_rate_turn * :deteriorationRatio");
$stm->bindInt("deteriorationRatio", $globalConfig->getDeteriorationRatio());
$stm->execute();

//process destroyed objects:
$crumblingStats = new Statistic("crumbling", $db);
$stm = $db->query("SELECT objects.location AS loc, objects.id AS id, objects.person AS pl, objecttypes.unique_name AS name,
  objecttypes.rules AS rules, objects.weight AS weight, objects.attached AS attached
  FROM objects, objecttypes WHERE objects.type=objecttypes.id AND deterioration > 10000 AND objects.expired_date = 0");
foreach ($stm->fetchAll() as $killed_objects) {
  if ($killed_objects->pl > 0) {
    Event::create(126, "OBJECT=$killed_objects->id")->forCharacter($killed_objects->pl)->show();
    $crumblingStats->update($killed_objects->name, 0, 1);
  } else {
    if ($killed_objects->loc > 0) {
      Event::create(125, "OBJECT=$killed_objects->id")->inLocation($killed_objects->loc)->show();
    }
    $crumblingStats->update($killed_objects->name, 0, 1);
  }

  $storage = $killed_objects->attached;
  $i = 0;
  while ($i < 100 && $storage > 0) { // reduce weight of container
    $stm = $db->prepare("UPDATE objects SET weight = weight - :weight WHERE id = :objectId LIMIT 1");
    $stm->bindInt("weight", $killed_objects->weight);
    $stm->bindInt("objectId", $storage);
    $stm->execute();

    $stm = $db->prepare("SELECT attached FROM objects WHERE id = :objectId");
    $stm->bindInt("objectId", $storage);
    $storage = $stm->executeScalar();
    $i++;
  }
}

// For deteriorated bodies, set status in chars table to 'buried'
$stm = $db->query("SELECT typeid FROM objects WHERE type=7 AND deterioration > 10000");
foreach ($stm->fetchAll() as $deteriorated_bodies) {
  $stm = $db->prepare("UPDATE chars SET status = :status WHERE id = :charId LIMIT 1");
  $stm->bindInt("status", CharacterConstants::CHAR_BURIED);
  $stm->bindInt("charId", $deteriorated_bodies->typeid);
  $stm->execute();
}


expire_multiple_objects("deterioration > 10000");


print "\n deteriorating";

include "server/server.footer.inc.php";
