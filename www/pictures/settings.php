<?php
if (isset($lang_abr)) {
  require_once(_LIB_LOC . "/stddef.inc.php");
} else {
  require_once("../../lib/stddef.inc.php");
}

$url = "https://" . _ENV . ".cantr.net/pictures";
$abpath = dirname(__FILE__) . _PATH_DELIMITER;
$uploaddir = _ROOT_LOC . "/user_assets/uploaded_images";

$sqltime = 0;
$sqlcount = 0;
$sqllog = "";

include("lang/english.php");
