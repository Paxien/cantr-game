<?php

putenv('GDFONTPATH=' . realpath('.'));

$font = "../lib/fonts/dvsc_i.ttf";

$colors = array ("black" => "000000", "white" => "ffffff", "blue" => "5d67eb", "darkblue" => "242c92",
  "apple" => "539224", "gold" => "a2860d");

$h = intval($_REQUEST['h']);
$w = intval($_REQUEST['w']);
$col1 = $_REQUEST['col1'];
$col2 = $_REQUEST['col2'];
$perc = $_REQUEST['perc'];
$val = $_REQUEST['val'];

$height = $h ? $h : 20;
$width = $w ? $w : 200;

if ($colors [$col1]) $col1 = $colors [$col1];
if ($colors [$col2]) $col2 = $colors [$col2];

if (!$col1) $col1 = "ca2e2e";
if (!$col2) $col2 = "ffffff";

if ($perc) $val = $perc / 100;

$x1 = round (1 + ($width - 2) * $val);
$x2 = $width - $x1;
$corner = 25;
$srcwidth = 75;

$output = imagecreatetruecolor ($width, $height);
$part1 = @imagecreatefromjpeg ("graphics/cantr/pictures/bar".$col1.".jpg");
$part2 = @imagecreatefromjpeg ("graphics/cantr/pictures/bar".$col2.".jpg");

// let's call left part of the bar as 'red' and the right part of the bar as 'white'
// let's call round part of the bar as a 'corner'

if ($x1 < $corner)
  $cx = $x1;
else
  $cx = $corner; 

imagecopyresized ($output, $part1, 0, 0, 0, 0, $cx, $height, $cx, $height);

if ($x1 < $corner) {
  $cx = $corner - $x1;
  $q = $cx;
  imagecopyresized ($output, $part2, $x1, 0, $x1, 0, $cx, $height, $cx, $height);
}


// red medium part (if exists)
if ($x1 > $corner) {
  $cx = $x1 - $corner;
  imagecopyresized ($output, $part1, $corner, 0, $corner, 0, $cx, $height, 10, $height);
}

// white medium part (if exists)
if ($x2 > $corner) {
  $cx = $x2 - $corner - $q;
  imagecopyresized ($output, $part2, $x1 + $q, 0, $corner, 0, $cx, $height, 10, $height);
}


if ($x2 < $corner) {
  $cx = $corner - $x2;
  imagecopyresized ($output, $part1, $width-$corner, 0, $srcwidth-$corner-1, 0, $cx, $height, $cx, $height);
}

if ($x2 < $corner)
  $cx = $x2;
else
  $cx = $corner; 

imagecopyresized ($output, $part2, $width-$cx, 0, $srcwidth-$cx, 0, $cx, $height, $cx, $height);

if (!$notext) {
  $text = sprintf ("%d%%", 100*$val);
  $dim = imagettfbbox (10, 0, $font, $text);
  $black = imagecolorallocate ($output, 0, 0, 0);
  $white = imagecolorallocate ($output, 255, 255, 255);
  $x = ($width - $dim [2]) / 2;
  $y = 14;
  imagettftext ($output, 10, 0, $x + 1, $y + 1, $white, $font, $text);
  imagettftext ($output, 10, 0, $x, $y + 1, $white, $font, $text);
  imagettftext ($output, 10, 0, $x, $y, $black, $font, $text);
}
  
imagepng ($output);

?>
