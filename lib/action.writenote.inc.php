<?php

$notename = $_REQUEST['notename'];
$notetext = $_REQUEST['notetext'];

// to notify people who make obvious cracking attempts or copypaste whole note html source
import_lib('func.strip_html_tags.inc.php');
if (!check_event_strings($notetext)) {
  $playerInfo = Request::getInstance()->getPlayer();
  $env = Request::getInstance()->getEnvironment();
  $subject = "Possible hacking attempt ". $env->getFullName();
  $message = "Player     : " . $playerInfo->getId() . ", " . $playerInfo->getFullName() . "\n";
  $message .= "Extra info : \n\n" . __FILE__ ."\n\nNote text:\n$notetext";
  $mailService = new MailService("Cantr Programming", $GLOBALS['emailProgramming']);
  $mailService->sendPlaintext($GLOBALS['emailProgramming'], $subject, $message);

  CError::throwRedirectTag("player", "error_invalid_input");
}

// SANITIZE INPUT
$setting = HTTPContext::getInteger('setting');
$notename = htmlspecialchars($notename);

// purify input text
$notePurifier = new NotePurifier();
$cleanedText = $notePurifier->purify($notetext);

$date = GameDate::NOW();
$dateStr = $date->getDay() . "-" . $date->getHour() . ":" . $date->getMinute();

$db = Db::get();
$stm = $db->prepare("INSERT INTO obj_notes (title, contents, setting, utf8title, utf8contents, encoding, converted)
  VALUES (NULL, NULL, :setting, :title, :contents, 'utf8', 2)");
$stm->bindInt("setting", $setting);
$stm->bindStr("title", $notename);
$stm->bindStr("contents", $cleanedText);
$stm->execute();
$id_note = $db->lastInsertId();

$noteObject = ObjectCreator::inInventory($char, ObjectConstants::TYPE_NOTE, ObjectConstants::SETTING_PORTABLE, 0)
  ->typeid($id_note)->create();

$stm = $db->prepare("INSERT INTO obj_notes_log (char_id, note_id, object_id, action, date)
  VALUES (:charId, :objNoteId, :objectId, 'create', :date)");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("objNoteId", $id_note);
$stm->bindInt("objectId", $noteObject->getId());
$stm->bindStr("date", $dateStr);
$stm->execute();

redirect("char.inventory");
