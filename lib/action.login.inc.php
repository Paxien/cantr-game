<?php

$id = $_REQUEST['id']; // it can be id or username
$password = $_REQUEST['password'];
$onetime = $_REQUEST['onetime']; // non empty if using a one time password instead of standard one
$from = HTTPContext::getRawString("from", ""); // from what source did it happen
$afterLogin = urldecode($_REQUEST['after']);

$login = new session;

$idVal = trim($id);
if (is_numeric($idVal)) {
  $login->id = intval($idVal);
} else {
  $login->user_name = $idVal;
}
// Don't sanitize it, it is hashed before entered in the database, and should be allowed to contain "unsafe" chars.
$login->password = $password;

$login->onetime = !empty($onetime);

$env = Request::getInstance()->getEnvironment();

if ($login->validate()) {

  $session_id = $login->start();

  if ($session_id) {
    $currentminute = date("i") + 60 * date("H");

    $db = Db::get();
    $stm = $db->prepare("UPDATE players SET lastminute = :lastMinute WHERE id = :id");
    $stm->bindInt("lastMinute", $currentminute);
    $stm->bindInt("id", $login->id);
    $stm->execute();

    $stm = $db->prepare("INSERT IGNORE INTO `player_logins` (`player_id`, `date`, `origin`, `onetime`)
      VALUES (:id, NOW(), :from, :onetime)");
    $stm->bindInt("id", $login->id);
    $stm->bindStr("from", $from);
    $stm->bindBool("onetime", !empty($onetime));
    $stm->execute();

    if (empty($afterLogin)) {
      $afterLogin = "player";
    }
    redirect($afterLogin, [], null, $session_id);
    exit;

  } else {
    CError::throwRedirectTag($page, "error_session_generation_problem");
  }
} else {
  CError::throwRedirect($page, $login->error);
}
