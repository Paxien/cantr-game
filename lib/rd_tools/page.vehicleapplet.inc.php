
<?php

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::ACCESS_VEHICLE_APPLET)) {
	CError::throwRedirect("player", "You are trying to enter a game administration page (vehicleapplet) you are not allowed access to.");
}
    show_title ("APPLET FOR MANAGING VEHICLES/BOAT");
    echo "<CENTER><TABLE WIDTH=700><TR><TD WIDTH=700>";
    echo "<p align=\"center\"><applet codebase = \".\" code = \"vehiclemanager.Vehiclemanager.class\" name = \"TestApplet\" width = \"800\" height = \"600\" hspace = \"0\" vspace = \"0\" align = \"middle\"></applet></p>";
    echo "</TD></TR></TABLE></CENTER>";
