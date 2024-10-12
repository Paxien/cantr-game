<?php
include_once("func.getdirection.inc.php");
require_once("func.calcweight.inc.php");

// SANTITIZE INPUT
$character = HTTPContext::getInteger('character');

$smarty = new CantrSmarty;

$v_link_used = $player != $char->getPlayer();

if ($v_link_used) {
  $playerInfo = Request::getInstance()->getPlayer();
  if (!$playerInfo->hasAccessTo(AccessConstants::CONTROL_OTHER_CHARACTERS)) {
    CError::throwRedirectTag("player", "error_you_are_not_allowed_to_view_events_of_other_players");
  }
}

$smarty->assign("maplink", "map");

if ($player == $char->getPlayer()) {
  $char->updateLastDateAndTime(GameDate::NOW());
  $char->saveInDb();
}

$char_loc = new char_location($char->getId());

$location_info = $char_loc->location_info;


$smarty->assign("character", $character);
$smarty->assign("location", $char->getLocation());
$smarty->assign("inlocation", $char->getLocation() != 0);
$smarty->assign("loctype", $location_info->type);
$smarty->assign("locregion", $location_info->region);
$smarty->assign("project", $char->getProject());

$charInfo = new CharacterInfoView($char);
$charInfo->show();

$db = Db::get();

$objects = CObject::inInventoryOf($char)->hasProperty("AlterViewRange")->findAll();

$increasedViewRange = Pipe::from($objects)->map(function(CObject $object) {
  return $object->getProperty("AlterViewRange");
})->maximum();

$BASIC_VIEW_RANGE = 100;
$fov = max($increasedViewRange, $BASIC_VIEW_RANGE);

