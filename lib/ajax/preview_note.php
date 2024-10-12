<?php

import_lib('func.strip_html_tags.inc.php');

$noteTitle = $_REQUEST['notename']; // input is lowercase, var name is camelCase
$noteText = $_REQUEST['notetext'];
$isDiff = ($_REQUEST['diff'] == "true");

if (Limitations::getLims($player, Limitations::TYPE_PLAYER_NOTE_PREVIEW)) {
  echo json_encode(array("look" => "", "code" => "You are too fast!"));
  exit;
}
// limit frequency of requesting preview to about one per 5 seconds
Limitations::addLim($player, Limitations::TYPE_PLAYER_NOTE_PREVIEW,
  Limitations::dhmstoc(0, 0, 0, 0)); // end of the same second

$noteTitle = htmlspecialchars($noteTitle);

$notePurifier = new NotePurifier();

if (!check_event_strings($noteText)) { // obvious cracking attempt
  $tag = new Tag("<CANTR REPLACE NAME=warn_lame_cracking_attempt>");
  echo json_encode(
    array("look" => "", "code" => $tag->interpret())
  );
} else {

  $cleanedText = $notePurifier->purify($noteText);

  if (strlen($cleanedText) >= 65535) { // text allowed in database is in bytes, not characters
    echo json_encode(array("look" => "", "code" => "Note is too long!"));
    exit;
  }

  $smarty = new CantrSmarty();
  $smarty->assign("note_title", $noteTitle);
  $smarty->assign("contents", $cleanedText);
  $smarty->assign("hideBackButton", true);
  $smarty->assign("hideEncodingChange", true);

  ob_start();
  $smarty->displayLang("info.note.tpl", $lang_abr);
  $look = ob_get_clean();
  $look = wordwrap ($look, 85);

  $diffText = null;

  if ($isDiff) {
    $pipeSpec = array( // required pipes for both-sides communication
      array("pipe", "r"),
      array("pipe", "w"),
    );

    $pipes = array();
    $env = array("JAVA_TOOL_OPTIONS" => "-Dfile.encoding=UTF-8");
    // start java diff program - it's because of poor peformance of php implementation - often time > 60s
    $proc = proc_open("java -jar ". _LIB_LOC . "/3rdparty/DiffMatchPatch/bin/notes-diff.jar",
      $pipeSpec, $pipes, null, $env);

    $in = $pipes[0];
    $out = $pipes[1];

    fwrite($in, json_encode( // send old and new version of note as json
      array("previous" => $noteText,
      "current" => $cleanedText))
    );
    fclose($in);

    $diffText = json_decode(stream_get_contents($out)); // get diff as json

    fclose($out);
    proc_close($proc);
  }
  $cleanedText = htmlspecialchars($cleanedText);

  echo json_encode(
    array("look" => $look, "code" => $cleanedText, "diff" => $diffText)
  );
}
