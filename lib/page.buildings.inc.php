<?php

$smarty = new CantrSmarty;

$v_link_used = $player != $char->getPlayer();

if ($v_link_used) {
  $playerInfo = Request::getInstance()->getPlayer();
  if (!$playerInfo->hasAccessTo(AccessConstants::CONTROL_OTHER_CHARACTERS)) {
    CError::throwRedirectTag("player", "error_you_are_not_allowed_to_view_events_of_other_players");
  }
}


function getAdditionalSignsForLocation($location, Character $char, Db $db)
{
  $locationNaming = new LocationNaming(Location::loadById($location->id), $db);
  $allNames = $locationNaming->getAllNames($char);
  array_shift($allNames);
  $location->signs = $allNames;

  return $location;
}

if (!$v_link_used) {
  $char->updateLastDateAndTime(GameDate::NOW());
  $char->saveInDb();
}

$db = Db::get();

$charInfo = new CharacterInfoView($char);
$charInfo->show();

if ($char->getLocation() != 0) {

  /* ***************** VEHICLES ********************* */

  $shipTypes = Location::getShipTypeArray();

  $allVehicleTypes = array();
  $stm = $db->query("SELECT vehicles FROM connecttypes");
  $lists = $stm->fetchScalars();

  foreach ($lists as $vehList) {
    $toAdd = array_filter(explode(",", $vehList), "Validation::isPositiveInt");
    $allVehicleTypes = array_merge($allVehicleTypes, $toAdd);
  }
  $allVehicleTypes = array_values(array_unique($allVehicleTypes));

  $stm = $db->prepare(
    "SELECT l.id AS id, l.deterioration AS det, ot.unique_name AS typename,
      ot.id AS type_id, ad.id AS animal, ad.fullness, ad.loyal_to,
    (SELECT t.grammar FROM texts t
      WHERE t.name = CONCAT('item_', ot.unique_name, '_b') AND t.language IN (:language, 1)
      ORDER BY t.language DESC LIMIT 1) AS gender
    FROM locations l
    INNER JOIN objecttypes ot ON ot.id = l.area
    LEFT JOIN animal_domesticated ad ON ad.from_location = l.id
    WHERE l.type = 3 AND l.region = :locationId
    ORDER BY l.name");
  $stm->bindInt("language", $l);
  $stm->bindInt("locationId", $char->getLocation());
  $stm->execute();

  $ships = array();
  $animals = array();
  $landVehicles = array();
  $constructions = array();
  $deteriorationView = new DeteriorationViewFactory(DeteriorationViewFactory::VISIBLE_WHEN_NOT_ZERO);
  foreach ($stm->fetchAll() as $vehicle) {
    // I think this is not exactly as get_deter_descr was intended ... shouldn't
    // it return the adjective + noun, rather than just adjective?
    $vehicle->det = $deteriorationView->show($vehicle->det, $vehicle->gender);

    $visibleMarkerPresenter = new VisibleMarkerPresenter($db);
    $visibleLocation = Location::loadById($vehicle->id);
    $vehicle->sails = $visibleMarkerPresenter->printVisibleObjects($visibleLocation->getVisibleObjects());
    $vehicle = getAdditionalSignsForLocation($vehicle, $char, $db);
    $vehicle->isDisassemblable = $visibleLocation->isDisassemblable();

    if (in_array($vehicle->type_id, $shipTypes)) {
      $ships[] = $vehicle;
    } elseif ($vehicle->animal) {
      $vehicle->det = Animal::getFedTagFromValue($vehicle->fullness, $vehicle->gender);
      if ($vehicle->loyal_to == $char->getId()) {
        $vehicle->can_unsaddle = true;
      }
      $animals[] = $vehicle;
    } elseif (in_array($vehicle->type_id, $allVehicleTypes)) {
      $landVehicles[] = $vehicle;
    } else {
      $constructions[] = $vehicle;
    }
  }

  $vehicles = array(
    "land_vehicles" => $landVehicles,
    "animals" => $animals,
    "ships" => $ships,
    "constructions" => $constructions,
  );


  $smarty->assign("vehicles", $vehicles);

  /* ***************** BUILDINGS / ROOMS ********************* */

  $stm = $db->prepare("SELECT MAX(local_number) FROM locations WHERE type = 2 AND region = :locationId");
  $stm->bindInt("locationId", $char->getLocation());
  $max = $stm->executeScalar();

  $stm = $db->prepare(
    "SELECT l.id AS id, l.type AS type, ot.unique_name AS typename,
       l.local_number AS ln, l.deterioration AS det,
    (SELECT t.grammar FROM texts t
      WHERE t.name = CONCAT('item_', ot.unique_name, '_b') AND t.language IN (:language, 1)
      ORDER BY language DESC LIMIT 1
    ) AS gender,
    (SELECT specifics FROM objects o
      INNER JOIN obj_properties op ON op.objecttype_id = o.type AND op.property_type = 'EnableSeeingOutside'
     WHERE o.location = l.id LIMIT 1) AS window
    FROM locations l
      INNER JOIN objecttypes ot ON ot.id = l.area 
    WHERE l.type = 2 AND l.region = :locationId
    ORDER BY l.id");
  $stm->bindInt("language", $l);
  $stm->bindInt("locationId", $char->getLocation());
  $stm->execute();

  $deteriorationView = new DeteriorationViewFactory(DeteriorationViewFactory::VISIBLE_WHEN_NOT_ZERO);
  foreach ($stm->fetchAll() as $building) {
    if ($building->ln > 0)
      $building->ncount = $building->ln;
    else {
      // TODO viewing buildings page shouldn't alter local numbers
      $max++;
      $building->ncount = $max;
      $stm = $db->prepare("UPDATE locations SET local_number = :localNumber WHERE id = :id LIMIT 1");
      $stm->bindInt("localNumber", $building->ncount);
      $stm->bindInt("id", $building->id);
      $stm->execute();
    }

    $buildingObj = Location::loadById($building->id);
    $building->isDestroyable = $buildingObj->isDestroyable();
    $building->isRepairable = $buildingObj->getDeterioration() >= 0;
    $building->det = $deteriorationView->show($building->det, $building->gender);
    $building = getAdditionalSignsForLocation($building, $char, $db);

    $building->haswindow = ($building->window != null);
    $isWindowOpen = (strpos($building->window, "open") !== false); // "open" == window is open, "closed" == not
    $building->hasopenwindow = $isWindowOpen;

    $buildings [] = $building;
  }
  // Is it possible here to sort $buildings on the ncount field? --> Would look a lot better once buildings can be destroyed ;)

  $smarty->assign("buildings", $buildings);
}

$smarty->displayLang("page.buildings.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();