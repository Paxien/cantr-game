<?php
/*
This file is a placeholder for the old forum location. It will redirect to the new forum location
*/
$params = "?";
$params .= (isset($f)) ? ('f=' . $f . '&') : '';
$params .= (isset($t)) ? ('t=' . $t) : "t=12990";

header("location: http://forum.cantr.org/viewtopic.php" . $params);

?>
