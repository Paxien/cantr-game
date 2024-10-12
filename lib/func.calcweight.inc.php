<?php
include_once "stddef.inc.php";

function calcweight($id)
{
  $weight = 0;

  $weight += calcweightlocal($id);

  $locIds = getAllChildrenLocations($id);

  $db = Db::get();
  if (!empty($locIds)) {
    $stm = $db->prepareWithIntList("SELECT SUM(project_weight) FROM locations l
    INNER JOIN objecttypes ot ON ot.id = l.area
    WHERE l.type IN (2, 3, 5) AND l.id IN (:locationIds)", [
      "locationIds" => $locIds,
    ]);
    $weightOfLocations = $stm->executeScalar();

    $weight += $weightOfLocations;
    $weight += calculateTotalWeightOfContents($locIds, $db);
  }

  return $weight;
}

function getAllChildrenLocations($id)
{
  $parents = [$id];
  $locIds = [];
  do {
    $children = LocationFinder::any()
      ->regions($parents)
      ->types([
        LocationConstants::TYPE_BUILDING,
        LocationConstants::TYPE_VEHICLE,
        LocationConstants::TYPE_SAILING_SHIP,
      ])->findIds();
    $locIds = array_values(array_merge($locIds, $children));
    $parents = $children;
  } while (count($parents) > 0);

  return $locIds;
}

function calcweightlocal($id)
{
  return calculateTotalWeightOfContents([$id], Db::get());
}

function calculateTotalWeightOfContents(array $ids, Db $db)
{
  if (!count($ids)) {
    return 0;
  }

  if (!Validation::isPositiveIntArray($ids)) {
    $locationsStr = implode(",", $ids);
    throw new InvalidArgumentException("ids must be an array of positive integers: " . $locationsStr);
  }

  $weight = 0;

  // chars + inventories
  $stm = $db->prepareWithIntList("SELECT id FROM chars WHERE location IN (:locationIds) AND status = :active", [
    "locationIds" => $ids,
  ]);
  $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
  $stm->execute();
  $charIds = $stm->fetchScalars();
  if (count($charIds) > 0) {
    $weight += CharacterConstants::BODY_WEIGHT * count($charIds);
    $stm = $db->prepareWithIntList("SELECT SUM(weight) FROM objects WHERE person IN (:charIds)", [
      "charIds" => $charIds,
    ]);
    $weight += $stm->executeScalar();
  }

  // objects
  $stm = $db->prepareWithIntList("SELECT SUM(weight) FROM objects WHERE location IN (:locationIds)", [
    "locationIds" => $ids,
  ]);
  $weight += $stm->executeScalar();

  // projects
  $stm = $db->prepareWithIntList("SELECT SUM(weight) FROM projects WHERE location IN (:locationIds)", [
    "locationIds" => $ids,
  ]);
  $weight += $stm->executeScalar();

  return $weight;
}

function addcapacity($id, Db $db)
{
  $maxweight = 0;
  $stm = $db->prepare("SELECT t.rules as rules
    FROM objects AS o,objecttypes AS t
    WHERE o.type = t.id AND o.location = :locationId AND t.rules LIKE '%addcapacity:%'");
  $stm->bindInt("locationId", $id);
  $stm->execute();
  foreach ($stm->fetchScalars() as $rule) {
    $rulesArray = Parser::rulesToArray($rule);
    if (array_key_exists('addcapacity', $rulesArray)) {
      $maxweight += $rulesArray['addcapacity'];
    }
  }

  return $maxweight;
}
