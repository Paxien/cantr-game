<?php

$playerInfo = Request::getInstance()->getPlayer();
$playersList = $_REQUEST['player_ids'];

if (!$playerInfo->hasAccessTo(AccessConstants::SEE_TRAVELS_TIMELINE)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}
$smarty = new CantrSmarty();

$displayData = !empty($playersList);


$smarty->assign("rawPlayersList", $playersList);
$smarty->assign("displayData", $displayData);
function getTravelHistory(Db $db, $charactersList)
{
  $stm = $db->prepareWithIntList("SELECT * FROM travelhistory WHERE person IN (:charactersList) ORDER BY id", [
    "charactersList" => $charactersList,
  ]);

  $stm->execute();
  $allRows = $stm->fetchAll();
  return $allRows;
}

/**
 * @param $db Db
 * @param $rootLocIds
 * @param $vehIds
 * @return array
 */
function prefetchLocInfoById(Db $db, $rootLocIds, $vehIds)
{
  $locInfoById = [];

  $stm = $db->prepareWithIntList("SELECT id, name, usersname FROM oldlocnames WHERE id IN (:locationIds)", [
    "locationIds" => $rootLocIds
  ]);
  $stm->execute();
  $rootLocNames = $stm->fetchAll();
  foreach ($rootLocNames as $vehData) {
    if ($vehData->usersname) {
      $locInfoById[$vehData->id] = [
        "name" => $vehData->usersname,
        "type" => "outside",
      ];
    } else { // fallback in case usersname is not defined
      $locInfoById[$vehData->id] = [
        "name" => $vehData->name,
        "type" => "outside",
      ];
    }
  }

  $stm = $db->prepareWithIntList("SELECT l.id, l.name, ot.unique_name AS type_name
    FROM locations l
    LEFT JOIN objecttypes ot ON ot.id = l.area WHERE l.id IN (:locationIds)", [
    "locationIds" => $vehIds,
  ]);
  $stm->execute();
  $vehNames = $stm->fetchAll();
  foreach ($vehNames as $vehData) {
    if (StringUtil::contains($vehData->name, "<CANTR")) {
      $characterInLocationName = preg_match("<CANTR CHARNAME ID=(\d+)>", $vehData->name, $matches);
      if ($characterInLocationName) {
        $animalOwnerId = $matches[1];
        $vehData->name = TagBuilder::forText($vehData->name)
          ->observedBy($animalOwnerId)->allowHtml(false)
          ->language(LanguageConstants::ENGLISH)->build()
          ->interpret();
      }
    }
    $locInfoById[$vehData->id] = [
      "name" => $vehData->name,
      "type" => $vehData->type_name,
    ];
  }
  $locInfoById[0] = [
    "name" => "** by foot **",
    "type" => "none",
  ];
  return $locInfoById;
}

function prefetchCharacterInfoById(Db $db, $characterIds)
{
  $stm = $db->prepareWithIntList("SELECT c.id, c.name, c.player, c.status, p.firstname, p.lastname
      FROM chars c 
      INNER JOIN players p ON p.id = c.player
    WHERE c.id IN (:characterIds)", [
    "characterIds" => $characterIds,
  ]);
  $stm->execute();
  $charactersList = [];
  foreach ($stm->fetchAll() as $characterData) {
    $charactersList[$characterData->id] = [
      "name" => $characterData->name,
      "id" => $characterData->id,
      "playerId" => $characterData->player,
      "playerName" => $characterData->firstname . " " . $characterData->lastname,
      "status" => $characterData->status >= CharacterConstants::CHAR_DECEASED ? "DEAD" : "ALIVE",
    ];
  }
  return $charactersList;
}

