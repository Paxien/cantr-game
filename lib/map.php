<?php

$objects = CObject::inInventoryOf($char)->hasProperty("AlterViewRange")->findAll();

$increasedViewRange = Pipe::from($objects)->map(function($object) {
  return $object->getProperty("AlterViewRange");
})->maximum();

$BASIC_VIEW_RANGE = 100;

$fov = max($increasedViewRange, $BASIC_VIEW_RANGE);

$db = Db::get();
$stm = $db->prepare("SELECT type, region FROM locations WHERE id = :id");
$stm->bindInt("id", $char->getLocation());
$stm->execute();
list ($locType, $locRegion) = $stm->fetch(PDO::FETCH_NUM);

  $allowedType = in_array($locType, array(LocationConstants::TYPE_OUTSIDE,
  LocationConstants::TYPE_SUBFIELD, LocationConstants::TYPE_SAILING_SHIP));
$locTypeAllowed = $char->isTravelling() || $allowedType;

if (!$locTypeAllowed && $locType == LocationConstants::TYPE_VEHICLE) {
  $stm = $db->prepare("SELECT (l.type = :outside OR ot.objectcategory = :categoryHarbours
    OR ot.objectcategory = :categoryShips) FROM locations l, objecttypes ot WHERE l.id = :region AND l.area = ot.id");
  $stm->bindInt("outside", LocationConstants::TYPE_OUTSIDE);
  $stm->bindInt("categoryHarbours", ObjectConstants::OBJCAT_HARBOURS);
  $stm->bindInt("categoryShips", ObjectConstants::OBJCAT_SHIPS);
  $stm->bindInt("region", $locRegion);
  $locTypeAllowed = $stm->executeScalar();
}

$isDegree = $locType == LocationConstants::TYPE_SAILING_SHIP;
$map = new MapView($isDegree, (PlayerSettings::getInstance($player)->get(PlayerSettings::COMPASS) == 0), $char->getLanguage());

$pos = $char->getPos();
$map->show($pos["x"], $pos["y"], $fov);
