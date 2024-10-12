<?php

$selectedtab = HTTPContext::getInteger('selectedtab');

switch ($selectedtab) {
  case 0:
    include 'deactive_events/action.charsettings.deactive.inc.php';
    break;
  case 1:
    include 'filters/action.charsettings.filters.inc.php';
    break;
  case 2:
    include 'other/action.death_old_age.inc.php';
    break;
  case 3:
    include 'spawning/action.opt_out_from_spawning.php';
    break;
}

redirect("char.events");