if ($displayData) {
  $playersList = str_replace(" ", "", $playersList); // remove spaced
  $playersList = explode(",", $playersList);
  if (!Validation::isPositiveIntArray($playersList)) {
    CError::throwRedirect("travels_timeline", "Invalid format of characters list. It should be a comma-separated list of character IDs");
  }

  $db = Db::get();

  $stm = $db->prepareWithIntList("SELECT id FROM chars WHERE player IN (:playerIds)", [
    "playerIds" => $playersList,
  ]);
  $stm->execute();
  $charactersList = $stm->fetchScalars();

  if (empty($charactersList)) {
    CError::throwRedirect("travels_timeline", "The specified players have no characters");
  }

  $allRows = getTravelHistory($db, $charactersList);

  $rootLocIds = [];
  $vehIds = [];
  $characterIds = [];
  $resultRows = [];
  foreach ($allRows as $row) {
    $characterId = $row->person;
    $resultRows[] = [
      "id" => $row->id,
      "person" => $characterId,
      "location" => $row->location,
      "day" => $row->day,
      "hour" => $row->hour,
      "vehicle" => $row->vehicle,
      "type" => $row->arrival ? "ARRIVAL" : "DEPARTURE",
    ];
    $rootLocIds[] = $row->location;
    $vehIds[] = $row->vehicle;
    $characterIds[] = $row->person;
  }

  $rootLocIds = array_unique($rootLocIds);
  $vehIds = array_unique($vehIds);

  $locInfoById = prefetchLocInfoById($db, $rootLocIds, $vehIds);
  $characterInfoById = prefetchCharacterInfoById($db, $characterIds);

  function formatCharacterCell($characterId, $characterNameById)
  {
    return $characterNameById[$characterId];
  }

  function formatLocationNameCell($locInfo)
  {
    $locName = $locInfo["name"];
    return mb_substr($locName, 0, min(24, mb_strlen($locName)));
  }

  function formatVehicleCell($locInfo)
  {
    return $locInfo["name"];
  }

  function formatVehicleTypeCell($locInfo)
  {
    return $locInfo["type"];
  }

  function formatDateCell($day, $hour)
  {
    return $day . "-" . $hour;
  }

  $allRows = null;
  $formattedTabularData = [
    [
      "id",
      "charName",
      "charId",
      "playerName",
      "playerId",
      "locationName",
      "locationId",
      "type",
      "date",
      "vehicle",
      "vehicleId",
      "vehicleType",
    ]
  ];


  $shipTypeIds = Location::getShipTypeArray();
  $stm = $db->prepareWithIntList("SELECT unique_name FROM objecttypes WHERE id IN (:shipTypes)", [
    "shipTypes" => $shipTypeIds,
  ]);
  $stm->execute();
  $shipUniqueNames = $stm->fetchScalars();

  $vehicleNameByType = [];
  foreach ($resultRows as $row) {
    $charInfo = $characterInfoById[$row["person"]];
    $locInfo = $locInfoById[$row["location"]];
    $vehInfo = $locInfoById[$row["vehicle"]];

    // between these days there was a bug that for recorded land travels vehicle type was saved indead of vehicle ID
    // if the type of connected vehicle is recognized as a valid ship type then it's most likely a correct row
    $affectedByTravelHistoryBug = $row["day"] >= 4892 && $row["day"] <= 5164
      && !in_array($vehInfo["type"], $shipUniqueNames);
    if ($affectedByTravelHistoryBug) {
      if (!array_key_exists($row["vehicle"], $vehicleNameByType)) {
        $stm = $db->prepare("SELECT unique_name FROM objecttypes WHERE id = :vehicleType");
        $stm->bindInt("vehicleType", $row["vehicle"]);
        $vehicleNameByType[$row["vehicle"]] = $stm->executeScalar();
      }
      $vehInfo["type"] = $vehicleNameByType[$row["vehicle"]];
    }

    $formattedTabularData[] = [
      intval($row["id"]),
      $charInfo["name"],
      intval($charInfo["id"]),
      $charInfo["playerName"],
      intval($charInfo["playerId"]),
      formatLocationNameCell($locInfo),
      intval($row["location"]),
      $row["type"],
      formatDateCell($row["day"], $row["hour"]),
      (!$affectedByTravelHistoryBug ? formatVehicleCell($vehInfo) : "UKNOWN"),
      intval($row["vehicle"]),
      ($affectedByTravelHistoryBug ? $vehInfo["type"] : formatVehicleTypeCell($vehInfo)),
    ];
  }

  $smarty->assign("concernedCharacters", array_values($characterInfoById));
  $smarty->assign("tableSize", count($formattedTabularData));
  $smarty->assign("tableData", json_encode($formattedTabularData));
}

$smarty->displayLang("pd_tools/page.travels_timeline.tpl", $lang_abr);

