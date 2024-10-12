<?php

$reenter = $_REQUEST['reenter'];
$mailSent = $_REQUEST['mail_sent'];

// Smarty init
session_start();

$smarty = new CantrSmarty;

if (isset($s)) { // logged in
  $session = new session;

  $loggedin = true;
  $playerInfo = Request::getInstance()->getPlayer();
  $smarty->assign("player_details", "{$playerInfo->getFullNameWithId()} {$playerInfo->getEmail()}");
} else {
  $loggedin = false;
}

if (isset($_SESSION["mail_to"])) {
  $mail_to = $_SESSION["mail_to"];
} else {
  $mail_to = -1;
}

$depts = array();
$smarty->assign("loggedin", $loggedin);
$dept = new stdClass();
$dept->id = -1;
$dept->name = "<CANTR REPLACE NAME=dept_support>";
$dept->description = "<CANTR REPLACE NAME=dept_support_description>";

$depts[] = clone $dept;

$PLAYERS_DEPARTMENT = 6;

$db = Db::get();

$stm = $db->query("SELECT * FROM councils");
foreach ($stm->fetchAll() as $dept_info) {
  $dept = new stdClass();
  $dept->id = $dept_info->id;
  $dept->name = "<CANTR REPLACE NAME=dept_" . $dept_info->email . ">";
  $dept->description = "<CANTR REPLACE NAME=dept_" . $dept_info->email . "_description>";
  if ($mail_to == $dept->id || ($mail_to == -1 && $dept->id == $PLAYERS_DEPARTMENT)) {
    $dept->selected = " selected";
  }
  $depts[] = $dept;
}

//Special email addresses not in councils table
$dept = new stdClass();
$dept->id = 100;
$dept->name = "Webzine";
$dept->description = "Webzine";
if ($mail_to == $dept->id) {
  $dept->selected = " selected";
}
$depts[] = clone $dept;

$dept->id = 101;
$dept->name = "Finance";
$dept->description = "Finance";
if ($mail_to == $dept->id) {
  $dept->selected = " selected";
}
$depts[] = clone $dept;

if ($loggedin) {
  $smarty->assign("return_page", "player");
} else {
  $smarty->assign("return_page", "intro");
}


$smarty->assign("SessionID", $s);
$smarty->assign("departments", $depts);
$smarty->assign("mailSent", $mailSent);

if (!isset($lang_abr)) {
  $l = intval($l);
  if ($l <= 0) {
    unset($l);
  }
  if (!isset($l)) {
    $l = 1;
  }
  $stm = $db->prepare("SELECT abbreviation FROM languages WHERE id = :language LIMIT 1");
  $stm->bindInt("language", $l);
  $lang_abr = $stm->executeScalar();
}

$smarty->assign("recaptcha_key", _RECAPTCHA_SITE_KEY);

// now check if return after validation failure
if (!$reenter) {
  $errmsg = "";
  $message = "";
  $mail_from = "";
  $mail_confirm = "";
  $mail_subject = "";
  $mail_to = -1;
  $smarty->assign("displayerror", false);
} else {
  $errmsg = $_SESSION["errmsg"];
  $message = $_SESSION["message"];
  $mail_from = $_SESSION["mail_from"];
  $mail_subject = $_SESSION["mail_subject"];
  $mail_confirm = $_SESSION["mail_confirm"];
  $mail_to = $_SESSION["mail_to"];
  $smarty->assign("displayerror", true);

}
$smarty->assign("error_message", $errmsg);
$smarty->assign("mail_from", $mail_from);
$smarty->assign("mail_confirm", $mail_confirm);
$smarty->assign("mail_subject", $mail_subject);
$smarty->assign("message", $message);


$smarty->displayLang("form.contact.tpl", $lang_abr);
