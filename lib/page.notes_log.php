<?php
// TODO query complicated to use prepared statements
$search_from_day = HTTPContext::getInteger('search_from_day');
$search_from_hour = HTTPContext::getInteger('search_from_hour');
$search_to_day = HTTPContext::getInteger('search_to_day');
$search_to_hour = HTTPContext::getInteger('search_to_hour');
$search_note_title = HTTPContext::getRawString("search_note_title");
$search_char_id = HTTPContext::getInteger('search_char_id');
$search_note_id = HTTPContext::getInteger('search_note_id');
$search_object_id = HTTPContext::getInteger('search_object_id');

$ptab = HTTPContext::getInteger('ptab');

$plr = Request::getInstance()->getPlayer();
if ($plr->hasAccessTo(AccessConstants::NOTES_MANIPULATION)) {
  $db = Db::get();

  if ($ptab < 1) {
    $ptab = 1;
  }

  $searchSpecs = "";
  if (!empty($search_char_id)) {
    $searchSpecs .= "AND char_id = $search_char_id ";
  }

  if (!empty($search_note_id)) {
    $searchSpecs .= "AND note_id = $search_note_id ";
  }

  if (!empty($search_note_title)) {
    $searchSpecs .= " AND (nts.utf8title LIKE " . $db->quote($search_note_title) . " OR nlog.prev_title LIKE " . $db->quote($search_note_title) . ") ";
  }

  if (!empty($search_object_id)) {
    $searchSpecs .= " AND object_id=$search_object_id ";
  }

  $dateSpecs = "";
  if ($search_from_day || $search_from_hour || $search_to_day || $search_to_hour) {
    $search_from_day ? $fd = sprintf("%04d", $search_from_day) : $fd = "0000";
    $search_from_hour ? $fh = $search_from_hour : $fh = "0";
    $search_to_day ? $td = $search_to_day : $td = "9999";
    $search_to_hour ? $th = $search_to_hour : $th = "7";

    $fromDate = "$fd-$fh";
    $toDate = "$td-$th";

    $dateSpecs = "AND date >= '$fromDate' AND date < '$toDate'";
  }

  $stm = $db->query("SELECT COUNT(*) AS count FROM obj_notes_log nlog
    " . (!empty($search_note_title) ? "LEFT JOIN obj_notes nts ON nts.id = nlog.note_id" : "") . " WHERE 1=1 $searchSpecs $dateSpecs");
  $objCount = $stm->executeScalar();

  $stm = $db->query("SELECT nlog.*, ch.name AS char_name, plr.id AS player_id, nts.utf8title AS note_name
    FROM obj_notes_log nlog LEFT JOIN chars ch ON ch.id = nlog.char_id
    LEFT JOIN players plr ON plr.id = ch.player
    LEFT JOIN obj_notes nts ON nts.id=nlog.note_id
    WHERE 1=1 $searchSpecs $dateSpecs ORDER BY nlog.id DESC LIMIT " . ($ptab - 1) * 100 . ", 100");

  $notes_list = $stm->fetchAll();
  foreach ($notes_list as &$noteData) {
    $stm = $db->query("SELECT nlog2.id
      FROM obj_notes_log nlog2 
      WHERE nlog2.prev_contents IS NOT NULL
        AND nlog2.id > " . intval($noteData->id) . " 
        AND nlog2.note_id = " . intval($noteData->note_id) . "
      ORDER BY nlog2.id LIMIT 1");
    $noteData->next_ver_id = $stm->executeScalar();
  }

  $smarty = new CantrSmarty();
  $smarty->assign("notes_list", $notes_list);
  $smarty->assign("nCount", (ceil($objCount / 100)));
  $smarty->assign("ptab", $ptab);
  $smarty->assign("search_from_day", $search_from_day);
  $smarty->assign("search_from_hour", $search_from_hour);
  $smarty->assign("search_to_day", $search_to_day);
  $smarty->assign("search_to_hour", $search_to_hour);
  $smarty->assign("search_char_id", $search_char_id);
  $smarty->assign("search_note_id", $search_note_id);
  $smarty->assign("search_object_id", $search_object_id);

  $smarty->assign("search_note_title", $search_note_title);

  $smarty->displayLang("page.notes_log.tpl", $lang_abr);

}