if ($char->getLocation() != 0) {

  /* ***************** DESCRIPTION ********************* */

  if (in_array($location_info->type, [
    LocationConstants::TYPE_VEHICLE,
    LocationConstants::TYPE_SAILING_SHIP,
    LocationConstants::TYPE_BUILDING
  ])) {

    //check that our vehicle is traveling (roads)
    $stm = $db->prepare("SELECT ( COUNT(*) > 0 ) FROM travels t, locations l
      WHERE l.id = :locationId AND t.type = l.area AND t.person = l.id");
    $stm->bindInt("locationId", $location_info->id);
    $outdoor = $stm->executeScalar();

    if (!$outdoor) {
      //check that our vehicle isn't inside another vehicle/building, but we must ignore
      //harbours and ship (to give visibility to ships docked to harbour or another ship)
    $stm = $db->prepare("SELECT (l.type = :outside OR ot.objectcategory = :categoryHarbours
    OR ot.objectcategory = :categoryShips) FROM locations l, objecttypes ot WHERE l.id = :region AND l.area = ot.id");
      $stm->bindInt("outside", LocationConstants::TYPE_OUTSIDE);
      $stm->bindInt("categoryHarbours", ObjectConstants::OBJCAT_HARBOURS);
      $stm->bindInt("categoryShips", ObjectConstants::OBJCAT_SHIPS);
      $stm->bindInt("region", $location_info->region);
      $outdoor = $stm->executeScalar();
    }
    $smarty->assign("outdoor", $outdoor);

    if (($location_info->type == LocationConstants::TYPE_SAILING_SHIP) ||
      ($outdoor && (
          ($location_info->type == LocationConstants::TYPE_VEHICLE) ||
          (($location_info->type == LocationConstants::TYPE_BUILDING) && Window::hasOpenWindow($location_info->id))
        ))
    ) {
      $weather = Weather::loadByPos(intval($location_info->x), intval($location_info->y));
      $weatherView = new WeatherView($weather);
      $smarty->assign("weatherData", $weatherView->getText());
      $smarty->assign("season", $weather->getSeasonName());
    }

    $stm = $db->prepare("SELECT rules FROM objecttypes WHERE id = :type LIMIT 1");
    $stm->bindInt("type", $location_info->area);
    $loc_rules = $stm->executeScalar();

    $smarty->assign("locname", TagBuilder::forLocation($char->getLocation())->allowHtml(true)->build()->interpret());

    if ($location_info->type == LocationConstants::TYPE_BUILDING) { // building description
      $custom_description_edit = Descriptions::getDescription($char->getLocation(), Descriptions::TYPE_BUILDING);
      $custom_description_id = Descriptions::getDescriptionId($char->getLocation(), Descriptions::TYPE_BUILDING);
      $custom_description = str_replace("\n", "<br />", $custom_description_edit);
      $smarty->assign("is_custom_description_allowed", true);
      $smarty->assign("custom_description", $custom_description);
      $smarty->assign("custom_description_id", $custom_description_id);
      $smarty->assign("custom_description_edit", $custom_description_edit);
    }

    /* FUEL LEVEL METER */
    $loc_rules = Parser::rulesToArray($loc_rules);
    if ($loc_rules['engine']) {
      $engines = explode(",", $loc_rules['engine']);
      $engineIds = ObjectTypeFinder::any()->names($engines)->findIds();

      $engine = CObject::locatedIn($location_info->id)->types($engineIds)->find();

      if ($engine != null && $engine->hasProperty("Storage")) {
        $engineStorage = new Storage($engine);

        $proportion = 1 - $engineStorage->getSpaceLeft() / $engineStorage->getCapacity();
        $smarty->assign("isfuel", true);
        $smarty->assign("fuel", round($proportion, 2));
        $smarty->assign("fuel100", TextFormat::getPercentFromFraction($proportion));
        $smarty->assign("fuelwt", $engine->getWeight() - $engineStorage->getBaseWeight());
      }
    }
    if ($loc_rules['maxweight']) {
      try {
        $loc = Location::loadById($location_info->id);

        $proportion1 = max(0, min($loc->getTotalWeight() / $loc->getMaxWeight(), 1));
        $smarty->assign("show_capacity", true);
        $smarty->assign("capacity", round($proportion1, 2));
        $smarty->assign("capacity100", TextFormat::getPercentFromFraction($proportion1));
      } catch (InvalidArgumentException $e) {
        Logger::getLogger("page.description", "no loc " . $location_info->id);
      }
    }

    if ($loc_rules['maxpeople']) {
      try {
        $loc = Location::loadById($location_info->id);
        $proportion2 = max(0, min($loc->getCharacterCount() / $loc->getMaxCharacters(), 1));

        $smarty->assign("show_crowding", true);
        $smarty->assign("crowding", round($proportion2, 2));
        $smarty->assign("crowding100", $loc->getCharacterCount() . "/" . $loc->getMaxCharacters());
      } catch (InvalidArgumentException $e) {
        Logger::getLogger("page.description", "no loc " . $location_info->id);
      }
    }
  } else {
    $smarty->assign("locname", $char_loc->nametag);
  }
  $areaName = Location::loadById($location_info->id)->getTypeUniqueName();
  $smarty->assign("areaname", $areaName);

  if ($location_info->type == LocationConstants::TYPE_OUTSIDE) {
    $digging_slots_used = Location::getUsedDiggingSlots($location_info->id);
    $smarty->assign("TOTAL", $location_info->digging_slots);
    $smarty->assign("USED", $digging_slots_used);
  }

  $smarty->assign("borderslake", $location_info->borders_lake);
  $smarty->assign("borderssea", $location_info->borders_sea);

  if ($outdoor && $location_info->type == LocationConstants::TYPE_VEHICLE) {

    $pos = $char->getPos();

    $x = $pos["x"];
    $y = $pos["y"];
  }

  if (in_array($location_info->type, [LocationConstants::TYPE_OUTSIDE, LocationConstants::TYPE_SUBFIELD])) {
    $smarty->assign("doraws", true);
    $stm = $db->prepare("SELECT rawtypes.action, rawtypes.name, raws.type FROM raws
      LEFT JOIN rawtypes ON rawtypes.id = raws.type WHERE raws.location = :locationId");
    $stm->bindInt("locationId", $char->getLocation());
    $stm->execute();

    $raws = $stm->fetchAll();
    foreach ($raws as $raw) {
      $raw->name = "<CANTR REPLACE NAME=" . TagUtil::getRawTagByName($raw->name) . ">";
    }
    $smarty->assign("raws", $raws);
  }

  $location = Location::loadById($location_info->id);
  $locationRepair = new LocationRepairManager($location, new ResourceRequirementsConsumer());
  $smarty->assign("isRepairable", $location->isRepairable());

  if ($location_info->type == LocationConstants::TYPE_SAILING_SHIP) {

    $sailing = Sailing::loadByVesselId($char->getLocation());
    $watertype = $sailing->getShip()->getAreaType();
    if ($sailing->getSpeedPercent() == 0) {
      $smarty->assign("TYPE", $watertype);
    } elseif ($sailing->isDocking()) {
      $distance = $sailing->getDistanceToDockingTarget();
      $turnsToDock = $sailing->getTurnsToDock();
      $hoursToDock = ceil($turnsToDock / (SailingConstants::TURNS_PER_DAY / GameDateConstants::HOURS_PER_DAY));
      $smarty->assign("DOCKING", $hoursToDock);
      $smarty->assign("docking", true);

      $smarty->assign("canCancelDocking", $hoursToDock > 1);
    } else {
      $smarty->assign("SPEED", round($sailing->getSpeedPercent()));
      $smarty->assign("DIRECTION", $sailing->getDirection());
      $smarty->assign("TYPE", $watertype);
    }
    $smarty->assign("sailingid", $sailing->getId());
  }
  $pos = $char->getPos();
} else {

  /* ***************** DESCRIPTION FOR TRAVELLERS ********************* */

  $pos = $char->getPos();

  $x = $pos["x"];
  $y = $pos["y"];
}

if (!$location_info->id || Location::loadById($location_info->id)->isMapVisibilityEnabled()) {
  $visibleMarkersFinder = new VisibleMarkersFinder($pos["x"], $pos["y"], $char);
  $isOnSea = $location_info->id && Location::loadById($location_info->id)->getRoot()
      ->getType() == LocationConstants::TYPE_SAILING_SHIP;

  $visibleFromDistance = $visibleMarkersFinder->getThingsVisibleFromDistance(
    $fov / 100, $location_info->id ? $location_info->id : 0, $char, $isOnSea);

  $dockableLocIds = [];
  if ($sailing && !$sailing->isDocking() && (count($sailing->getDockable()) > 0)) {
    $dockableLocIds = $sailing->getDockable();
  }

  $markers = Pipe::from($visibleFromDistance)
    ->map(function(VisibleMarker $visibleMarker) use ($dockableLocIds, $char) {
      $markerEntity = $visibleMarker->getMarker();
      $canBeDockedTo = ($markerEntity instanceof Location) && in_array($markerEntity->getId(), $dockableLocIds);

      $additionalSigns = "";
      $visibleObjectsOnDeck = "";
      if ($markerEntity instanceof Location) {
        $visibleMarkerPresenter = new VisibleMarkerPresenter(Db::get());
        $visibleObjectsOnDeck = $visibleMarkerPresenter->printVisibleObjects($markerEntity->getVisibleObjects(false));
        if ($visibleMarker->canSeeDetailedName()) { // show additional signs only when showing primary sign
          $additionalSigns = $visibleMarkerPresenter->printShipSigns($markerEntity, $char);
        }
      }

      return [
        "id" => $markerEntity->getId(),
        "canBeDockedTo" => $canBeDockedTo,
        "text" => $visibleMarker->getText(),
        "objectsOnDeck" => $visibleObjectsOnDeck,
        "additionalSigns" => $additionalSigns,
      ];
    })->toArray();

  $smarty->assign("visibleFromDistance", $markers);
}

/* *************** SIGNAL FIRES FOR FOOT TRAVELLERS AND OUTSIDE LOCATIONS ******* */
if (($char->getLocation() == 0) or ($location_info->type == LocationConstants::TYPE_OUTSIDE)) {

  if ($location_info->id) {
    $posX = $location_info->x;
    $posY = $location_info->y;
  } else {
    $posX = $x;
    $posY = $y;
  }

  $weather = Weather::loadByPos($posX, $posY);
  $weatherView = new WeatherView($weather);
  $harvestEfficiency = $weather->getAgriculturalConditions()->getDescriptiveHarvestEfficiency();
  $smarty->assign("weatherData", $weatherView->getText());
  $smarty->assign("season", $weather->getSeasonName());
  $smarty->assign("harvestEfficiency", $harvestEfficiency);
}

/**
 * @param $connection
 * @param $location
 * @param $location_info
 * @param $l
 * @param $accessibleParts String[] names of parts that can be visible from this location
 * @return stdClass
 */
function getConnectionInfo(Connection $connection, $location, $location_info, $l, $accessibleParts, Db $db)
{
  $road = new stdClass();
  $road->id = $connection->getId();

  $anyPartCanBeImproved = count($connection->getPotentialImprovements()) - count($connection->getOngoingImprovements()) > 0;
  $anyPartCanBeRepaired = Pipe::from($connection->getParts())->filter(function(ConnectionPart $part) {
    return $part->getDeterioration() > 0;
  })->count();
  $anyPartCanBeDestroyed = Pipe::from($connection->getParts())->filter(function(ConnectionPart $part) {
    return $part->getType()->isDestroyable();
  })->count();

  $anyPartCanBeManipulated = $anyPartCanBeImproved || $anyPartCanBeRepaired || $anyPartCanBeDestroyed;

  $road->canImprove = $location == $location_info->id && $anyPartCanBeManipulated;

  if ($location_info->type == LocationConstants::TYPE_VEHICLE) {
    $vehicle_type = $location_info->area;
  } else {
    $vehicle_type = 'walking';
  }

  $road->endloc = $connection->getOppositeLocation($location);
  $road->accepted = $connection->canBeMovedOn($vehicle_type, $accessibleParts);

  $deteriorationView = (new DeteriorationViewFactory(DeteriorationViewFactory::VISIBLE_WHEN_NOT_ZERO))->language($l);

  $types = Pipe::from($connection->getParts())
    ->filter(function(ConnectionPart $part) use ($accessibleParts) {
      return in_array($part->getType()->getName(), $accessibleParts);
    })->map(function(ConnectionPart $part) use ($deteriorationView, $l, $db) {
      $roadTag = "road_" . $part->getType()->getName();
      $roadName = TagBuilder::forTag($roadTag)->build()->interpret();
      $stm = $db->prepare("SELECT grammar FROM texts WHERE name = :name AND language = :language LIMIT 1");
      $stm->bindStr("name", $roadTag);
      $stm->bindInt("language", $l);
      $roadGrammar = $stm->executeScalar();
      return $deteriorationView->show($part->getDeterioration(), $roadGrammar, $roadName);
    })->toArray();

  $road->type = implode(", ", $types);

  $road->direction = getdirectionname($connection->getDirectionFromLocation($location));
  return $road;
}

if ($location_info->id != 0) {

  /* ***************** EXITS ********************* */

  if (($location_info->type != LocationConstants::TYPE_OUTSIDE) and ($location_info->region != 0)) {
    $smarty->assign("doorid", $location_info->region);
  }

  if ($location_info->type == LocationConstants::TYPE_BUILDING) {

    $isWindowOpen = Window::hasOpenWindow($location_info->id);

    $smarty->assign("iswindow", CObject::locatedIn($location_info->id)->type(Window::OBJECTTYPE_WINDOW_ID)->exists());
    $smarty->assign("iswindowopen", $isWindowOpen);
  }

  $locationId = $location_info->id;
  if ($location_info->type == LocationConstants::TYPE_VEHICLE) {
    $locationId = $location_info->region;
  }

  $roads = [];
  if ($locationId != 0) {
    $location = Location::loadById($locationId);
    $accessibleRoadTypesProp = $location->getProperty("AccessibleRoadTypes");
    if ($accessibleRoadTypesProp === null) {
      $accessibleRoadTypesProp = [];
    }
    $rootLoc = $location->getRoot();
    foreach (ConnectionFinder::connectionsAdjacentTo($rootLoc) as $connection) {
      $accessiblePartsOfConnection = array_intersect($connection->getTypeNames(), $accessibleRoadTypesProp);
      if (!empty($accessiblePartsOfConnection)) {
        $roads[] = getConnectionInfo($connection, $rootLoc->getId(), $location_info, $l, $accessiblePartsOfConnection, $db);
      }
    }
  }

  $smarty->assign("roads", $roads);

  if ($char_loc->isvehicle) {
    $isboat = in_array($char_loc->typeid, Location::getShipTypeArray());

    $smarty->assign("isboat", $isboat);
    if ($isboat) {

      $smarty->assign("docking", $sailing && $sailing->isDocking());

      if ($location_info->type == LocationConstants::TYPE_SAILING_SHIP) {

        if ($sailing && !$sailing->isDocking() && (count($sailing->getDockable()) > 0)) {
          $docklocs = [];
          foreach ($sailing->getDockable() as $val) {

            $dockingTarget = Location::loadById($val);

            $dockloc = new stdClass();
            $dockloc->id = $dockingTarget->getId();
            $angle = Measure::direction($sailing->getX(), $sailing->getY(), $dockingTarget->getX(), $dockingTarget->getY());
            $dockloc->direction = getdirectionname($angle);
            $docklocs [] = clone $dockloc;
          }

          $smarty->assign("docklocs", $docklocs);

        }
      }
    }
  }

  $locationsToBeDockableTo = LocationFinder::any()
    ->region($location_info->id)
    ->type(LocationConstants::TYPE_BUILDING)
    ->findIds();

  array_push($locationsToBeDockableTo, $location_info->id);

  $stm = $db->prepareWithIntList("SELECT vessel FROM sailing WHERE docking_target IN (:ids)",
    ["ids" => $locationsToBeDockableTo]);
  $stm->execute();
  $smarty->assign("dockingShips", $stm->fetchScalars());
}

JsTranslations::getManager()->addTags(["page_alter_sailing_1"]);

$smarty->displayLang("page.description.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
