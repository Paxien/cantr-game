<?php
// imgerror
 if (!$img) { $img = "404.png"; }
 Header("Content-type: image/png");
 $fn=fopen("".$abpath."images/$img","r");
 fpassthru($fn);
