<?php

$note_id = HTTPContext::getInteger('note_id');
$notes_log_id = HTTPContext::getInteger('notes_log_id');

$currentPlayer = Request::getInstance()->getPlayer();
if (!$currentPlayer->hasAccessTo(AccessConstants::NOTES_MANIPULATION)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$db = Db::get();
if ($note_id > 0) {
    $stm = $db->prepare("SELECT utf8title AS note_title, utf8contents AS content FROM obj_notes WHERE id = :id");
    $stm->bindInt("id", $note_id);
    $stm->execute();
  } elseif ($notes_log_id > 0) {
    $stm = $db->prepare("SELECT prev_title AS note_title, prev_contents AS content FROM obj_notes_log WHERE id = :id");
    $stm->bindInt("id", $notes_log_id);
    $stm->execute();
  } else {
    CError::throwRedirect("player", "there's no valid note for such id");
  }
  $obj = $stm->fetchObject();
    
  $printable = wordwrap ($obj->content, 85);

  $smarty = new CantrSmarty();
  $smarty->assign("contents", $printable);
  $smarty->assign("note_title", $obj->note_title);

  echo "<h3 style=\"text-align:center;\"><a href=\"index.php?page=notes_log\" class=\"button_charmenu\">Back to notes log</a></h3>";
  $smarty->displayLang ("info.note.tpl", $lang_abr);
