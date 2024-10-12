<?php

$gameDate = GameDate::NOW();
$laterDayMin = HTTPContext::getInteger('later_day_min', $gameDate->getDay() - 30);
$laterDayMax = HTTPContext::getInteger('later_day_max', $gameDate->getDay());

$db = Db::get();

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::SEE_INDIRECT_OBJECT_TRANSFERS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$stm = $db->prepare("
  SELECT t_later.id, c_later.player,
    c_earlier.name AS from_char_name, c_earlier.id AS from_char_id,
    c_later.name AS to_char_name, c_later.id AS to_char_id,
    t_later.object_id,
    t_earlier.day AS from_day, t_earlier.hour AS from_hour,
    t_later.day AS to_day, t_earlier.hour AS to_hour,
    (SELECT unique_name
      FROM objecttypes ot
      INNER JOIN objects o ON ot.id = o.type
      WHERE o.id = t_later.object_id
    ) AS object_name,
    (SELECT o.type
      FROM objects o
      WHERE o.id = t_later.object_id
    ) AS object_type,
    (SELECT CONCAT(firstname, ' ', lastname) 
      FROM players p
      WHERE p.id = c_later.player
    ) AS player_name
  FROM recorded_translocations t_earlier
    INNER JOIN recorded_translocations t_later ON t_later.object_id = t_earlier.object_id
    INNER JOIN chars c_later ON c_later.id = t_later.to_character
    INNER JOIN chars c_earlier ON c_earlier.id = t_earlier.from_character
  WHERE t_later.id > t_earlier.id
    AND t_later.to_character > 0 AND t_earlier.from_character > 0
    AND t_later.to_character != t_earlier.from_character -- other characters
    AND c_later.player = c_earlier.player -- of the same player
    AND t_later.day BETWEEN :laterDayMin AND :laterDayMax
  ORDER BY t_later.id
  LIMIT 10000 -- ugly hack, this LIMIT modifies query plan for SELECT in mysql 5.5 so the query is not blocked forever
");

$stm->bindInt("laterDayMin", $laterDayMin);
$stm->bindInt("laterDayMax", $laterDayMax);
$stm->execute();

$results = $stm->fetchAll();

$formattedTabularData = [
  [
    "transferId",
    "fromChar",
    "toChar",
    "playerName",
    "playerId",
    "earlierDate",
    "laterDate",
    "daysDiff",
    "objectId",
    "objectType",
  ]
];

function strippedName($name)
{
  return mb_substr($name, 0, 15) . (mb_strlen($name) > 15 ? "..." : "");
}

function showLockForKey($keyId, Db $db)
{
  $stm = $db->prepare("SELECT olock.* FROM objects okey 
    INNER JOIN objects olock ON olock.id = okey.specifics
  WHERE okey.id = :keyId");
  $stm->bindInt("keyId", $keyId);
  $stm->execute();
  $lockInfo = $stm->fetchObject();
  if ($lockInfo->attached > 0) { // storage lock
    return " to storage " . TagBuilder::forObject($lockInfo->attached)->build()->interpret() . " ($lockInfo->attached)";
  } elseif ($lockInfo->location) {
    return " to location " . TagBuilder::forLocation($lockInfo->location)->build()->interpret() . " ($lockInfo->location)";
  } else {
    return " to unknown";
  }
}

foreach ($results as $row) {
  $formattedTabularData[] = [
    $row->id,
    strippedName($row->from_char_name) . " (" . $row->from_char_id . ")",
    strippedName($row->to_char_name) . " (" . $row->to_char_id . ")",
    $row->player_name,
    "<a href='index.php?page=infoplayer&player_id=$row->player'>$row->player</a>",
    $row->from_day . "-" . $row->from_hour,
    $row->to_day . "-" . $row->to_hour,
    $row->to_day - $row->from_day,
    $row->object_id,
    $row->object_name . ($row->object_type == ObjectConstants::TYPE_KEY ? showLockForKey($row->object_id, $db) : ""),
  ];
}

$smarty = new CantrSmarty();
$smarty->assign("tableSize", count($results));
$smarty->assign("tableData", json_encode($formattedTabularData));
$smarty->assign("laterDayMin", $laterDayMin);
$smarty->assign("laterDayMax", $laterDayMax);
$smarty->displayLang("pd_tools/page.indirect_object_transfers.tpl", $lang_abr);
