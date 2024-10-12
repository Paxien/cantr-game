<?php
/*
This file is a placeholder for the old forum location. It will redirect to the new forum location
*/

$params = "?";
$params .= (isset($f)) ? ("f=" . $f) : "f=1";

header("location: http://forum.cantr.org/viewforum.php" . $params);

?>
