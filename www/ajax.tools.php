<?php

$startTime = microtime(true);

$ajaxRequest = true; // changes behaviour of cantr_redirect function and Error class

include_once "../lib/stddef.inc.php";

$s = session::getSessionFromCookie();

include_once _LIB_LOC . "/urlencoding.inc.php";
DecodeURIs();

$db = Db::get();
$turn = GameDate::NOW()->getObject();
$turn_info = clone $turn; // backward compatible

$session_handle = new Session($s, null, false);
$session_info = $session_handle->checklogin();

if (!($session_info instanceof stdClass)) { // when error occurred => session is corrupted
  CError::throwRedirect("", "Something went wrong");
}

$player = $session_info->player;
$lang_abr = $session_handle->languagestr;

$page = HTTPContext::getString('page', '');

$allowedPages = array(
  "playerinfo" => "ajax/info.player.php",
);

if (!array_key_exists($page, $allowedPages)) {
  CError::throwRedirect("player", "Page $page is not available");
}

include(_LIB_LOC . "/" . $allowedPages[$page]);

$time = microtime(true) - $startTime;

if (mt_rand(0, 1000) < 20) {
  $timing = new Timing($db);
  $timing->store(true);
}
