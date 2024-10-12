<?php

$ajaxRequest = true; // changes behaviour of cantr_redirect function and Error class

include_once "../lib/stddef.inc.php";

include_once _LIB_LOC . "/urlencoding.inc.php";
DecodeURIs();

$page = $_REQUEST['page'];

$pages = [
  "validate_email" => "ajax/validate_email.php",
  "validate_username" => "ajax/validate_username.php",
];

include(_LIB_LOC . "/" . $pages[$page]);
