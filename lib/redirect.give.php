<?php

if (!isset($_REQUEST['receiver'])) {
  include _LIB_LOC ."/form.give.php";
} else {
  include _LIB_LOC ."/action.give.php";
}
