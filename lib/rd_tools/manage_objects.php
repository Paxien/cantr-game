<?php

$error = $_REQUEST['error'];
$func = $_REQUEST['func'];

switch ($func) {

 	case "copy" :
 		include "mo_copy.inc.php";
 		
 	case "form" :
 		if ($data) {
 			include "mo_store.inc.php";
 		} else {
 			include "mo_form.inc.php";
 		}

	default :
		include "mo_main.inc.php";
}

